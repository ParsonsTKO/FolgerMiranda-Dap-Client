<?php declare(strict_types=1);

namespace DAPMyShelfBundle\Client\Query;

use Ramsey\Uuid\UuidInterface;
use DAPClientBundle\Client\Query\QueryInterface;

class MyShelf implements QueryInterface
{
    /**
     * @var UuidInterface
     */
    private $myShelfFolder;

    /**
     * @param UuidInterface $myShelfFolder
     */
    public function __construct(UuidInterface $myShelfFolder = null)
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
                ownerName
                MyShelfRecords {
                    dapID
                    owner
                    folder
                    notes
                    sortOrder
                    dateAdded
                    lastUpdated
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
                MyShelfFolders {
                    MyShelfFolderName
                    MyShelfFolderTag
                    isPublic
                    notes
                    sortOrder
                    dateAdded
                    lastUpdated
                    owner
                    record {
                        dapID
                        owner
                        folder
                        notes
                        sortOrder
                        dateAdded
                        lastUpdated
                        fullRecord {
                            dapID
                            title {
                                displayTitle
                            }
                        }
                    }
                }
            }',
            null !== $this->myShelfFolder
                ? sprintf(' (myShelfFolder="%s")', $this->myShelfFolder->toString())
                : ''
        );
    }
}
