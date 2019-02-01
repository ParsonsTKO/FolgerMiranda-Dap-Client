<?php declare(strict_types=1);

namespace DAPClientBundle\Client\Query;

class CurrentUser implements QueryInterface
{
    /**
     * {@inheritdoc}
     */
    public function toGQL() : string
    {
        return 'currentUser {
            username
            email
            displayName
            enabled
        }';
    }
}