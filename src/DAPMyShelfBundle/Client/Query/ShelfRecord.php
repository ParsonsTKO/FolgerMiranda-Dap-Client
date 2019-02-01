<?php declare(strict_types=1);

namespace DAPMyShelfBundle\Client\Query;

use Ramsey\Uuid\Uuid;
use DAPClientBundle\Client\Query\QueryInterface;

class ShelfRecord implements QueryInterface
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
            if (!is_integer($value)) {
                $arguments[] = sprintf(
                    '%s: "%s"',
                    $name,
                    str_replace('"', "'", $value)
                );
            } else {
                $arguments[] = sprintf($name.': %u', $value);
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
            'ShelfItem%s {
                success,
                MyShelf{MyShelfRecords{dapID}}
            }',
            $argumentsString
        );
    }

    /**
     * @param array $filters
     */
    private function convertFiltersToQueryArguments(array $filters) : void
    {
        if (!empty($filters['dapid'])) {
            $dapIdUuid = Uuid::fromString($filters['dapid']);
            $this->queryArguments['dapID'] = $dapIdUuid->toString();
        }

        if (isset($filters['notes'])) {
            $this->queryArguments['notes'] = trim(preg_replace('/(\r\n|\r|\n)+/', ' ', $filters['notes']));
        }

        if (isset($filters['shelftag'])) {
            $shelfTagId = $filters['shelftag'];
            if (!empty($shelfTagId)) {
                $shelfTagUuid = Uuid::fromString($shelfTagId);
                $shelfTagId = $shelfTagUuid->toString();
            }

            $this->queryArguments['shelfTag'] =  $shelfTagId ;
        }

        if (!empty($filters['sortorder'])) {
            $this->queryArguments['sortorder'] =  (int)$filters['sortorder'];
        }
    }
}
