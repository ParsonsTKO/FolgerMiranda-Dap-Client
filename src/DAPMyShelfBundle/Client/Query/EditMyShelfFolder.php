<?php declare(strict_types=1);

namespace DAPMyShelfBundle\Client\Query;

use Ramsey\Uuid\UuidInterface;
use DAPClientBundle\Client\Query\QueryInterface;

class EditMyShelfFolder implements QueryInterface
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
            if (!is_bool($value)) {
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
            'EditShelfFolder%s {
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
            $this->queryArguments['shelfTag'] = $filters['shelftag'];
        }

        if (!empty($filters['tagname'])) {
            $this->queryArguments['tagName'] = trim($filters['tagname']);
        }

        if (isset($filters['tagnotes'])) {
            $this->queryArguments['tagNotes'] = preg_replace('/(\r\n|\r|\n)+/', ' ', $filters['tagnotes']);
        }

        if (isset($filters['ispublic'])) {
            $this->queryArguments['isPublic'] =  filter_var($filters['ispublic'], FILTER_VALIDATE_BOOLEAN);
        }
    }
}
