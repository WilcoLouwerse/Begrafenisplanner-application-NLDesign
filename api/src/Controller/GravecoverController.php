<?php


namespace App\Controller;

use App\Service\ApplicationService;
//use App\Service\RequestService;
use App\Service\CommonGroundService;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class DeveloperController
 * @package App\Controller
 * @Route("/gravecover")
 */
class GravecoverController extends AbstractController
{
    private $commonGroundService;

    /**
     * @Route("/view")
     * @Template
     */
    public function viewAction(Session $session, $slug = false, Request $httpRequest, CommonGroundService $commonGroundService, ApplicationService $applicationService)
    {
        $this->commonGroundService = $commonGroundService;

        $variables = [];
        $variables['testText'] = "Gravecovers :";

        $gravecovers = [];
        $gravecovers['name'] = "";
        $gravecovers = $this->commonGroundService->getResourceList($this->commonGroundService->getComponent('grc')['href'].'/grave_covers');

        $variables['gravecovers'] = $gravecovers;


        return $variables;
    }

    /**
     * @Route("/add")
     * @Template
     */
    public function addAction(Session $session, $slug = false, Request $httpRequest, CommonGroundService $commonGroundService, ApplicationService $applicationService)
    {
        $this->commonGroundService = $commonGroundService;

        $variables = [];
        $variables['testText'] = "Gravecovers data";

        $gravecovers = [];
        //$gravecovers['name'] = "";
        //$gravecovers = $this->commonGroundService->getResourceList($this->commonGroundService->getComponent('grc')['href'].'/grave_covers');

        //$test['gravecovers'] = $gravecovers;

        return $variables;
    }

}
