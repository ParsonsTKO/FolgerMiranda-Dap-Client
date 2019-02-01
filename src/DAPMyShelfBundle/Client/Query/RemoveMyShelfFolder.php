<?php declare(strict_types=1);

namespace DAPMyShelfBundle\Client\Query;

use Ramsey\Uuid\UuidInterface;
use DAPClientBundle\Client\Query\QueryInterface;

class RemoveMyShelfFolder implements QueryInterface
{
    /**
     * @var array
     */
    private $queryArguments = [];

    /**
     * @param array $filters
     */
    public function __construct(array $filters = [])
    {
        $this->convertFiltersToQueryArguments($filters);
    }

    /**
     * {@inheritdoc}
     */
    public function toGQL() : string
    {
        $arguments = [];

        foreach ($this->queryArguments as $name => $value) {

            if(!is_bool($value)){
                $arguments[] = sprintf(
                    '%s: "%s"',
                    $name,
                    str_replace('"', "'", $value)
                );
            } else {
                $arguments[] = sprintf($name.':%s', $value ? "true" : "false");
            }
        }

        $argumentsString = '';

        if (!empty($arguments)) {
            $argumentsString = sprintf(
                ' (%s)',
                implode("\n", $arguments)
            );
        }

        return sprintf(
            'UnShelfFolder%s {
                success
            }',
            $argumentsString
        );
    }

    /**
     * @param array $filters
     */
    private function convertFiltersToQueryArguments(array $filters) : void
    {

        if (!empty($filters['shelftag'])) {
            $this->queryArguments['shelfTag'] =  $filters['shelftag'];
        }

        if (isset($filters['withprejudice'])) {
            $this->queryArguments['withPrejudice'] =  filter_var($filters['withprejudice'], FILTER_VALIDATE_BOOLEAN);
        } else {
            //Since the UnShelfFolder is executed in a single request, by default withprejudice is false
            $this->queryArguments['withPrejudice'] =  filter_var(false, FILTER_VALIDATE_BOOLEAN);
        }

    }
}
