<?php
/**
 * File containing the SearchController class.
 *
 * (c) http://parsonstko.com/
 * (c) Developers jdiaz, johnc, natep
 */

namespace DAPClientBundle\Controller;

use DAPClientBundle\Pagination\Pagerfanta\SearchAdapter;
use DAPClientBundle\Services\SearchService;
use Pagerfanta\Adapter\ArrayAdapter;
use Pagerfanta\Pagerfanta;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Cache;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use DAPClientBundle\Services\RecordService;
use DAPClientBundle\Client\Client;

class SearchController extends Controller
{
    /**
     * Search Using GraphQL API.
     * @Cache(smaxage="3600", public=true)
     * @param $request \Symfony\Component\HttpFoundation\Request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Exception
     */
    public function gqlSearchAction(Request $request, SearchService $searchService, RecordService $recordService)
    {
        try {
            $searchSettings = $this->getParameter('dap_client.search');
            $graphqlEndpoint = $searchSettings['views']['result']['endpoint'];

            /* Validate when search term is empty */
            $searchText = $request->query->get('searchterm', '');
            if (empty($searchText)) {
                return $this->redirectToRoute("dap_client_homepage", array('msg' => 1));
            }

            try {
                $pager = new Pagerfanta(
                    new SearchAdapter(
                        $this->container->get(Client::class),
                        $request->query->all()
                    )
                );
                $pager->setMaxPerPage(10);
                $pager->setCurrentPage($request->get('page', 1));
                $result = $pager->getCurrentPageResults();
            } catch (\Exception $e) {
                $this->get('dap_client.logger')->error($e->getMessage());
                $pager = new Pagerfanta(new ArrayAdapter([]));
                $result["search"] = [];
                $result["facets"] = [];
                $result["featuredResult"] = [];
                $nbResults = 0;
            }
            
            $searchLanguagesList = $searchSettings["search_featured_languages"];
            asort($searchLanguagesList);
        
            return $this->render(
                'DAPClientBundle:Search:gqlresults.html.twig',
                array(
                    'pager' => $pager,
                    'records' => $result["search"],
                    'facets' => $searchService->configDisplayFacets($result["facets"]),
                    'featuredResult' => $result["featuredResult"],
                    // all of these search settings things could be in a service
                    // that is set from the provided parameters
                    'languagesOffered' => $searchLanguagesList,
                    'formats' => $searchSettings["formats"],
                    'genres' => $searchSettings["genres"],
                    'searchText' => htmlentities($searchText),
                )
            );
        } catch (\Exception $e) {
            $this->get('dap_client.logger')->error($e->getMessage());
            throw $this->createNotFoundException('Page could not be found. Error: ' . $e->getMessage());
        }
    }

    /**
     * Redirect detail page.
     * @Cache(smaxage="3600", public=true)
     *
     * @param $name
     * @param $dapID
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function detailAction($name, $dapID, SearchService $searchService, RecordService $recordService)
    {
        try {
            $viewSettings = $searchService->searchSettings['views']['detail'];

            if (!$searchService->validateUUID($dapID)) {
                throw $this->createNotFoundException('Invalid ID.');
            }

            if (array_key_exists('GET_option_value', $viewSettings['record'])) {
                $viewSettings['record']['GET_option_value'] =
                    str_replace('dapIDValue', $dapID, $viewSettings['record']['GET_option_value']);
            }

            $contentResult = $searchService->getContent('record', $viewSettings);

            if (empty($contentResult->data->records)) {
                throw new \UnexpectedValueException("Record not found, empty or with errors");
            }
            $record = reset($contentResult->data->records);

            if (empty($record)) {
                throw new \UnexpectedValueException("Record not found or empty");
            }

            //preprocess related images
            $relatedItemsInfo = array();

            if (isset($record->file_location)) {
                //this record has a first-class binary file attached to it

                $t = (object) array();

                //figure out file info
                $pathinfo = pathinfo($record->file_location);
                $t->type = $record->format;
                $t->url = $searchService->searchSettings['views']['detail']['binary_endpoint'] . $record->file_location;
                $t->show = $record->name;
                $t->title = $record->name;
                if (isset($record->MPSO)) {
                    $t->order = $record->MPSO;
                }
                $t->filetype = $pathinfo['extension'];
                if (isset($record->size)) {
                    $t->filesize = $record->size;
                }
                if (isset($t->url)) {
                    $t->download = $t->url;
                }
                array_push($relatedItemsInfo, $t);
            }

            //This method was used before the Universal Viewr - Validate if this can be removed.
            if (isset($record->folgerRelatedItems)) {
                foreach ($record->folgerRelatedItems as $k => $v) {
                    $t = (object) array();
                    if (isset($v->folgerObjectType) and $v->folgerObjectType == 'partofcollection') {
                        //skip this, we'll process separately for now
                    } else {
                        //switch( strtolower($v->folgerRemoteIdentification->folgerRemoteSystemID) ) {
                        if (isset($v->folgerObjectType)) {
                            switch (strtolower($v->folgerObjectType)) {
                                case 'luna':
                                case 'image': //This will need to change when we have other image sources
                                    //this is inefficient n^2 approach
                                    $foundImage = false;
                                    if (isset($record->images) && !is_null($record->images)) {
                                        foreach ($record->images as $ik => $iv) {
                                            if ($v->folgerRemoteIdentification->folgerRemoteUniqueID == $iv->rootfile) {
                                                if (isset($v->folgerObjectType)) {
                                                    $t->type = $v->folgerObjectType;
                                                } else {
                                                    $t->type = 'image';
                                                }
                                                $t->url = $viewSettings['images_endpoint'] . $iv->size4jpgURL;
                                                if ($v->description && $v->description != '') {
                                                    $t->show = $v->description;
                                                } elseif (isset($v->label)) {
                                                    $t->show = $v->label;
                                                }

                                                $t->title = $iv->pageNumber;
                                                if (isset($v->label)) {
                                                    $t->title = $v->label;
                                                }
                                                if (isset($v->mpso)) {
                                                    $t->order = $v->mpso;
                                                }
                                                $t->root = $v->folgerRemoteIdentification->folgerRemoteUniqueID;

                                                $tempImageInfo = $this->getImageInfo($t->url);

                                                $t->filetype = $tempImageInfo['type'];
                                                $t->filesize = $tempImageInfo['width'] . 'x' . $tempImageInfo['height'] . ' - ' . $tempImageInfo['size'];
                                                $t->download = '/download/image/' . $t->root . '/' . explode('/', $iv->size4jpgURL)[5];

                                                $foundImage = true;
                                            }
                                        }
                                    }
                                    if (!$foundImage) {
                                        //the image has not been imported, there is no match.
                                        //do a placeholder
                                        $t->type = 'missing_image';
                                        $t->url = null;
                                        $t->show = $v->description;
                                        $t->title = $v->label;
                                        $t->order = $v->mpso;
                                        $t->filetype = '';
                                        $t->filesize = '';
                                    }
                                    break;
                                case 'oembed':
                                    $t->type = 'oembed';
                                    if (isset($v->folgerRemoteIdentification->folgerRemoteUniqueID)) {
                                        $t->url = $v->folgerRemoteIdentification->folgerRemoteUniqueID;
                                    }
                                    if (isset($v->description)) {
                                        $t->show = $v->description;
                                    }
                                    if (isset($v->title)) {
                                        $t->title = $v->label;
                                    }
                                    if (isset($v->mpso)) {
                                        $t->order = $v->mpso;
                                    }
                                    $t->filetype = '';
                                    $t->filesize = '';
                                    if (isset($t->url)) {
                                        $t->download = $t->url;
                                    }
                                    break;
                                default:
                                    if (isset($v->folgerObjectType)) {
                                        $t->type = $v->folgerObjectType;
                                    }
                                    if (isset($v->folgerRemoteIdentification->folgerRemoteUniqueID)) {
                                        $t->url = $v->folgerRemoteIdentification->folgerRemoteUniqueID;
                                    }
                                    if (isset($v->description)) {
                                        $t->show = $v->description;
                                    }
                                    if (isset($v->label)) {
                                        $t->title = $v->label;
                                    }
                                    if (isset($v->mpso)) {
                                        $t->order = $v->mpso;
                                    }
                                    $t->filetype = '';
                                    $t->filesize = '';
                                    if (isset($t->url)) {
                                        $t->download = $t->url;
                                    }
                                    break;
                            }
                        }
                        if (isset($t)) {
                            if (isset($t->types)) {
                                array_push($relatedItemsInfo, $t);
                            }
                        }
                    }
                }
                //if we just wanted to pass through the data, but we need to process image info
                //$relatedItemsInfo = $record->folgerRelatedItems;
            }

            $relatedItemsList = \GuzzleHttp\json_encode($relatedItemsInfo);

            $collectionThing = array();

            if (isset($record->internalRelations)) {
                foreach ($record->internalRelations as $k => $v) {
                    array_push($collectionThing, $v);
                }
            }

            $collectionList = \GuzzleHttp\json_encode($collectionThing);

            //prepare open:graph metadata
            $ogMeta = array();
            $ogMeta['og:title'] = $record->title->displayTitle ?? '';
            if(isset($record->caption)){
                $ogMeta['og:description'] = $record->caption;
            } else {
                $ogMeta['og:description'] = 'The Folger Shakespeare library record for '. $record->title->displayTitle ?? '';
            }
            //$ogMeta['og:type']
            //take first related image for og:image field
            //will replace this with reference to thumbnail of this image
            /*
             if (count($relatedItemsInfo) > 0) {
                foreach ($relatedItemsInfo as $relatedItemInfo) {
                    if ($relatedItemInfo->type !== 'image') {
                        continue;
                    } else {
                        $ogMeta['og:image'] = $relatedItemInfo->url;
                        break;
                    }
                }
            }
            */
            $ogMeta['og:image'] = 'https://static.collections.folger.edu/FolgerShakespeareLibrary.png';

            if (isset($record->format)) {
                $record->format = (!$recordService->isArrayEmpty($record->format)) ? $record->format : null;
            }
            if (isset($record->language)) {
                $record->language = (!$recordService->isArrayEmpty($record->language)) ? $record->language : null;
            }
            if (isset($record->genre)) {
                $record->genre = (!$recordService->isObjectEmpty($record->genre)) ? $record->genre : null;
            }
            if (isset($record->identifiers)) {
                $record->identifiers = (!$recordService->isArrayEmpty($record->identifiers)) ? $record->identifiers : null;
            }
            if (isset($record->notes)) {
                $record->notes = (!$recordService->isArrayEmpty($record->notes)) ? $record->notes : null;
            }
            if (isset($record->subjects)) {
                $record->subjects = (!$recordService->isObjectEmpty($record->subjects)) ? $record->subjects : null;
            }
            if (isset($record->relationship)) {
                $record->relationship->agents = (!$recordService->isArrayEmpty($record->relationship->agents)) ? $record->relationship->agents : null;
                $record->relationship->works = (!$recordService->isArrayEmpty($record->relationship->works)) ? $record->relationship->works : null;
                $record->relationship->locations = (!$recordService->isArrayEmpty($record->relationship->locations)) ? $record->relationship->locations : null;
                $record->relationship->locations = (!$recordService->isArrayEmpty($record->relationship->locations)) ? $record->relationship->locations : null;
            }
            if (isset($record->fileInfo)) {
                $record->fileInfo = (!$recordService->isObjectEmpty($record->fileInfo)) ? $record->fileInfo : null;
            }
            if (isset($record->locationCreated)) {
                $record->locationCreated = (!$recordService->isObjectEmpty($record->locationCreated)) ? $record->locationCreated : null;
                ;
            }
            if (isset($record->title)) {
                $record->alsoKnowAs = $recordService->getDisplayableAlsoKnowAs(clone $record->title);
            }
            if (isset($record->groupings)) {
                $record->groupings = $recordService->getDisplayableGroupings($record->groupings);
            }


            $searchSettings = $this->getParameter('dap_client.search');
            $searchLanguagesList = $searchSettings["languages"];
            asort($searchLanguagesList);

            return $this->render(
                'DAPClientBundle:Search:detail.html.twig',
                array(
                    'viewSettings' => $viewSettings,
                    'record' => $record,
                    'relatedItemsList' => $relatedItemsList,
                    'collectionList' => $collectionList,
                    'detailMeta' => $ogMeta,
                    'languagesOffered' => $searchLanguagesList,
                )
            );
        } catch (\Exception $e) {
            throw $this->createNotFoundException('Page could not be found. Error: ' . $e->getMessage());
        }
    }

    /*
     * Convenience function borrowed from twig extension
     * get info about image file
     *
     */
    public function getImageInfo($path)
    {
        try {
            $types = array(
                1 => 'GIF',
                2 => 'JPG',
                3 => 'PNG',
                4 => 'SWF',
                5 => 'PSD',
                6 => 'BMP',
                7 => 'TIFF(intel byte order)',
                8 => 'TIFF(motorola byte order)',
                9 => 'JPC',
                10 => 'JP2',
                11 => 'JPX',
                12 => 'JB2',
                13 => 'SWC',
                14 => 'IFF',
                15 => 'WBMP',
                16 => 'XBM',
            );

            $image = get_headers($path, 1);

            $imageKb = $image["Content-Length"] / 1024;

            list($width, $height, $type) = getimagesize($path);

            return array(
                'width' => $width,
                'height' => $height,
                'type' => $types[$type],
                'size' => number_format($imageKb, 0) . "kb",
            );
        } catch (\Exception $e) {
            return array(
                'width' => null,
                'height' => null,
                'type' => null,
                'size' => null,
            );
        }
    }
}
