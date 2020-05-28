<?php

// src/Controller/ProcessController.php

namespace App\Controller;

use App\Service\ApplicationService;
//use App\Service\RequestService;
use Conduction\CommonGroundBundle\Service\CommonGroundService;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;



/**
 * The Procces controller handles any calls that have not been picked up by another controller, and wel try to handle the slug based against the wrc
 *
 * Class ProcessController
 * @package App\Controller
 * @Route("/process")
 */
class ProcessController extends AbstractController
{
    /**
     * This function shows all available processes
     *
     * @Route("/")
     * @Template
     */
    public function indexAction(Session $session, Request $request, CommonGroundService $commonGroundService, ApplicationService $applicationService, ParameterBagInterface $params){
        $variables = $applicationService->getVariables();
        $variables['processes'] = $commonGroundService->getResourceList(['component'=>'ptc','type'=>'process_types'])['hydra:member'];
        return $variables;
    }
	/**
     * This function will kick of the suplied proces with given values
     *
	 * @Route("/{id}")
	 * @Route("/{id}/{slug}", name="app_process_slug")
	 * @Template
	 */
    public function loadAction(Session $session, $id, string $slug = 'home',Request $request, CommonGroundService $commonGroundService, ApplicationService $applicationService, ParameterBagInterface $params)
    {
        $variables = $applicationService->getVariables();
        if($request->isMethod('POST')){
            $resource = $request->request->all();
            if(key_exists('organization',$resource)){
                if(key_exists('request',$variables) && key_exists('properties',$variables['request'])){
                    $resource['properties'] = array_replace_recursive($variables['request']['properties'],$resource['properties']);
                }
                foreach($resource['properties'] as $key=>$property){
                    if($key == 'begraafplaats' && is_string($resource['properties'][$key]) && is_array(json_decode($resource['properties'][$key], true)) && (json_last_error() == JSON_ERROR_NONE))
                    {
                        $resource['properties'][$key] = json_decode($resource['properties'][$key],true)['Cemetery'];
                    }
                    if(is_array($property)
                        && key_exists('postalCode', $property)
                        && key_exists('houseNumber',$property)
                    ){
                        $addresses = $commonGroundService->getResourceList(['component'=>'as','type'=>'adressen'],['postcode'=>$property['postalCode'],'huisnummer'=>$property['houseNumber'],'huisnummertoevoeging'=>$property['houseNumberSuffix']])['adressen'];
                        if(empty($addresses)){
                            $this->addFlash('error', "adres niet gevonden");
                            unset($resource['properties'][$key]);
                        }else{
                            $resource['properties'][$key] = $addresses[0]['id'];
                        }
                    }
                }
                $variables['request'] = $commonGroundService->saveResource($resource, ['component'=>'vrc','type'=>'requests']);
                $session->set('request', $variables['request']);
                if(key_exists('next',$resource)){
                    $slug = $resource['next'];
                }
            }
        }
        $variables['process'] = $commonGroundService->getResource(['component'=>'ptc','type'=>'process_types','id'=>$id]);
        if(
            $slug == 'home' &&
            key_exists('request',$variables) &&
            key_exists('currentStage', $variables['request']) &&
            $variables['request']['currentStage']){
                $variables['stage'] = $commonGroundService->getResource($variables['request']['currentStage']);
        }
        elseif($slug != 'home'){
            foreach($variables['process']['stages'] as $stage){
                if($stage['slug'] == $slug){
                    $variables['stage'] = $stage;
                }
            }
        }
        else{
            foreach($variables['process']['stages'] as $stage){
                if($stage['start'])
                    $variables['stage'] = $stage;
            }
        }
        $variables["slug"] = $slug;

        $variables['cemeteries'] = [];
        $variables['organizations'] = $commonGroundService->getResourceList(['component'=>'wrc','type'=>'organizations'])['hydra:member'];
        $variables['grave_types'] = $commonGroundService->getResourceList(['component'=>'grc','type'=>'grave_types'])['hydra:member'];

        $cemeteries = $commonGroundService->getResourceList(['component'=>'grc','type'=>'cemeteries']);
        if(key_exists("hydra:view", $cemeteries)) {
            $lastPageCemeteries = (int) str_replace("/cemeteries?page=", "", $cemeteries["hydra:view"]["hydra:last"]);
            for ($i = 1; $i <= $lastPageCemeteries; $i++) {
                $variables['cemeteries'] = array_merge($variables['cemeteries'], $commonGroundService->getResourceList(['component'=>'grc','type'=>'cemeteries'], ['page'=>$i])["hydra:member"]);
            }
        }
        else {$variables["cemeteries"] = $cemeteries["hydra:member"];}

        if(
            key_exists('request',$variables) &&
            key_exists('properties', $variables['request'])
        )
        {
            foreach($variables['request']['properties'] as $key=>$property)
            {
                if (!is_array($property) && (strpos($property, 'begraven.zaakonline.nl') !== false || strpos($property, 'westfriesland.commonground.nu') !== false))
                {
                    $variables['request']['urlproperties'][$key] = $commonGroundService->getResource($property);
                    if (key_exists('reference',$variables['request']['urlproperties'][$key]))
                    {
                        $variables['request']['urlproperties'][$key]['name'] = $variables['request']['urlproperties'][$key]['reference'];
                    }
                    if(key_exists('burgerservicenummer',$variables['request']['urlproperties'][$key]))
                    {
                        $variables['request']['urlproperties'][$key]['name'] = $variables['request']['urlproperties'][$key]['naam']['voornamen']." ".
                            $variables['request']['urlproperties'][$key]['naam']['geslachtsnaam'];
                    }
                }
            }

            if (key_exists('begraafplaats', $variables['request']['properties']))
            {
                $variables['selectedBegraafplaats'] = $commonGroundService->getResource($variables['request']['properties']['begraafplaats']);
            }
            else{//de volgende code moet nog eens goed naar gekeken worden, dit is een tijdelijke oplossing voor een probleem, het hard wegzetten van een begraafplaats.
                $variables['selectedBegraafplaats'] = $commonGroundService->getResource(['component'=>'grc','type'=>'cemeteries','id'=>'2556c084-0687-4ca1-b098-e4f0a7292ae8']);
                $variables['selectedBegraafplaats']['reference'] = "Wognum (Kreekland), ER IS NOG GEEN BEGRAAFPLAATS GEKOZEN!";
            }
            $variables['selectedGemeente'] = $commonGroundService->getResource($variables['selectedBegraafplaats']['organization']);
            $variables['calendar'] = $commonGroundService->getResource($variables['selectedBegraafplaats']['calendar']);
            if (key_exists('grafsoort', $variables['request']['properties']))
            {
                $variables['selectedGrafsoort'] = $commonGroundService->getResource($variables['request']['properties']['grafsoort']);
            }
            if (key_exists('event', $variables['request']['properties']))
            {
                $variables['selectedEvent'] = $commonGroundService->getResource($variables['request']['properties']['event']);
            }
            if (key_exists('overledene', $variables['request']['properties']))
            {
                $variables['selectedOverledene'] = $commonGroundService->getResource($variables['request']['properties']['overledene']);
            }
            if (key_exists('belanghebbende', $variables['request']['properties']))
            {
                $variables['selectedBelanghebbende'] = $commonGroundService->getResource($variables['request']['properties']['belanghebbende']);
            }
        }
        else {//de volgende code moet nog eens goed naar gekeken worden, dit is een tijdelijke oplossing voor een probleem, het hard wegzetten van een begraafplaats.
            $variables['selectedBegraafplaats'] = $commonGroundService->getResource(['component'=>'grc','type'=>'cemeteries','id'=>'2556c084-0687-4ca1-b098-e4f0a7292ae8']);
            $variables['selectedBegraafplaats']['reference'] = "Wognum (Kreekland), ER IS NOG GEEN BEGRAAFPLAATS GEKOZEN!";
            $variables['calendar'] = $commonGroundService->getResource($variables['selectedBegraafplaats']['calendar']);
        }

        $i = 0;
        while(true) {
            $todayDate = new \DateTime("now");
            $dayToCheck = $todayDate->modify('-'.$i.' days');
            if ($dayToCheck->format('N')== 1) {
                $variables['lastMonday'] = $dayToCheck;
                break;
            }
            else {$i++;}
        }

        $variables['ingeschrevenpersonen'] = $commonGroundService->getResourceList(['component'=>'brp','type'=>'ingeschrevenpersonen']);
        $variables['geboortes'] = $commonGroundService->getResourceList(['component'=>'brp','type'=>'geboortes']);

        return $variables;
    }
}






