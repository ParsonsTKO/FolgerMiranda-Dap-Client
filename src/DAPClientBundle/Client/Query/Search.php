<?php declare(strict_types=1);

namespace DAPClientBundle\Client\Query;

class Search implements QueryInterface
{
    /**
     * @var array
     */
    private $queryArguments = [];

    /**
     * @param array $filters
     */
    public function __construct(array $filters = [])
    {
        $this->convertFiltersToQueryArguments($filters);
    }

    /**
     * {@inheritdoc}
     */
    public function toGQL() : string
    {
        $arguments = [];

        foreach ($this->queryArguments as $name => $value) {
            $arguments[] = sprintf(
                '%s: "%s"',
                $name,
                str_replace('"', "'", $value)
            );
        }

        $argumentsString = '';

        if (!empty($arguments)) {
            $argumentsString = sprintf(
                ' (%s)',
                implode("\n", $arguments)
            );
        }

        return sprintf(
            'search%s {
                dapID
                format
                language
                title {
                    displayTitle
                }
                genre {
                    name
                    uri
                }
                dateCreated {
                    displayDate
                    isoDate
                }
                locationCreated {
                    addressCountry
                    addressLocality
                    addressRegion
                    locationDescriptor
                }
                folgerDisplayIdentifier
                creator
                relationships {
                    agents {
                        agentURI
                        agentName
                        relationship
                    }
                }
                availableOnline
                hasRelatedImages
                isImage
                hasImages
                caption
            }',
            $argumentsString
        );
    }

    /**
     * @param array $filters
     */
    private function convertFiltersToQueryArguments(array $filters) : void
    {
        /**
         *
         * ?? = Unsure
         * >> = Implemented
         * !! = Not Implemented
         *
         * ?? dapID: (what request property)
         * >> searchText:
         * >> language:
         * >> format:
         * ?? mirandaGenre: (what request property)
         * >> genre:
         * ?? dateCreated: (what request property)
         * ?? refine: (is this just sent directly to the server as the range, etc fields aren't in the schama)
         * ?? refineto: (is this just sent directly to the server as the range, etc fields aren't in the schama)
         * >> offset:
         * >> pagesize:
         * >> createdFrom:
         * >> createdUntil:
         * >> availableOnline:
         * ?? arrayFacets: (what request property)
         * >> facets:
         */
        
        if (!empty($filters['searchterm'])) {
            $this->queryArguments['searchText'] = $filters['searchterm'];
        }

        if (!empty($filters['languagefilter'])) {
            $this->queryArguments['language'] = $filters['languagefilter'];
        }
        
        if (!empty($filters['format'])) {
            $this->queryArguments['format'] = $filters['format'];
        }
        
        if (!empty($filters['genre'])) {
            $this->queryArguments['genre'] = $filters['genre'];
        }
        
        if (!empty($filters['createdfrom'])) {
            $this->queryArguments['createdFrom'] = $filters['createdfrom'];
        }
        
        if (!empty($filters['createduntil'])) {
            $this->queryArguments['createdUntil'] = $filters['createduntil'];
        }
        
        $pagesize = !empty($filters['pageSize']) ? (int) $filters['pageSize'] : 10;
        $this->queryArguments['pagesize'] = $pagesize;
        
        $page = !empty($filters['page']) ? (int) $filters['page'] : 1;
        $this->queryArguments['offset'] = ($page - 1) * $pagesize;

        if (!empty($filters['facets'])) {
            $this->queryArguments['facets'] = str_replace(
                '"',
                "'",
                json_encode($filters['facets'])
            );
            $this->queryArguments['facets'] = json_encode($filters['facets']);
        }

        if ('on' === ($filters['availableonline'] ?? false)) {
            $this->queryArguments['availableOnline'] = 'folger_related_itemsORfile_info';
        }
    }
}
