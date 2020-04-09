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
class BegrafenisplannenController extends AbstractController
{
    /**
     * @Route("/begraafplaats")
     * @Template
     */
    public function begraafplaatsAction(Session $session, $slug = false, Request $httpRequest, CommonGroundService $commonGroundService, ApplicationService $applicationService)
    {
        $variables = [];

        if ($httpRequest->isMethod('POST'))
        {
            return $this->redirect($this->generateUrl('app_begrafenisplannen_datumtijd'));
        }

        return $variables;
    }

    /**
     * @Route("/datumtijd")
     * @Template
     */
    public function datumtijdAction(Session $session, $slug = false, Request $httpRequest, CommonGroundService $commonGroundService, ApplicationService $applicationService)
    {
        $variables = [];
        if ($httpRequest->isMethod('POST')) {
                return $this->redirect($this->generateUrl('app_begrafenisplannen_artikelen'));
        }
        return $variables;
    }

    /**
     * @Route("/artikelen")
     * @Template
     */
    public function artikelenAction(Session $session, $slug = false, Request $httpRequest, CommonGroundService $commonGroundService, ApplicationService $applicationService)
    {
        $variables = [];
        return $variables;
    }

    /**
     * @Route("/overledene")
     * @Template
     */
    public function overledeneAction(Session $session, $slug = false, Request $httpRequest, CommonGroundService $commonGroundService, ApplicationService $applicationService)
    {
        $variables = [];
        return $variables;
    }

    /**
     * @Route("/belanghebbende")
     * @Template
     */
    public function belanghebbendeAction(Session $session, $slug = false, Request $httpRequest, CommonGroundService $commonGroundService, ApplicationService $applicationService)
    {
        $variables = [];
        return $variables;
    }

    /**
     * @Route("/bevestiging")
     * @Template
     */
    public function bevestigingAction(Session $session, $slug = false, Request $httpRequest, CommonGroundService $commonGroundService, ApplicationService $applicationService)
    {
        $variables = [];
        return $variables;
    }

}
