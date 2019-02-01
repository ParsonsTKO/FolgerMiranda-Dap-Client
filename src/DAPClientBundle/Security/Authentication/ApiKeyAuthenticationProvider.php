<?php declare(strict_types=1);

namespace DAPClientBundle\Security\Authentication;

use DAPClientBundle\Model\User;
use DAPClientBundle\Security\Token\ApiKeyUserToken;
use DAPClientBundle\Security\UserChecker\IsEnabledUserChecker;
use Symfony\Component\Security\Core\Authentication\Provider\AuthenticationProviderInterface;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class ApiKeyAuthenticationProvider implements AuthenticationProviderInterface
{
    /**
     * @var UserProviderInterface
     */
    private $userProvider;

    /**
     * @var string
     */
    private $providerKey;

    /**
     * @var UserCheckerInterface
     */
    private $userChecker;

    /**
     * @param UserProviderInterface $userProvider
     * @param string $providerKey
     */
    public function __construct(UserProviderInterface $userProvider, string $providerKey)
    {
        $this->userProvider = $userProvider;
        $this->providerKey = $providerKey;

        $this->userChecker = new IsEnabledUserChecker();
    }

    /**
     * {@inheritdoc}
     *
     * @param TokenInterface|ApiKeyUserToken $token
     */
    public function authenticate(TokenInterface $token)
    {
        /** @var User $user */
        if ($user = $this->userProvider->loadUserByUsername($token->getApiKey())) {
            $this->userChecker->checkPreAuth($user);
            $this->userChecker->checkPostAuth($user);

            $authenticatedToken = new ApiKeyUserToken(
                $user->getApiKey(),
                $user->getRoles()
            );
            $authenticatedToken->setUser($user);

            return $authenticatedToken;
        }

        throw new AuthenticationException('The API key authentication failed.');
    }

    /**
     * {@inheritdoc}
     */
    public function supports(TokenInterface $token)
    {
        return $token instanceof ApiKeyUserToken;
    }
}