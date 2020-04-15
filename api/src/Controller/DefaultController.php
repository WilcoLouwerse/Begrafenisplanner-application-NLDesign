<?php

// src/Controller/DashboardController.php

namespace App\Controller;

use App\Service\ApplicationService;
//use App\Service\RequestService;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Annotation\Route;

use App\Service\CommonGroundService;

/**
 * Class DeveloperController
 * @package App\Controller
 * @Route("/")
 */
class DefaultController extends AbstractController
{

	/**
	 * @Route("/")
	 * @Template
	 */
    public function indexAction(Session $session, $slug = false, Request $httpRequest, CommonGroundService $commonGroundService, ApplicationService $applicationService)
    {
        $variables = $applicationService->getVariables();

        $applicationId = '536bfb73-63a5-4719-b535-d835607b88b2';
        $wrcComponent = '536bfb73-63a5-4719-b535-d835607b88b2';
        $template = $this->commonGroundService->getResource($wrcComponent.'/applications/'.$wrcComponent.'/'.$slug);

        $template = $this->get('twig')->createTemplate($template);
        $template = $template->render($variables);

        return $response = new Response(
            $template,
            Response::HTTP_OK,
            ['content-type' => 'text/html']
        );
    }

}






