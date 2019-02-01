<?php declare(strict_types=1);

namespace DAPClientBundle\Client\Query;

interface QueryInterface
{
    /**
     * Convert query object to GQL string
     *
     * @return string
     */
    public function toGQL() : string;
}