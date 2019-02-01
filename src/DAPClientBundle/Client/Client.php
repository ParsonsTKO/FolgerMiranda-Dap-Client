<?php declare(strict_types=1);

namespace DAPClientBundle\Client;

use DAPClientBundle\Client\Query\QueryInterface;
use DAPClientBundle\Provider\UserProvider;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\RequestException;

class Client
{
    /**
     * @var string
     */
    private $guzzleClient;

    /**
     * @var UserProvider
     */
    private $userProvider;

    /**
     * @var string
     */
    private $graphQlEndpoint;

    /**
     * @var array
     */
    private $queries;

    /**
     * @param UserProvider $userProvider
     * @param string $graphQlEndpoint
     */
    public function __construct(
        UserProvider $userProvider,
        string $graphQlEndpoint
    ) {
        $this->userProvider = $userProvider;
        $this->guzzleClient = new GuzzleClient();
        $this->graphQlEndpoint = $graphQlEndpoint;

        $this->resetQueries();
    }

    /**
     *
     */
    public function resetQueries() : void
    {
        $this->queries = [];
    }

    /**
     * @param QueryInterface $query
     * @param string|null $alias
     */
    public function addQuery(QueryInterface $query, string $alias = null) : void
    {
        $this->queries[] = [
            'query' => $query,
            'alias' => $alias,
        ];
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function send() : array
    {
        $query = [
            'query' => $this->generateFullQueryString(),
        ];

        if (null !== $user = $this->userProvider->getUserFromTokenStorage()) {
            $query['api-key'] = $user->getApiKey();
        }

        try {
            $response = $this->guzzleClient->get($this->graphQlEndpoint, [
                'query' => $query,
            ]);
        } catch (RequestException $exception) {
            if (null === $response = $exception->getResponse()) {
                throw $exception;
            }
        }

        $json = $response->getBody()->getContents();
        $data = json_decode($json, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \Exception('data is not valid JSON');
        }

        if (isset($data['errors']) || !isset($data['data'])) {
            $messages = [];

            foreach ($data['errors'] ?? [] as $error) {
                $messages[] = $error['message'] ?? 'Problem';
            }

            if (0 === count($messages)) {
                $messages[] = 'No error messages found';
            }

            $this->resetQueries();
            //throw new \Exception('ERROR FROM DAP SERVER: ' . $json);
            //throw new \Exception(implode("\n", $messages));
        }

        return $data['data'];
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function mutation() : array
    {
        $query = [
            'query' => $this->generateFullMutationString(),
        ];

        if (null !== $user = $this->userProvider->getUserFromTokenStorage()) {
            $query['api-key'] = $user->getApiKey();
        }

        try {
            $response = $this->guzzleClient->get($this->graphQlEndpoint, [
                'query' => $query,
            ]);
        } catch (RequestException $exception) {
            if (null === $response = $exception->getResponse()) {
                throw $exception;
            }
        }

        $json = $response->getBody()->getContents();
        $data = json_decode($json, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \Exception('data is not valid JSON');
        }

        if (isset($data['errors']) || !isset($data['data'])) {
            $messages = [];

            foreach ($data['errors'] ?? [] as $error) {
                $messages[] = $error['message'] ?? 'Problem';
            }

            if (0 === count($messages)) {
                $messages[] = 'No error messages found';
            }

            $this->resetQueries();

            throw new \Exception(implode(", ", $messages));
        }
        return $data['data'];
    }

    /**
     * @return string
     */
    private function generateFullQueryString() : string
    {
        $query = 'query {' . "\n";

        foreach ($this->queries as $queryAndAlias) {
            $query .= $this->generateQueryStringForItem($queryAndAlias['query'], $queryAndAlias['alias']) . "\n";
        }

        $query .= '}';

        //   $query = preg_replace('/\n/', '', $query);
        //   $query = preg_replace('/ +/', ' ', $query);

        return $query;
    }

    /**
     * @return string
     */
    private function generateFullMutationString() : string
    {
        $query = 'mutation{' . "\n";

        foreach ($this->queries as $queryAndAlias) {
            $query .= $this->generateQueryStringForItem($queryAndAlias['query'], $queryAndAlias['alias']) . "\n";
        }

        $query .= '}';

        return $query;
    }

    /**
     * @param QueryInterface $query
     * @param string|null $alias
     * @return string
     */
    private function generateQueryStringForItem(QueryInterface $query, string $alias = null) : string
    {
        return sprintf(
            '%s%s%s',
            $alias ?: '',
            $alias ? ' : ' : '',
            $query->toGQL()
        );
    }
}
