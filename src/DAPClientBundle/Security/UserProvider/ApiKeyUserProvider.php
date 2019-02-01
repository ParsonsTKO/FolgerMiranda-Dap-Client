<?php declare(strict_types=1);

namespace DAPClientBundle\Security\UserProvider;

use DAPClientBundle\Model\User;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;

class ApiKeyUserProvider implements UserProviderInterface
{
    const COOKIE_ATTR_NAME = '_api-key';

    /**
     * @var RequestStack
     */
    private $requestStack;

    /**
     * @var string
     */
    private $graphQlEndpoint;

    /**
     * @param RequestStack $requestStack
     * @param string $graphQlEndpoint
     */
    public function __construct(
        RequestStack $requestStack,
        string $graphQlEndpoint
    ) {
        $this->requestStack = $requestStack;
        $this->graphQlEndpoint = $graphQlEndpoint;
    }

    /**
     * {@inheritdoc}
     */
    public function loadUserByUsername($apiKey)
    {
        return $this->fetchUser($apiKey);
    }

    /**
     * {@inheritdoc}
     */
    public function refreshUser(UserInterface $user)
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf(
                'Instances of "%s" are not supported.',
                get_class($user)
            ));
        }

        return $this->fetchUser($user->getApiKey());
    }

    /**
     * {@inheritdoc}
     */
    public function supportsClass($class)
    {
        return User::class === $class;
    }

    /**
     * @param string $apiKey
     * @return User|null
     */
    private function fetchUser(string $apiKey) : ?User
    {
        if (null !== $user = $this->fetchUserFromGraphQl($apiKey)) {
            if (null !== $request = $this->requestStack->getMasterRequest()) {
                $request->attributes->set(self::COOKIE_ATTR_NAME, true);
            }

            return $user;
        }

        return User::createInvalid();
    }

    /**
     * @param string $apiKey
     * @return User|null
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    private function fetchUserFromGraphQl(string $apiKey) : ?User
    {
        $client = new Client();

        try {
            $response = $client->request('GET', $this->graphQlEndpoint, [
                'headers'       => [
                    'Accept'    => 'application/json',
                ],
                'query'         => [
                    'api-key'   => $apiKey,
                    'query'     => '{ currentUser { username email displayName enabled }}'
                ]
            ]);
        } catch (RequestException $exception) {
            if (null !== $exception->getResponse()) {
                // If GraphQL query throw a NotAuthenticated return invalid user to force log out
                if (403 === $exception->getResponse()->getStatusCode()) {
                    return User::createInvalid();
                }
            }

            // Log and handle
            throw new \Exception('error communicating with backend');
        }

        $json = json_decode($response->getBody()->getContents(), true);

        if (JSON_ERROR_NONE !== json_last_error() ) {
            throw new \Exception('not json from graphql');
        }

        if (!is_array($json) || empty($json['data']['currentUser'])) {
            return null;
        }

        $userData = $json['data']['currentUser'];

        return new User(
            $apiKey,
            $userData['username'],
            $userData['email'],
            $userData['displayName'],
            $userData['enabled']
        );
    }
}