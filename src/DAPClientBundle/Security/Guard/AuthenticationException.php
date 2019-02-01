<?php declare(strict_types=1);

namespace DAPClientBundle\Security\Guard;

use Symfony\Component\Security\Core\Exception\AuthenticationException as BaseAuthenticationException;

class AuthenticationException extends BaseAuthenticationException
{
    /**
     * {@inheritdoc}
     */
    public function getMessageKey()
    {
        return $this->message;
    }
}