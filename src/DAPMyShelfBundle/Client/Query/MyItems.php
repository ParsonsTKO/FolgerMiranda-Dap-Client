<?php declare(strict_types=1);

namespace DAPMyShelfBundle\Client\Query;

use DAPClientBundle\Client\Query\QueryInterface;

class MyItems implements QueryInterface
{
    /**
     * @var UuidInterface
     */
    private $myShelfFolder;

    /**
     * @param UuidInterface $myShelfFolder
     */
    public function __construct(string $myShelfFolder = null)
    {
        $this->myShelfFolder = $myShelfFolder;
    }

    /**
     * {@inheritdoc}
     */
    public function toGQL() : string
    {
        return sprintf(
            'MyShelf%s {
                MyShelfRecords {
                    dapID
                    fullRecord {
                        dapID
                        language
                        title {
                            displayTitle
                        }
                        identifiers {
                            key
                            value
                        }
                        holdingInstitution {
                            name
                            exhibitionCode
                        }
                        creator
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
                        preferredCitation
                        simplifiedTranscription
                    }
                }
            }',
            null !== $this->myShelfFolder
                ? sprintf(' (myShelfFolder:"%s")', $this->myShelfFolder)
                : ''
        );
    }
}
