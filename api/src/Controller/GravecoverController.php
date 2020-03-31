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
use DateTimeZone;

/**
 * Class DeveloperController
 * @package App\Controller
 * @Route("/gravecover")
 */
class GravecoverController extends AbstractController
{
    /**
     * @Route("/view")
     * @Template
     */
    public function viewAction(Session $session, $slug = false, Request $httpRequest, CommonGroundService $commonGroundService, ApplicationService $applicationService)
    {
        $variables = [];

        $gravecovers = $commonGroundService->getResourceList($commonGroundService->getComponent('grc')['href'].'/grave_covers');

        $variables['gravecovers'] = $gravecovers;

        return $variables;
    }

    /**
     * @Route("/add")
     * @Template
     */
    public function addAction(Session $session, $slug = false, Request $httpRequest, CommonGroundService $commonGroundService, ApplicationService $applicationService)
    {
        $variables = [];
        $variables['gravecover'] = "";

        if(isset($_POST['Submit']))
        {
            $timezone = new DateTimeZone('Europe/Amsterdam');
            $date     = \DateTime::createFromFormat('yy-m-d H:m:s', 'yy-m-d H:m:s', $timezone);

            $gravecover = [];
            $gravecover['dateCreated'] = $date;
            $gravecover['dateModified'] = $date;
            $gravecover['description'] = $_POST['Description'];
            $gravecover['name'] = $_POST['Name'];
            $gravecover = $commonGroundService->createResource($gravecover, $commonGroundService->getComponent('grc')['href'].'/grave_covers');

            $variables['gravecover'] = $gravecover;
        }

        return $variables;
    }

}
