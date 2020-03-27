<?php

// src/Security/TokenAuthenticator.php
namespace App\Security;

use App\Security\User\CommongroundUser;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\Exception\InvalidCsrfTokenException;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface; 
use Symfony\Component\Security\Guard\AbstractGuardAuthenticator;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use App\Service\CommonGroundService;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class CommongroundUserAuthenticator extends AbstractGuardAuthenticator
{
	private $em;
	private $params;
	private $commonGroundService;
	private $csrfTokenManager;
	private $router;
	private $urlGenerator;
	
	public function __construct(EntityManagerInterface $em, ParameterBagInterface $params, CommonGroundService $commonGroundService, CsrfTokenManagerInterface $csrfTokenManager, RouterInterface $router, UrlGeneratorInterface $urlGenerator)
	{
		$this->em = $em;
		$this->params = $params;
		$this->commonGroundService = $commonGroundService;
		$this->csrfTokenManager = $csrfTokenManager;
		$this->router = $router;
		$this->urlGenerator= $urlGenerator;
	}
	
	/**
	 * Called on every request to decide if this authenticator should be
	 * used for the request. Returning false will cause this authenticator
	 * to be skipped.
	 */
	public function supports(Request $request)
	{
		return 'app_user_login' === $request->attributes->get('_route')
		&& $request->isMethod('POST');
	}
	
	/**
	 * Called on every request. Return whatever credentials you want to
	 * be passed to getUser() as $credentials.
	 */
	public function getCredentials(Request $request)
	{
		$credentials = [
				'username' => $request->request->get('username'),
				'password' => $request->request->get('password'),
				'csrf_token' => $request->request->get('_csrf_token'),
		];
		
		$request->getSession()->set(
				Security::LAST_USERNAME,
				$credentials['username']
				);
		
		return $credentials;
	}
	
	public function getUser($credentials, UserProviderInterface $userProvider)
	{
		/*
		$token = new CsrfToken('authenticate', $credentials['csrf_token']);
		if (!$this->csrfTokenManager->isTokenValid($token)) {
			throw new InvalidCsrfTokenException();
		}		
		*/ 
		
		$users = $this->commonGroundService->getResourceList($this->params->get('auth_provider_user').'/users',["username"=> $credentials['username']], true);
		$users = $users["hydra:member"];
				
		if(!$users ||count($users) < 1){
			return;			
		}
		
		$user = $users[0];
				
		return new CommongroundUser($user['username'], $user['id'], null, ['ROLE_USER'],$user['person'],$user['organization']);
	}
	
	public function checkCredentials($credentials, UserInterface $user)
	{
		
		$user = $this->commonGroundService->createResource($credentials, $this->params->get('auth_provider_user').'/login');
		
		if(!$user){
			return false;			
		}
		
		// no adtional credential check is needed in this case so return true to cause authentication success
		return true;
	}
	
	public function onAuthenticationSuccess(Request $request, TokenInterface $token, $providerKey)
	{		
		return new RedirectResponse($this->urlGenerator->generate('app_user_dashboard'));
	}
	
	public function onAuthenticationFailure(Request $request, AuthenticationException $exception)
	{
		var_dump('noooz');
		
		return new RedirectResponse($this->urlGenerator->generate('app_user_login'));
	}
	
	/**
	 * Called when authentication is needed, but it's not sent
	 */
	public function start(Request $request, AuthenticationException $authException = null)
	{
		return new RedirectResponse($this->urlGenerator->generate('app_user_login'));
	}
	
	public function supportsRememberMe()
	{
		return true;
	}
	
	protected function getLoginUrl()
	{
		return $this->router->generate('app_user_login');
	}
}