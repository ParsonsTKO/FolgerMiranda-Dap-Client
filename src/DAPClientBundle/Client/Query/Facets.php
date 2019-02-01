<?php declare(strict_types=1);

namespace DAPClientBundle\Client\Query;

/**
 * Must be added to the query after a search query
 */
class Facets implements QueryInterface
{
    /**
     * {@inheritdoc}
     */
    public function toGQL() : string
    {
        return 'facets {
            facet
            key
            count
        }';
    }

    /**
     * @param array $labels
     * @param array $facets
     * @return array
     */
    public static function reformatFacetsForTemplate(array $labels, array $facets) : array
    {
        $reformatted = [];

        foreach ($facets as $index => $facet) {
            if (array_key_exists($facet['facet'], $labels)) {
                $label = $labels[$facet['facet']];
                $reformatted[$label][$index] = $facet;
            }
        }

        return $reformatted;
    }
}