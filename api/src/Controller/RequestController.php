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
	 * @Template
	 */
    public function loadAction(Session $session, string $slug = 'home',Request $request, CommonGroundService $commonGroundService, ApplicationService $applicationService, ParameterBagInterface $params)
    {
        $variables = $applicationService->getVariables();

        // Lets provide this data to the template
        $redirect = $request->query->get('redirect');
    }

    /**
     * @Route("/start/{id}")
     * @Template
     */
    public function startAction(Session $session, string $slug = 'home',Request $request, CommonGroundService $commonGroundService, ApplicationService $applicationService, ParameterBagInterface $params)
    {
        $variables = $applicationService->getVariables();

        // Lets provide this data to the template
        $redirect = $request->query->get('redirect');
    }

}






