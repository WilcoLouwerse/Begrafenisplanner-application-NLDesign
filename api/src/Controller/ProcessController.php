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
     * This function will kick of the suplied proces with given values
     *
	 * @Route("/{id}")
	 * @Route("/{id}/{slug}")
	 * @Template
	 */
    public function loadAction(Session $session, $id, string $slug = 'home',Request $request, CommonGroundService $commonGroundService, ApplicationService $applicationService, ParameterBagInterface $params)
    {
        $variables = $applicationService->getVariables();
        if($request->isMethod('POST')){
            $resource = $request->request->all();
            if(key_exists('organization',$resource)){
                if(key_exists('request',$variables) && key_exists('properties',$variables['request'])){

                    $resource['properties'] = array_replace_recursive($resource['properties'],$variables['request']['properties']);
                }
//                var_dump($resource);
//                die;
                $variables['request'] = $commonGroundService->saveResource($resource, ['component'=>'vrc','type'=>'requests']);
                $session->set('request', $variables['request']);
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
//            var_dump($variables['process']['stages']);
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
        // Lets provide this data to the template
//        $redirect = $request->query->get('redirect');

        return $variables;
        //$result =
    }
}






