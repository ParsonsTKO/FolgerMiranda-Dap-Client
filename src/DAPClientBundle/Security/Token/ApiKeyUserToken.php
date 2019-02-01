<?php declare(strict_types=1);

namespace DAPClientBundle\Security\Token;

use Symfony\Component\Security\Core\Authentication\Token\AbstractToken;

class ApiKeyUserToken extends AbstractToken
{
    /**
     * @var string
     */
    private $apiKey;

    /**
     * @param string $apiKey
     * @param array $roles
     */
    public function __construct(
        string $apiKey,
        array $roles = []
    ) {
        parent::__construct($roles);

        $this->apiKey = $apiKey;

        $this->setAuthenticated(count($roles) > 0);
    }

    /**
     * {@inheritdoc}
     */
    public function getCredentials()
    {
        return $this->apiKey;
    }

    /**
     * @return string
     */
    public function getApiKey() : ?string
    {
        return $this->apiKey;
    }
}