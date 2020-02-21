<?php

// src/Security/User/CommongroundUserProvider.php
namespace App\Security\User;

use App\Security\User\CommongroundUser;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;

class CommongroundUserProvider implements UserProviderInterface
{
	public function loadUserByUsername($username)
	{
		return $this->fetchUser($username);
	}
	
	public function refreshUser(UserInterface $user)
	{
		if (!$user instanceof CommongroundUser) {
			throw new UnsupportedUserException(
					sprintf('Instances of "%s" are not supported.', get_class($user))
					);
		}
		
		$username = $user->getUsername();
		
		return $this->fetchUser($username);
	}
	
	public function supportsClass($class)
	{
		return CommongroundUser::class === $class;
	}
	
	private function fetchUser($uuid)
	{
		// make a call to your webservice here
		//$userData = ...
		// pretend it returns an array on success, false if there is no user
		
		//if ($userData) {
		//	$password = '...';
			
			// ...
			
			return new CommongroundUser('Default User', $uuid, null, ['user']);
		//}
		
		throw new UsernameNotFoundException(
				sprintf('User "%s" does not exist.', $uuid)
				);
	}
}