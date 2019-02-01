<?php declare(strict_types=1);

namespace DAPClientBundle\Client\Query;

class Record implements QueryInterface
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
            'records%s {
                dapID
                language
                identifiers {
                    key
                    value
                }
                holdingInstitution {
                    name
                    exhibitionCode
                }
                abstract
                recordType
                creator
                title {
                    displayTitle
                    uniformTitle {
                        titleString
                    }
                    extendedTitle
                    alternateTitles {
                        titleText
                    }
                }
                dateCreated {
                    displayDate
                    isoDate
                }
                extent
                notes {
                    label
                    note
                }
                folgerDisplayIdentifier
                folgerDimensions
                folgerProvenance
                folgerRelatedItems {
                    remoteUniqueID {
                        remoteID
                        remoteSystem
                    }
                }
                isBynaryFile
                binaryFileUrl {
                    url
                    type
                    remoteUrl
                    oembed
                }
                isRemoteSystem
                remoteSystemUrl {
                    oembed
                    url
                }
                isImage
                hasImages
                availableOnline
                hasRelatedImages
                format
                genre {
                    name
                    uri
                }
                license
                locationCreated {
                    addressLocality
                    addressCountry
                    addressRegion
                    locationDescriptor
                }
                mirandaGenre
                size
                relationships {
                    agents {
                        agentURI
                        agentName
                        relationship
                  }
                  works {
                        relationship
                        workInstance
                  }
                  locations {
                        relationship
                        locationDescriptor
                        addressLocality
                        addressRegion
                        addressCountry
                  }
                }
                preferredCitation
                simplifiedTranscription
                fileInfo {
                    encodingFormat
                    width
                    height
                    duration
                    numberOfRows
                    contentSize
                }
              }',
            $argumentsString
        );
    }

    /**
     * @param array $filters
     */
    private function convertFiltersToQueryArguments(array $filters) : void
    {
        if (!empty($filters['dapId'])) {
            $this->queryArguments['dapID'] = $filters['dapId'];
        }
    }
}
