<?php declare(strict_types=1);

namespace DAPClientBundle\Security\Factory;

use DAPClientBundle\Security\Authentication\ApiKeyAuthenticationProvider;
use DAPClientBundle\Security\Listener\ApiKeyAuthenticationListener;
use Symfony\Bundle\SecurityBundle\DependencyInjection\Security\Factory\AbstractFactory;
use Symfony\Component\DependencyInjection\ChildDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class ApiKeyFactory extends AbstractFactory
{
    /**
     */
    public function __construct()
    {
        $this->addOption('check_path', '/login-check');
        $this->addOption('api_key_parameter', 'api-key');
    }

    /**
     * @param ContainerBuilder $container
     * @param string $id
     * @param array $config
     * @param string $userProviderId
     * @return string
     */
    protected function createAuthProvider(ContainerBuilder $container, $id, $config, $userProviderId)
    {
        $providerId = ApiKeyAuthenticationProvider::class . '.' . $id;
        $container
            ->setDefinition($providerId, new ChildDefinition(ApiKeyAuthenticationProvider::class))
            ->setArgument(0, new Reference($userProviderId))
            ->setArgument(1, $id)
        ;

        return $providerId;
    }

    /**
     * {@inheritdoc}
     */
    public function getPosition()
    {
        return 'pre_auth';
    }

    /**
     * {@inheritdoc}
     */
    public function getKey()
    {
        return 'api_key';
    }

    /**
     * {@inheritdoc}
     */
    protected function getListenerId()
    {
        return ApiKeyAuthenticationListener::class;
    }
}