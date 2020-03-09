<?php
// src/Controller/DefaultController.php
namespace App\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Doctrine\ORM\EntityManagerInterface;
use GuzzleHttp\Client;
use Knp\Bundle\MarkdownBundle\MarkdownParserInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use App\Service\CommonGroundService;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use App\Security\User\CommongroundUser;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Class UserController
 * @package App\Controller
 *
 */
class UserController extends AbstractController
{
	/**
	 * @Route("/login", methods={"GET"})
     * @Template
	 */
	public function login(Request $request, CommonGroundService $commonGroundService,  ParameterBagInterface $params, EventDispatcherInterface $dispatcher)
	{
		return [];
	}

	/**
     * @Route("/logout", methods={"GET"})
	 * @Template
	 */
	public function logout(Request $request, CommonGroundService $commonGroundService,  ParameterBagInterface $params, EventDispatcherInterface $dispatcher)
	{
		return [];
	}

}
