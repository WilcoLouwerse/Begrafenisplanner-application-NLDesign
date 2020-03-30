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
 * @Route("/grave")
 */
class GraveController extends AbstractController
{
    private $commonGroundService;

    /**
     * @Route("/add")
     * @Template
     */
    public function addAction(Session $session, $slug = false, Request $httpRequest, CommonGroundService $commonGroundService, ApplicationService $applicationService)
    {
        $this->commonGroundService = $commonGroundService;

        $test = [];
        $test['testText'] = "Graves :";

        $graves = [];
        $graves = $this->commonGroundService->createResource($graves, $this->commonGroundService->getComponent('grc')['href'].'/graves');

        $test['graves'] = $graves;

        return $test;
    }

}
