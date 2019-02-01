<?php declare(strict_types=1);

namespace DAPMyShelfBundle\Controller;

use DAPMyShelfBundle\Client\Query\EditMyShelfFolder;
use DAPMyShelfBundle\Client\Query\EmptyAll;
use DAPMyShelfBundle\Client\Query\MyFolder;
use DAPMyShelfBundle\Client\Query\MyItems;
use DAPMyShelfBundle\Client\Query\RemoveMyShelfFolder;
use DAPMyShelfBundle\Client\Query\ShelfRecord;
use DAPMyShelfBundle\Client\Query\UnshelfRecord;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use DAPClientBundle\Client\Client;
use DAPMyShelfBundle\Client\Query\AddMyShelfFolder;
use DAPClientBundle\Client\Query\CurrentUser;
use DAPMyShelfBundle\Client\Query\MyShelf;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use DAPClientBundle\Services\DownloadService;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

class MyShelfController extends Controller
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
    public function dashboardAction()
    {
        try {
            //$this->addDefaultQueries(); // Currentyl backend doesn't support the CurrentUser Parameter, only the apikey
            $this->graphQlClient->addQuery(new MyShelf());

            return $this->templating->renderResponse(
                'DAPMyShelfBundle:MyShelf:dashboard.html.twig',
                ['data' => $this->graphQlClient->send()]
            );
        } catch (\Exception $e) {
            $this->get('dap_client.logger')->error($e->getMessage());
            throw $this->createNotFoundException('MyShelf could not be found. Error: '.$e->getMessage());
        }
    }

    /**
     * @return string
     * @throws \Exception
     */
    public function folderAction($folderId)
    {
        try {
            $this->graphQlClient->addQuery(new MyFolder($folderId));

            return $this->templating->renderResponse(
                'DAPMyShelfBundle:MyShelf:folder.html.twig',
                ['data' => $this->graphQlClient->send()]
            );
        } catch (\Exception $e) {
            $this->get('dap_client.logger')->error($e->getMessage());
            throw $this->createNotFoundException('Folder could not be found. Error: '.$e->getMessage());
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

            if (array_key_exists("format", $params) && $params["format"] == "json") {
                return $this->forward('DAPMyShelfBundle:MyShelfJson:addFolder', array('request' => $request));
            }

            $this->graphQlClient->addQuery(new AddMyShelfFolder($params));
            $data = $this->graphQlClient->mutation();
            
            if (array_key_exists("redirect", $params) && !empty($params["redirect"])) {
                return $this->redirect($params["redirect"]);
            }

            return $this->redirectToRoute('dap_myshelf_dashboard');
        } catch (\Exception $e) {
            $this->get('dap_client.logger')->error($e->getMessage());
            throw $this->createNotFoundException('Folder could not be created. Error: '.$e->getMessage());
        }
    }

    /**
     * @return string
     * @throws \Exception
     */
    public function shelfRecordAction(Request $request)
    {
        try {
            $params = array_merge($request->query->all(), $request->request->all());

            if (array_key_exists("format", $params) && $params["format"] == "json") {
                return $this->forward('DAPMyShelfBundle:MyShelfJson:shelfRecord', array('request' => $request));
            }

            $this->graphQlClient->addQuery(new ShelfRecord($params));
            $data = $this->graphQlClient->mutation();

            if (array_key_exists("redirect", $params) && !empty($params["redirect"])) {
                return $this->redirect($params["redirect"]);
            }

            return $this->redirectToRoute('dap_myshelf_dashboard');
        } catch (\Exception $e) {
            $this->get('dap_client.logger')->error($e->getMessage());
            throw $this->createNotFoundException('Item could not be created. Error: '.$e->getMessage());
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

            if (array_key_exists("format", $params) && $params["format"] == "json") {
                return $this->forward('DAPMyShelfBundle:MyShelfJson:unshelfRecord', array('request' => $request));
            }

            $this->graphQlClient->addQuery(new UnshelfRecord($params));
            $data = $this->graphQlClient->mutation();

            if (array_key_exists("redirect", $params) && !empty($params["redirect"])) {
                return $this->redirect($params["redirect"]);
            }

            return $this->redirectToRoute('dap_myshelf_dashboard');
        } catch (\Exception $e) {
            $this->get('dap_client.logger')->error($e->getMessage());
            throw $this->createNotFoundException('Item could not be removed. Error: '.$e->getMessage());
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

            if (array_key_exists("format", $params) && $params["format"] == "json") {
                return $this->forward('DAPMyShelfBundle:MyShelfJson:removeAll', array('request' => $request));
            }

            $shelfTag = array_key_exists("shelftag", $params) ? $params["shelftag"] : null;
            $this->graphQlClient->addQuery(new EmptyAll($shelfTag));
            $this->graphQlClient->mutation();
            
            if (array_key_exists("redirect", $params) && !empty($params["redirect"])) {
                return $this->redirect($params["redirect"]);
            }
            
            return $response = $this->redirectToRoute('dap_myshelf_dashboard');
        } catch (\Exception $e) {
            $this->get('dap_client.logger')->error($e->getMessage());
            throw $this->createNotFoundException('Could not remove items. Error: '.$e->getMessage());
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
            
            if (array_key_exists("format", $params) && $params["format"] == "json") {
                return $this->forward('DAPMyShelfBundle:MyShelfJson:editFolder', array('request' => $request));
            }
            
            $this->graphQlClient->addQuery(new EditMyShelfFolder($params));
            $data = $this->graphQlClient->mutation();

            if (!array_key_exists("success", $data['EditShelfFolder'])) {
                throw \Exception("Operation failed");
            }

            if (array_key_exists("redirect", $params) && !empty($params["redirect"])) {
                return $this->redirect($params["redirect"]);
            }
            
            return $response = $this->redirectToRoute('dap_myshelf_dashboard');
        } catch (\Exception $e) {
            $this->get('dap_client.logger')->error($e->getMessage());
            throw $this->createNotFoundException('Folder could not be edited. Error: '.$e->getMessage());
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

            if (array_key_exists("format", $params) && $params["format"] == "json") {
                return $this->forward('DAPMyShelfBundle:MyShelfJson:removeFolder', array('request' => $request));
            }

            $this->graphQlClient->addQuery(new RemoveMyShelfFolder($request->query->all()));
            $this->graphQlClient->mutation();

            if (array_key_exists("redirect", $params) && !empty($params["redirect"])) {
                return $this->redirect($params["redirect"]);
            }

            return $this->redirectToRoute('dap_myshelf_dashboard');
        } catch (\Exception $e) {
            $this->get('dap_client.logger')->error($e->getMessage());
            throw $this->createNotFoundException("Folder couldn't be removed. Error: ".$e->getMessage());
        }
    }

    /**
     *
     * @param
     *
     * @return
     */
    public function downloadAction(Request $request, DownloadService $downloadService)
    {
        try {
            $params = $request->query->all();
            $shelfTag = array_key_exists("shelftag", $params) ? $params["shelftag"] : null;
            $fileName = $shelfTag ? : "MyShelf";

            $this->graphQlClient->addQuery(new MyItems($shelfTag));
            $data = $this->graphQlClient->send();
            $records = array_column($data['MyShelf'][0]['MyShelfRecords'], 'fullRecord');
            
            if (empty($records)) {
                throw new \UnexpectedValueException("No records found");
            }

            $csv = $downloadService->generateCsvFromRecords($records);

            $response = new Response($csv);
            $response->headers->set('Content-Type', 'text/csv');
            $response->headers->set('Content-Disposition', $response->headers->makeDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT, str_replace(array('/', '\\', '%'), "-", $fileName . '.csv'), $fileName . ".csv"));
            
            return $response;
        } catch (\Exception $e) {
            $this->get('dap_client.logger')->error($e->getMessage());
            throw $this->createNotFoundException('CSV file for items could not be generated. Error: ' . $e->getMessage());
        }
    }

    /**
     * @return string
     * @throws \Exception
     */
    public function publicFolderAction($shelfTag)
    {
        try {
            $this->graphQlClient->addQuery(new MyFolder($shelfTag));
            $data = $this->graphQlClient->send();
            $folders = $data["MyShelf"][0]["MyShelfFolders"];

            if (empty($folders)) {
                throw new \UnexpectedValueException("No folder found");
            }

            return $this->templating->renderResponse(
                'DAPMyShelfBundle:MyShelf:publicFolder.html.twig',
                [
                    'folder' => $folders[0]
                ]
            );
        } catch (\Exception $e) {
            $this->get('dap_client.logger')->error($e->getMessage());
            throw $this->createNotFoundException('Folder could not be found. Error: '.$e->getMessage());
        }
    }

    /**
     * Add default queries to GraphQL client
     */
    private function addDefaultQueries() : void
    {
        $this->graphQlClient->addQuery(new CurrentUser());
    }
}
