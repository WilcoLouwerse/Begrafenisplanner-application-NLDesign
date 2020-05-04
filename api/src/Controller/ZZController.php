<?php

// src/Controller/ZZController.php

namespace App\Controller;

use App\Service\ApplicationService;
//use App\Service\RequestService;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

use App\Service\CommonGroundService;

/**
 * The ZZ controller handles any calls that have not been picked up by another controller, and wel try to handle the slug based against the wrc
 *
 * Class ZZController
 * @package App\Controller
 * @Route("/")
 */
class ZZController extends AbstractController
{

	/**
	 * @Route("/{slug}", requirements={"slug"=".+"}, name="slug")
	 * @Template
	 */
    public function indexAction(Session $session, string $slug = 'home',Request $request, CommonGroundService $commonGroundService, ApplicationService $applicationService, ParameterBagInterface $params)
    {
        $variables = $applicationService->getVariables();

        // Lets provide this data to the template
        $variables['query'] = $request->query->all();
        $variables['post'] = $request->request->all();

        // Lets also provide any or all id
        $slug_parts = explode('/',$slug);
        $variables['id'] = end($slug_parts);

        // Lets find an appoptiate slug
        $slugs = $commonGroundService->getResourceList(['component'=>'wrc','type'=>'slugs'],['application.id'=>$variables['application']['id'],'slug'=>$slug])["hydra:member"];

        if(count($slugs) != 0){
            $content = $slugs[0]['template']['content'];
        }
        else{
            // Throw not found
        }

        // Lets see if there is a post to procces
        if ($request->isMethod('POST')) {

            // Passing the variables to the resource
            $resource = $request->request->all();
            $configuration = $commonGroundService->saveResource($resource, ['component'=>$resource['@component'],'type'=>$resource['@type']]);
        }


        // Create the template
        $template = $this->get('twig')->createTemplate($content);
        $template = $template->render($variables);

        return $response = new Response(
            $template,
            Response::HTTP_OK,
            ['content-type' => 'text/html']
        );
    }

}






