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
     * @Route("/{id}/start")
     */
    public function startAction(Session $session, $id, Request $request, CommonGroundService $commonGroundService, ApplicationService $applicationService, ParameterBagInterface $params){
        $session->set('request',null);

        return $this->redirect($this->generateUrl('app_process_load',['id'=>$id]));
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
                if(key_exists('request',$variables) && key_exists('properties',$variables['request']) && key_exists('properties', $resource)){
                    $resource['properties'] = array_replace_recursive($variables['request']['properties'],$resource['properties']);
                }elseif(key_exists('properties', $resource)){
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
            $variables['request']['currentStage'])
        {
            $variables['stage'] = $commonGroundService->getResource($variables['request']['currentStage']);
        }
        elseif($slug != 'home'){
            foreach($variables['process']['stages'] as $stage){


                if($stage['slug'] == $slug){

                    $variables['stage'] = $stage;
                }
                if(!key_exists('stage',$variables))
                    $variables['stage']['slug'] = $slug;
            }
        }
        else{
            foreach($variables['process']['stages'] as $stage){

                if($stage['start'])
                    $variables['stage'] = $stage;

            }
        }
        $variables["slug"] = $slug;

        if(
            key_exists('request',$variables) &&
            key_exists('properties', $variables['request'])
        )
        {
            if (key_exists('overledene', $variables['request']['properties']) && !empty($variables['request']['properties']['overledene']))
            {
                $variables['selectedOverledene'] = $commonGroundService->getResource($variables['request']['properties']['overledene']);
            }
            if (key_exists('belanghebbende', $variables['request']['properties']) && !empty($variables['request']['properties']['belanghebbende']))
            {
                $variables['selectedBelanghebbende'] = $commonGroundService->getResource($variables['request']['properties']['belanghebbende']);
            }
        }

        
        return $variables;
    }

}






