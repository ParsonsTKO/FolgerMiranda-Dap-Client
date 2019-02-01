<?php declare(strict_types=1);

namespace DAPClientBundle\Model;

use Symfony\Component\Security\Core\User\EquatableInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class User implements
    UserInterface,
    EquatableInterface
{
    public const ROLE_USER  = 'ROLE_USER';

    /**
     * @var string
     */
    private $apiKey;

    /**
     * @var string
     */
    private $username;

    /**
     * @var string
     */
    private $email;

    /**
     * @var null|string
     */
    private $displayName;

    /**
     * @var bool
     */
    private $enabled;

    /**
     * @param string $apiKey
     * @param string $username
     * @param string $email
     * @param null|string $displayName
     * @param bool $enabled
     */
    public function __construct(
        string $apiKey,
        string $username,
        string $email,
        ?string $displayName,
        bool $enabled
    ) {
        $this->apiKey = $apiKey;
        $this->username = $username;
        $this->email = $email;
        $this->displayName = $displayName;
        $this->enabled = $enabled;
    }

    /**
     * @return User
     */
    public static function createInvalid() : User
    {
        return new static(
            'invalid',
            'invalid',
            'invalid',
            null,
            false
        );
    }

    /**
     * @return string
     */
    public function getApiKey() : string
    {
        return $this->apiKey;
    }

    /**
     * {@inheritdoc}
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * @return string
     */
    public function getEmail() : string
    {
        return $this->email;
    }

    /**
     * @return null|string
     */
    public function getDisplayName() : ?string
    {
        return $this->displayName;
    }

    /**
     * @return string
     */
    public function getDisplayableName() : string
    {
        /** Change order for fallback priority */
        foreach ([
            $this->displayName,
            $this->username,
            $this->email
        ] as $value) {
            if ($value !== null) {
                return $value;
            }
        }

        /** This will never happen as email and username are required, but here just in case */
        return 'user';
    }

    /**
     * @return bool
     */
    public function isEnabled() : bool
    {
        return $this->enabled;
    }

    /**
     * {@inheritdoc}
     */
    public function isEqualTo(UserInterface $user)
    {
        if (!$user instanceof User) {
            return false;
        }

        if ($this->apiKey !== $user->apiKey) {
            return false;
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function eraseCredentials()
    {
        // Do nothing
    }

    /**
     * {@inheritdoc}
     */
    public function getPassword()
    {
        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function getRoles()
    {
        return [
            self::ROLE_USER,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getSalt()
    {
        return null;
    }

    /**
     * @return array
     */
    public function toArray() : array
    {
        return [
            'api_key'       => $this->apiKey,
            'email'         => $this->email,
            'display_name'  => $this->displayName,
            'enabled'       => $this->enabled,
            'username'      => $this->username,
        ];
    }

    /**
     * @param array $array
     * @return User
     * @throws \Exception
     */
    public static function fromArray(array $array) : User
    {
        foreach ([
            'api_key',
            'email',
            'display_name',
            'enabled',
            'username',
        ] as $key) {
            if (!array_key_exists($key, $array)) {
                throw new \Exception(sprintf(
                    'User data must have a "%s" key. Actual keys are: "%s"',
                    $key,
                    implode('", "', array_keys($array))
                ));
            }
            
            if ('enabled' === $key) {
                if (!is_bool($array['enabled'])) {
                    throw new \Exception(sprintf(
                        'User data "enabled" is expected to be a boolean, "%s" provided',
                        is_object($array['enabled']) ? get_class($array['enabled']) : gettype($array['enabled'])
                    ));
                }
            } else {
                if (!is_string($key)) {
                    throw new \Exception(sprintf(
                        'User data "%s" is expected to be a string, "%s" provided',
                        $key,
                        is_object($array[$key]) ? get_class($array[$key]) : gettype($array[$key])
                    ));
                }
            }
        }

        return new static(
            $array['api_key'],
            $array['username'],
            $array['email'],
            $array['display_name'],
            $array['enabled']
        );
    }
}