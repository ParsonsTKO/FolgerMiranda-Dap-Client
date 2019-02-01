<?php


namespace DAPClientBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Cache;
use DAPClientBundle\Services\SearchService;

class MiradorController extends Controller
{

    /**
     * Search Using GraphQL API.
     * @Cache(smaxage="3600", maxage="3600", public=true)
     * @param $request \Symfony\Component\HttpFoundation\Request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
      
    public function miradorAction($dapID, SearchService $searchService)
    {
        try {
            if (!$searchService->validateUUID($dapID)) {
                throw new \InvalidArgumentException('Invalid ID.');
            }
            $manifest_endpoint = $this->getParameter("dap_client.search")["views"]["detail"]["manifest_endpoint"];
            return $this->render(
            'DAPClientBundle:Mirador:mirador.html.twig',
              array(
                "manifest_uri" => $manifest_endpoint."/".$dapID.".json",
                )
        );
        } catch (\Exception $e) {
            throw $this->createNotFoundException('Page not found. Error: '.$e->getMessage());
        }
    }
}
