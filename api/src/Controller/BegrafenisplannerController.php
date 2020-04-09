<?php


namespace App\Controller;

use App\Service\ApplicationService;
//use App\Service\RequestService;
use App\Service\CommonGroundService;
use DateTimeZone;
use Doctrine\ORM\EntityManagerInterface;
use phpDocumentor\Reflection\Types\Integer;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Constraints\DateTime;

/**
 * Class DeveloperController
 * @package App\Controller
 * @Route("/begrafenisplannen")
 */
class BegrafenisplannerController extends AbstractController
{

    /**
     * @Route("/begraafplaats")
     * @Template
     */
    public function begraafplaatsAction(Session $session, $slug = false, Request $httpRequest, CommonGroundService $commonGroundService, ApplicationService $applicationService)
    {
        $variables = [];

        return $variables;
    }

}
