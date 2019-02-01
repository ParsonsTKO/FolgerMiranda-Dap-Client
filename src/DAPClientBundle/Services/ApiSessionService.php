<?php

namespace DAPClientBundle\Services;

use DAPClientBundle\Services\SearchService;

class ApiSessionService
{
    private $searchService;

    public function __construct(SearchService $searchService)
    {
        $this->searchService = $searchService;
    }

    public function getUserSession($cookies)
    {
        if (!$cookies->has('userData')) {
            throw new \Exception("User not logged in");
        }

        $userData = json_decode($cookies->get('userData'));

        if (!$this->searchService->validateUUID($userData->apikey)) {
            throw new \Exception("API key not valid");
        }
            
        if (empty($userData->name)) {
            throw new \Exception("Name invalid");
        }

        return $userData;
    }

    public function getUserCallbackData($requestQuery)
    {
        $name = $requestQuery->get('displayname');
        $apikey = $requestQuery->get('api-key');

        if (empty($name)) {
            $name = $requestQuery->get('username');
        }

        if (empty($name)) {
            throw new \Exception("Display name or username is invalid");
        } else {
        }

        if (!$this->searchService->validateUUID($apikey)) {
            throw new \Exception("API key not valid");
        }
            
        return json_encode(array("name" => $name, "apikey" => $apikey));
    }
}
