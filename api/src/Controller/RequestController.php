<?php

// src/Controller/ZZController.php

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
 * The Request controller handles any calls that have not been picked up by another controller, and wel try to handle the slug based against the wrc
 *
 * Class RequestController
 * @package App\Controller
 * @Route("/request")
 */
class RequestController extends AbstractController
{
	/**
	 * @Route("/load/{id}")
	 */
    public function loadAction($id, Session $session, string $slug = 'home',Request $request, CommonGroundService $commonGroundService, ApplicationService $applicationService, ParameterBagInterface $params)
    {
        //$variables = $applicationService->getVariables();
        $loadedRequest = $commonGroundService->getResource(['component'=>'vrc','type'=>'requests','id'=>$id]);//,['extend'=>'processType']);
        $session->set('request', $loadedRequest);
        //todo: extend subscriber gebruiken, cgs testen met query parameter
        return $this->redirect($this->generateUrl('app_process_load',['id'=>$commonGroundService->getResource($loadedRequest['processType'])['id']]));
    }

    /**
     * @Route("/")
     * @Template
     */
    public function indexAction(Session $session, string $slug = 'home',Request $request, CommonGroundService $commonGroundService, ApplicationService $applicationService, ParameterBagInterface $params)
    {
        $variables = $applicationService->getVariables();
        $variables['requests'] = $commonGroundService->getResourceList(['component'=>'vrc','type'=>'requests'],['submitters.brp'=>$variables['user']['@id']])['hydra:member'];
//        var_dump($variables['requests']);

        // Lets provide this data to the template
        return $variables;
    }

}






