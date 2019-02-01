<?php
/**
 * File containing the CCBSearchAdapter class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
 */

namespace DAPClientBundle\Pagination\Pagerfanta;

use Pagerfanta\Adapter\AdapterInterface;
use GuzzleHttp\Psr7;
use GuzzleHttp\Exception\RequestException;
use DAPClientBundle\Client\Client;
use DAPClientBundle\Client\Query\Facets;
use DAPClientBundle\Client\Query\FeaturedResult;
use DAPClientBundle\Client\Query\Pagination;
use DAPClientBundle\Client\Query\Search;

class SearchAdapter implements AdapterInterface
{
    /**
     * @var int
     */
    private $nbResults;
    /**
     * @var array
     */
    private $params;
    /**
     * @var array
     */
    private $results;
    /**
     * Constructor.
     *
     * @param string $searchTerm
     */
    public function __construct(Client $client, $params)
    {
        $this->client = $client;
        $this->params = $params;
    }
    /**
     * Returns the number of results.
     *
     * @return integer The number of results.
     */
    public function getNbResults()
    {
        if (isset($this->results)) {
            $this->nbResults = reset($this->results["pagination"])["total"];
        } else {
            $searchResults = $this->doSearch($this->params);
            $this->nbResults = reset($searchResults["pagination"])["total"];
        }

        return $this->nbResults;
    }
    /**
     * Returns as slice of the results, as SearchHit objects.
     *
     * @param integer $offset The offset.
     * @param integer $length The length
     */
    public function getSlice($offset, $length)
    {
        $params = $this->params;
        $params["offset"] = $offset;
        $params["pagesize"] = $length;
        $searchResults = $this->doSearch($params);
        $this->nbResults = reset($searchResults["pagination"])["total"];
        return $this->results = $searchResults;
    }

    /**
     * Executes the query
     *
     * @return string $query
     */
    private function doSearch(array $params = [])
    {
        $page = array_key_exists("page", $params) ? $params["page"] : 1;
        $pagesize = array_key_exists("pagesize", $params) ? $params["pagesize"] : 10;
        $client = $this->client;
        $client->addQuery(new Search($params));
        $client->addQuery(new FeaturedResult($params["searchterm"]));
        $client->addQuery(new Pagination(($page - 1) * $pagesize));
        $client->addQuery(new Facets());

        $result = $client->send();

        if (!array_key_exists("pagination", $result) || !is_array($result["pagination"]) || !array_key_exists(0, $result["pagination"])) {
            throw new \UnexpectedValueException("Pagination in result from API is invalid");
        }

        if (!array_key_exists("search", $result) || !is_array($result["search"])) {
            throw new \UnexpectedValueException("Search in result from API is invalid");
        }

        if (!array_key_exists("facets", $result) || !is_array($result["facets"])) {
            throw new \UnexpectedValueException("Facets in result from API is invalid");
        }

        if (!array_key_exists("featuredResult", $result) || !is_array($result["featuredResult"])) {
            throw new \UnexpectedValueException("Featured results in result from API is invalid");
        }

        return $result;
    }
}
