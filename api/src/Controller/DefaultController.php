<?php

// src/Controller/DashboardController.php

namespace App\Controller;

use App\Service\ApplicationService;
//use App\Service\RequestService;
use DateTimeZone;
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
 * Class DeveloperController
 * @package App\Controller
 * @Route("/")
 */
class DefaultController extends AbstractController
{

	/**
	 * @Route("/{slug}", requirements={"slug"=".+"})
	 * @Template
	 */
    public function indexAction(Session $session, string $slug = 'home',Request $httpRequest, CommonGroundService $commonGroundService, ApplicationService $applicationService, ParameterBagInterface $params)
    {
        $variables = $applicationService->getVariables();

        // Lets find an appoptiate slug
        $slugs = $commonGroundService->getResourceList(['component'=>'wrc','type'=>'slugs'],['application.id'=>$variables['application']['id'],'slug'=>$slug])["hydra:member"];

        if(count($slugs) != 0){
            $content = $slugs[0]['template']['content'];
        }
        else{
            // Throw not found
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






