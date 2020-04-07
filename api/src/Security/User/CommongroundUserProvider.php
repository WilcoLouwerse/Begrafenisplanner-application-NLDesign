<?php

// src/Security/User/CommongroundUserProvider.php

namespace App\Security\User;

use App\Service\CommonGroundService;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class CommongroundUserProvider implements UserProviderInterface
{
    private $params;
    private $commonGroundService;

    public function __construct(ParameterBagInterface $params, CommonGroundService $commonGroundService)
    {
        $this->params = $params;
        $this->commonGroundService = $commonGroundService;
    }

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

    private function fetchUser($username)
    {
        $users = $this->commonGroundService->getResourceList($this->params->get('auth_provider_user').'/users', ['username'=> $username]);
        $users = $users['hydra:member'];

        if (!$users || count($users) < 1) {
            throw new UsernameNotFoundException(
                sprintf('User "%s" does not exist.', $uuid)
            );
        }

        $user = $users[0];

        if(!in_array('ROLE_USER', $user['roles'])) {
            $user['roles'][] = 'ROLE_USER';
        }

        return new CommongroundUser($user['username'], $user['id'], null, $user['roles'], $user['person'], $user['organization']);
    }
}
