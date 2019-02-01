<?php declare(strict_types=1);

namespace DAPClientBundle\Security\UserChecker;

use DAPClientBundle\Model\User;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class IsEnabledUserChecker implements UserCheckerInterface
{
    /**
     * @param UserInterface|User $user
     */
    public function checkPreAuth(UserInterface $user)
    {
        if (!$user instanceof User) {
            return;
        }

        if (!$user->isEnabled()) {
            throw new AuthenticationException('Your account has been disabled.');
        }
    }

    public function checkPostAuth(UserInterface $user)
    {
        return;
    }
}