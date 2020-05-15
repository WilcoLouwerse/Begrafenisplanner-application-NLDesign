<?php

// src/Controller/DefaultController.php

namespace App\Controller;

use App\Service\CommonGroundService;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Session\Session;


/**
 * Class UserController.
 */
class UserController extends AbstractController
{
    /**
     * @Route("/login", methods={"GET"})
     * @Template
     */
    public function login(Request $request, CommonGroundService $commonGroundService, ParameterBagInterface $params, EventDispatcherInterface $dispatcher)
    {
        return [];
    }

    /**
     * @Route("/logout")
     * @Template
     */
    public function logoutAction(Session $session)
    {
        $session->set('requestType', null);
        $session->set('request', null);
        $session->set('user', null);
        $session->set('employee', null);
        $session->set('contact', null);

        $this->addFlash('info', 'U bent uitgelogd');
        return $this->redirect($this->generateUrl('app_process_index'));
    }
}
