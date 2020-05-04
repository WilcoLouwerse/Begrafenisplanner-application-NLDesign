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
        $variables['cemeteries'] = [];

        $variables['organizations'] = $commonGroundService->getResourceList($commonGroundService->getComponent('wrc')['href'].'/organizations')["hydra:member"];

        $cemeteries = $commonGroundService->getResourceList($commonGroundService->getComponent('grc')['href'].'/cemeteries');
        if(key_exists("hydra:view", $cemeteries))
        {
            $lastPageCemeteries = (int) str_replace("/cemeteries?page=", "", $cemeteries["hydra:view"]["hydra:last"]);
            for ($i = 1; $i <= $lastPageCemeteries; $i++)
            {
                $variables['cemeteries'] = array_merge($variables['cemeteries'], $commonGroundService->getResourceList($commonGroundService->getComponent('grc')['href'].'/cemeteries', ['page'=>$i])["hydra:member"]);
            }
        }
        else
        {
            $variables["cemeteries"] = $cemeteries["hydra:member"];
        }

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

        $cemeteries = $commonGroundService->getResourceList($commonGroundService->getComponent('grc')['href'].'/cemeteries');

        $variables['calendar'] = $commonGroundService->getResourceList($cemeteries['hydra:member'][0]['calendar']);

        if ($httpRequest->isMethod('POST'))
        {
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

        if ($httpRequest->isMethod('POST'))
        {
            return $this->redirect($this->generateUrl('app_begrafenisplannen_overledene'));
        }

        return $variables;
    }

    /**
     * @Route("/overledene")
     * @Template
     */
    public function overledeneAction(Session $session, $slug = false, Request $httpRequest, CommonGroundService $commonGroundService, ApplicationService $applicationService)
    {
        $variables = [];

        $variables['ingeschrevenpersonen'] = $commonGroundService->getResourceList($commonGroundService->getComponent('brp')['href'].'/ingeschrevenpersonen');

        if ($httpRequest->isMethod('POST'))
        {
            return $this->redirect($this->generateUrl('app_begrafenisplannen_belanghebbende'));
        }
        return $variables;
    }

    /**
     * @Route("/belanghebbende")
     * @Template
     */
    public function belanghebbendeAction(Session $session, $slug = false, Request $httpRequest, CommonGroundService $commonGroundService, ApplicationService $applicationService)
    {
        $variables = [];

        if ($httpRequest->isMethod('POST'))
        {
            return $this->redirect($this->generateUrl('app_begrafenisplannen_bevestiging'));
        }

        return $variables;
    }

    /**
     * @Route("/bevestiging")
     * @Template
     */
    public function bevestigingAction(Session $session, $slug = false, Request $httpRequest, CommonGroundService $commonGroundService, ApplicationService $applicationService)
    {
        $variables = [];
        if ($httpRequest->isMethod('POST')) {
            return $this->redirect($this->generateUrl('app_default_index'));
        }
        return $variables;
    }

}
