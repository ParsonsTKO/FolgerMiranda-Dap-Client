<?php declare(strict_types=1);

namespace DAPMyShelfBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use DAPClientBundle\Client\Client;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use DAPMyShelfBundle\Client\Query\ShelfRecord;
use DAPMyShelfBundle\Client\Query\UnshelfRecord;
use DAPMyShelfBundle\Client\Query\MyItems;
use DAPMyShelfBundle\Client\Query\AllFolders;
use DAPMyShelfBundle\Client\Query\EditMyShelfFolder;
use DAPMyShelfBundle\Client\Query\RemoveMyShelfFolder;
use DAPMyShelfBundle\Client\Query\AddMyShelfFolder;
use DAPMyShelfBundle\Client\Query\EmptyAll;

class MyShelfJsonController extends Controller
{

    /**
     * @var Client
     */
    private $graphQlClient;

    /**
     * @var EngineInterface
     */
    private $templating;

    /**
     * @param Client $graphQlClient
     * @param EngineInterface $templating
     */
    public function __construct(
        Client $graphQlClient,
        EngineInterface $templating
    ) {
        $this->graphQlClient = $graphQlClient;
        $this->templating = $templating;
    }

    /**
     * @return string
     * @throws \Exception
     */
    public function shelfRecordAction(Request $request)
    {
        try {
            $params = array_merge($request->query->all(), $request->request->all());

            $this->graphQlClient->addQuery(new ShelfRecord($params));
            $data = $this->graphQlClient->mutation();
            
            return $this->json([
              "success" => $data['ShelfItem']['success'],
              "count" => count($data['ShelfItem']['MyShelf'][0]['MyShelfRecords']),
              ]);
        } catch (\Exception $e) {
            $this->get('dap_client.logger')->error($e->getMessage());
            $response = new Response();
            $response->setStatusCode(500);

            return $response;
        }
    }

    /**
     * @return string
     * @throws \Exception
     */
    public function unshelfRecordAction(Request $request)
    {
        try {
            $params = array_merge($request->query->all(), $request->request->all());

            $this->graphQlClient->addQuery(new UnshelfRecord($params));
            $data = $this->graphQlClient->mutation();

            return $this->json(["count" => count($data['UnShelfItem']['MyShelf'][0]['MyShelfRecords'])]);
        } catch (\Exception $e) {
            $this->get('dap_client.logger')->error($e->getMessage());
            $response = new Response();
            $response->setStatusCode(500);

            return $response;
        }
    }

    /**
     * @return string
     * @throws \Exception
     */
    public function allItemsAction()
    {
        try {
            $this->graphQlClient->addQuery(new MyItems());
            $data = $this->graphQlClient->send();
            $dapIds = array();
            foreach ($data['MyShelf'][0]['MyShelfRecords'] as $index => $recordDapID) {
                $dapIds[$index] = $recordDapID['dapID'];
            }
            
            return $this->json(array(
                'count' => count($data['MyShelf'][0]['MyShelfRecords']),
                'records' => $dapIds
            ));
        } catch (\Exception $e) {
            $this->get('dap_client.logger')->error($e->getMessage());
            $response = new Response();
            $response->setStatusCode(500);

            return $response;
        }
    }
    
    /**
     * @return string
     * @throws \Exception
     */
    public function allFoldersAction()
    {
        try {
            $this->graphQlClient->addQuery(new AllFolders());
            $data = $this->graphQlClient->send();

            $folders = array();
            foreach ($data['MyShelf'][0]['MyShelfFolders'] as $index => $folder) {
                $folders[$index]['MyShelfFolderName'] = $folder['MyShelfFolderName'];
                $folders[$index]['MyShelfFolderTag'] = $folder['MyShelfFolderTag'];
            }

            return $this->json(array(
                'count' => count($data['MyShelf'][0]['MyShelfFolders']),
                'folders' => $folders
            ));
        } catch (\Exception $e) {
            $this->get('dap_client.logger')->error($e->getMessage());
            $response = new Response();
            $response->setStatusCode(500);

            return $response;
        }
    }

    /**
     * @return string
     * @throws \Exception
     */
    public function addFolderAction(Request $request)
    {
        try {
            $params = array_merge($request->query->all(), $request->request->all());

            $this->graphQlClient->addQuery(new AddMyShelfFolder($params));
            $data = $this->graphQlClient->mutation();
            
            if(!$data['CreateShelfFolder']['success']){
              throw new \Exception('Operation failed! Please try again.');
            }


            return $this->json(['success' => $data['CreateShelfFolder']['success']]);
        } catch (\Exception $e) {
            $this->get('dap_client.logger')->error($e->getMessage());
            $response = $this->json(['error' => $e->getMessage()]);
            $response->setStatusCode(Response::HTTP_INTERNAL_SERVER_ERROR);
            return $response;
        }
    }

    /**
     * @return string
     * @throws \Exception
     */
    public function editFolderAction(Request $request)
    {
        try {
            $params = array_merge($request->query->all(), $request->request->all());

            $this->graphQlClient->addQuery(new EditMyShelfFolder($params));
            $data = $this->graphQlClient->mutation();
            
            if (!array_key_exists("success", $data['EditShelfFolder'])) {
                throw \Exception("Operation failed");
            }
            
            return $this->json(["success" => $data['EditShelfFolder']['success']]);
        } catch (\Exception $e) {
            $this->get('dap_client.logger')->error($e->getMessage());
            $response = new Response();
            $response->setStatusCode(500);

            return $response;
        }
    }

    /**
     * @return string
     * @throws \Exception
     */
    public function removeFolderAction(Request $request)
    {
        try {
            $params = array_merge($request->query->all(), $request->request->all());

            $this->graphQlClient->addQuery(new RemoveMyShelfFolder($params));
            $this->graphQlClient->mutation();
            
            return $this->json([]);
        } catch (\Exception $e) {
            $this->get('dap_client.logger')->error($e->getMessage());
            $response = new Response();
            $response->setStatusCode(500);

            return $response;
        }
    }

    /**
     * @return string
     * @throws \Exception
     */
    public function removeAllAction(Request $request)
    {
        try {
            $params = array_merge($request->query->all(), $request->request->all());

            $shelfTag = array_key_exists("shelftag", $params) ? $params["shelftag"] : null;
            $this->graphQlClient->addQuery(new EmptyAll($shelfTag));
            $this->graphQlClient->mutation();
            
            return $this->json([]);
        } catch (\Exception $e) {
            $this->get('dap_client.logger')->error($e->getMessage());
            $response = new Response();
            $response->setStatusCode(500);

            return $response;
        }
    }
}
