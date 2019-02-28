<?php

namespace App\Security;

use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\User\User as SecurityUser;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\User;

class ApiKeyUserProvider implements UserProviderInterface
{
    private $em;

    public function __construct(EntityManagerInterface $em){
        $this->em = $em;
    }

    public function getUsernameForApiKey($apiKey)
    {
        $user = $this->em->getRepository(User::class)->findOneBy(['apikey' => $apiKey]);
        if (is_null($user)){
            return null;
        }
        return $user->getEmail();
    }

    public function loadUserByUsername($username)
    {
        return new SecurityUser(
            $username,
            null,
            ['ROLE_API']
        );
    }

    public function refreshUser(UserInterface $user)
    {
        throw new UnsupportedUserException();
    }

    public function supportsClass($class)
    {
        return SecurityUser::class === $class;
    }
}

?>
