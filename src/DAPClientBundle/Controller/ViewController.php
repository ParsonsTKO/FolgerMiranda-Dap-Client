<?php
/**
 * File containing the ViewController class.
 *
 * (c) http://parsonstko.com/
 * (c) Developer jdiaz
 */

namespace DAPClientBundle\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Cache;
use DAPClientBundle\Services\SearchService;
use DAPClientBundle\Services\RecordService;
use DAPClientBundle\Services\DownloadService;

class ViewController extends Controller
{
    private $recordService;
    private $downloadService;
 
    /**
     * Redirect homepage.
     * @Cache(smaxage="3600", public=true)
     * @param $request
     *
     * @return
     */
    public function homeAction(Request $request)
    {
        try {
            $metadata = $this->getParameter('dap_client.head')['metadata'];
            $searchSettings = $this->getParameter('dap_client.search');
            $searchLanguagesList = $searchSettings["search_featured_languages"];
            asort($searchLanguagesList);

            $msg = $request->query->get('msg');
            $userMessage = '';
            switch ($msg) {
                case 1:
                    $userMessage = 'We were not able to work out what you were hoping to search for.<br>If you would like, enter an asterisk (*) for a wildcard search.';
                    break;
            }
            
            return $this->render(
                'DAPClientBundle::home.html.twig',
                array(
                    'metadata' => $metadata,
                    'usermessage' => $userMessage,
                    'languagesOffered' => $searchLanguagesList,
                    'formats' => $searchSettings["formats"],
                    'genres' => $searchSettings["genres"],
                )
            );
        } catch (\Exception $e) {
            $this->get('dap_client.logger')->error($e->getMessage());
            throw $this->createNotFoundException('Page could not be found. Error: '.$e->getMessage());
        }
    }

    /**
     * Dowload images.
     *
     * @param
     *
     * @return
     */
    public function downloadImageAction($rootfile, $image, SearchService $searchService)
    {
        try {
            $viewSettings = $searchService->searchSettings['views']['detail'];
            $imagesEndPoint = $viewSettings['images_endpoint'];
            $imagesPath = $viewSettings['images_path'];
            $url = $imagesEndPoint . $imagesPath . $rootfile . '/' . $image;
            $headers = get_headers($url, 1);
            $contentType = $headers['Content-Type'];
            $contentLength = $headers['Content-Length'];

            if (stripos($headers[0], "200 OK")) {
                header("Pragma: public");
                header("Expires: 0");
                header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
                header("Cache-Control: private", false);
                header("Content-Type: $contentType");
                header("Content-Disposition: attachment; filename=\"".basename($url)."\";");
                header("Content-Transfer-Encoding: binary");
                header("Content-Length: ".$contentLength);
                ob_clean();
                flush();
                readfile($url);
            } else {
                throw new \UnexpectedValueException("Image not found or empty");
            }
        } catch (\Exception $e) {
            $this->get('dap_client.logger')->error($e->getMessage());
            throw $this->createNotFoundException('Page could not be found. Error: '.$e->getMessage());
        }
    }

    /**
     * Dowload arbitrary binary files.
     *
     * @param
     *
     * @return
     */
    public function downloadBinaryAction($binaryFile, SearchService $searchService)
    {
        try {
            $viewSettings = $searchService->searchSettings['views']['detail'];
            $binaryEndPoint = $viewSettings['binary_endpoint'];
            $binaryPath = $viewSettings['binary_path'];
            $url = $binaryEndPoint . $binaryPath . '/' . $binaryFile;
            $headers = get_headers($url, 1);
            $contentType = $headers['Content-Type'];
            $contentLength = $headers['Content-Length'];

            if (stripos($headers[0], "200 OK")) {
                header("Pragma: public");
                header("Expires: 0");
                header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
                header("Cache-Control: private", false);
                header("Content-Type: $contentType");
                header("Content-Disposition: attachment; filename=\"".basename($url)."\";");
                header("Content-Transfer-Encoding: binary");
                header("Content-Length: ".$contentLength);
                ob_clean();
                flush();
                readfile($url);
            } else {
                throw new \UnexpectedValueException("Image not found or empty");
            }
        } catch (\Exception $e) {
            $this->get('dap_client.logger')->error($e->getMessage());
            throw $this->createNotFoundException('Page could not be found. Error: '.$e->getMessage());
        }
    }
    
    /**
     *
     * @Cache(smaxage="86400", maxage="86400", public=true)
     * @param
     *
     * @return
     */
    public function downloadCsvAction($dapID, SearchService $searchService, DownloadService $downloadService)
    {
        try {
            $record = $searchService->getRecordById($dapID);
            $csv = $downloadService->generateCsvFromRecords(array($record));
            $filename = $downloadService->getCsvFilenameFromRecord($record);
            
            $response = new Response($csv);
            $response->headers->set('Content-Type', 'text/csv');
            $response->headers->set('Content-Disposition', $response->headers->makeDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT, str_replace(array('/', '\\', '%'), "-", $filename .'.csv'), $dapID . ".csv"));
            
            return $response;
        } catch (\Exception $e) {
            $this->get('dap_client.logger')->error($e->getMessage());
            throw $this->createNotFoundException("Couldn't generate CSV from record. Error: " . $e->getMessage());
        }
    }

    public function recordSearchItemAction($record, RecordService $recordService)
    {
        $record = (object) $record;
        
        try {
            $record->icon = $recordService->getRecordIcon($record->format[0]);
            return $this->render(
                'DAPClientBundle::Search/record_item.html.twig',
                array(
                    'record' => $record,
                )
            );
        } catch (\Exception $e) {
            $this->get('dap_client.logger')->error($e->getMessage());
            return new Response("Cloudn't display record");
        }
    }
}
