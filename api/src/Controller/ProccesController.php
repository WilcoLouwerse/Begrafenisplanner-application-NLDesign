<?php

// src/Controller/ProccesController.php

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
 * The Procces controller handles any calls that have not been picked up by another controller, and wel try to handle the slug based against the wrc
 *
 * Class ProccesController
 * @package App\Controller
 * @Route("/procces")
 */
class ProccesController extends AbstractController
{
	/**
     * This function will kick of the suplied proces with given values
     *
	 * @Route("/{id}")
	 * @Template
	 */
    public function loadAction(Session $session, string $slug = 'home',Request $httpRequest, CommonGroundService $commonGroundService, ApplicationService $applicationService, ParameterBagInterface $params)
    {
        $variables = $applicationService->getVariables();

        // Lets provide this data to the template
        $redirect = $request->query->get('redirect');

        //$result =
    }
}






