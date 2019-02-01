<?php
/**
 * File containing the SearchService class.
 *
 * (c) http://parsonstko.com/
 * (c) Developer jdiaz
 */

namespace DAPClientBundle\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7;
use GuzzleHttp\Exception\RequestException;
use DAPClientBundle\Pagination\Pagerfanta\SearchAdapter;
use DAPClientBundle\Pagination\Pagerfanta\Pagerfanta;
use DAPClientBundle\Client\Query\Record;
use DAPClientBundle\Client\Client as GraphQLClient;

class SearchService
{
    /**
     * @var Client
     */
    private $graphQlClient;

    /**
     * @var UUIDv4Pattern
     */
    private $UUIDv4Pattern;

    /**
     * @var Container
     */
    private $client;

    /**
     * @var array
     */
    public $searchSettings;

    public function __construct(GraphQLClient $graphQlClient, $searchSettings)
    {
        $this->graphQlClient = $graphQlClient;
        $this->searchSettings = $searchSettings;
        $this->UUIDv4Pattern = '/^[0-9A-F]{8}-[0-9A-F]{4}-4[0-9A-F]{3}-[89AB][0-9A-F]{3}-[0-9A-F]{12}$/i';
    }

    /**
     * Get content by HTTP Client.
     *
     * @param array $viewSettings
     *
     * return
     */
    public function getContent($type, $viewSettings)
    {
        try {
            $client = new Client();
            $method = $viewSettings['method'];
            $endpoint = $viewSettings['endpoint'];
            $options = [
                    $viewSettings[$type][$method.'_option'] => [
                    $viewSettings[$type][$method.'_option_param'] => $viewSettings[$type][$method.'_option_value'],
                ],
            ];

            $response = $client->request($method, $endpoint, $options);

            return json_decode($response->getBody());
        } catch (\Exception $e) {
            throw new \InvalidArgumentException('Validation could not be found. Error: '.$e->getMessage());
        }
    }

    /**
     * Validate a UUID val.
     *
     * @param string $value
     *
     * return
     */
    public function validateUUID($value)
    {
        try {
            return preg_match($this->UUIDv4Pattern, $value);
        } catch (\Exception $e) {
            throw new \InvalidArgumentException('Validation could not be found. Error: '.$e->getMessage());
        }
    }

    /**
     * Sent HTTP Request with GuzzleHttp\Client to GraphQL API.
     *
     * @param
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */

    public function sendHttpRequest($url, $data = array())
    {
        $client = new Client();
        $res = $client->post($url, [
            'body' => $data,
            'headers' => [
                'Content-Type' => 'application/json',
            ]
        ]);

        $body = $res->getBody();
        $response = json_decode($body);

        if (!$response) {
            throw new \UnexpectedValueException("Endpoint '".$url."' response empty or invalid");
        }

        return $response;
    }

    public function buildJsonQuery($getParams)
    {
        $searchTerm = $getParams['searchTerm'];
        $searchSettings = $this->searchSettings;
        $filter = $searchSettings['views']['result']['GET_option_value'];
        $languagefilter = $getParams['languagefilter'];
        $format = $getParams['format'];
        $genre = $getParams['genre'];
        $createdFrom = $getParams['createdFrom'];
        $createdUntil = $getParams['createdUntil'];

        if (isset($getParams['facets'])) {
            $facets = json_encode($getParams['facets']);
            $facets = str_replace('"', "'", $facets);
        } else {
            $facets = '';
        }
        if (isset($getParams['pageSize'])) {
            $pagesize = $getParams['pageSize'];
        } else {
            $pagesize = 10;
        }
        if (isset($getParams['page'])) {
            $pagenumber = $getParams['page'];
        } else {
            $pagenumber = 0;
        }

        if (array_key_exists('availableOnline', $getParams) && $getParams['availableOnline'] == 'on') {
            $availableOnline = "folger_related_itemsORfile_info";
        } else {
            $availableOnline = "";
        }

        //$query = '{ "query": "{search(searchText: \"' . $searchTerm . '\",language: \"' . $languagefilter . '\",format: \"' . $format . '\",genre: \"' . $genre .'\",createdFrom: \"' . $createdFrom . '\",createdUntil: \"' . $createdUntil . '\",availableOnline: \"' . $availableOnline .  '\",facets: \"' . $facets . '\", pagenumber: \"' . $pagenumber . '\", pagesize: \"' . $pagesize . '\")'. $filter .',pagination(pagenumber: \"' . $pagenumber . '\"){index,count,total}}" }';
        return [
          "params" => [
            "searchText" => $searchTerm,
            "language" => $languagefilter,
            "format" => $format,
            "genre" => $genre,
            "createdFrom" => $createdFrom,
            "createdUntil" => $createdUntil,
            "availableOnline" => $availableOnline,
            "facets" => $facets,
            "offset" => $pagenumber,
            "pagesize" => $pagesize,
          ],
          "schema" => $filter.',pagination(offset: "' . $pagenumber . '"){index,count,total}',
        ];
    }

    public function getSearchInputs($request)
    {
        //get user inputs that we care about
        $userSearch = array();
        $userSearch['searchTerm'] = $request->query->get('searchterm');
        $userSearch['searchPhrase'] = $request->query->get('searchphrase');
        $userSearch['phraseField'] = $request->query->get('phrasefield');
        $userSearch['phraseFieldSearch'] = $request->query->get('phrasefieldsearch');
        $userSearch['filter'] = is_null($request->query->get('filter')) ? [] : explode("||", $request->query->get('filter'));
        $userSearch['filterValue'] = is_null($request->query->get('filtervalue')) ? [] : explode("||", $request->query->get('filtervalue'));
        $userSearch['rangeField'] = is_null($request->query->get('rangefield')) ? [] : explode("||", $request->query->get('rangefield'));
        $userSearch['rangeMin'] = is_null($request->query->get('rangemin')) ? [] : explode("||", $request->query->get('rangemin'));
        $userSearch['rangeMax'] = is_null($request->query->get('rangemax')) ? [] : explode("||", $request->query->get('rangemax'));
        $userSearch['rangeDemote'] = is_null($request->query->get('rangedemote')) ? [] : explode("||", $request->query->get('rangedemote')); //set to 1 to move the range filter into the collected query parts
        $userSearch['facetName'] = $request->query->get('facetname');
        $userSearch['facetField'] = $request->query->get('facetfield');
        $userSearch['pageNumber'] = $request->query->get('pagenumber');
        $userSearch['page'] = $request->query->get('page');
        $userSearch['pageSize'] = $request->query->get('pagesize');
        $userSearch['createdFrom'] = $request->query->get('createdfrom');
        $userSearch['createdUntil'] = $request->query->get('createduntil');
        $userSearch['createdDateSearch'] = $request->query->get('createddatesearch');
        $userSearch['creatorSearch'] = $request->query->get('creatorsearch');
        $userSearch['timeperiod'] = $request->query->get('timeperiod');
        $userSearch['languagefilter'] = $request->query->get('languagefilter');
        $userSearch['facets'] = $request->query->get('facets');
        $userSearch['format'] = ($request->query->get('format'));
        $userSearch['genre'] = ($request->query->get('genre'));
        $userSearch['address'] = $request->query->get('address');
        $userSearch['locality'] = $request->query->get('locality');
        $userSearch['callNumber'] = $request->query->get('callnumber');
        $userSearch['refine'] = "";
        $userSearch['refineto'] ="";
        $userSearch['availableOnline'] = $request->query->get('availableonline');
        //end get user inputs that we care about


        //Facets
        if ($userSearch['refine'] && $userSearch['refineto']) {
            switch (strtolower($userSearch['refine'])) {
                //Turning the era into its ranges
                case 'era':
                    switch (strtolower($userSearch['refineto'])) {
                        case 'until 1600':
                            array_push($userSearch['rangeField'], 'date_created.iso_date');
                            array_push($userSearch['rangeMin'], null);
                            array_push($userSearch['rangeMax'], 1600);
                            break;
                        case '1600-1700':
                            array_push($userSearch['rangeField'], 'date_created.iso_date');
                            array_push($userSearch['rangeMin'], 1600);
                            array_push($userSearch['rangeMax'], 1700);
                            break;
                        case '1700-1800':
                            array_push($userSearch['rangeField'], 'date_created.iso_date');
                            array_push($userSearch['rangeMin'], 1700);
                            array_push($userSearch['rangeMax'], 1800);
                            break;
                        case '1800-1900':
                            array_push($userSearch['rangeField'], 'date_created.iso_date');
                            array_push($userSearch['rangeMin'], 1800);
                            array_push($userSearch['rangeMax'], 1900);
                            break;
                        case '1900-2000':
                            array_push($userSearch['rangeField'], 'date_created.iso_date');
                            array_push($userSearch['rangeMin'], 1900);
                            array_push($userSearch['rangeMax'], 2000);
                            break;
                        case 'from 2000':
                            array_push($userSearch['rangeField'], 'date_created.iso_date');
                            array_push($userSearch['rangeMin'], 2000);
                            array_push($userSearch['rangeMax'], null);
                            break;
                    }
                    break;
                case 'media_types':
                case 'media_format':
                    array_push($userSearch['filter'], 'format');
                    array_push($userSearch['filterValue'], $userSearch['refineto']);
                    break;
                case 'folger_genres':
                    array_push($userSearch['filter'], 'folger_genre.terms');
                    array_push($userSearch['filterValue'], $userSearch['refineto']);
                    break;
            }
        }
        //end Facets
        return $userSearch;
    }
    
    /**
      * @param
      * @return object
      */
    
    public function getRecordById($dapID)
    {
        if (!$this->validateUUID($dapID)) {
            throw new \InvalidArgumentException("Invalid dapID");
        }

        $client = $this->graphQlClient;
        $client->addQuery(new Record(['dapId' => $dapID]));
        $result = $client->send();
        $record = reset($result["records"]);

        if (!isset($record)) {
            throw new \UnexpectedValueException("Invalid dapId");
        } elseif (empty($record)) {
            throw new \UnexpectedValueException("Record empty or with errors");
        }

        return $record;
    }
    public function configDisplayFacets(array $facets)
    {
        $labelFacets = $this->searchSettings['facets'];
        $facetsconfigured = [];
        
        foreach ($facets as $index => $resultItem) {
            $resultItem = (object) $resultItem;
            if (array_key_exists($resultItem->facet, $labelFacets)) {
                $facetLabel = $labelFacets[$resultItem->facet];
                $facetsconfigured[$facetLabel][$index] = $resultItem;
            }
        }
        return $facetsconfigured;
    }
}
