<?php declare(strict_types=1);

namespace DAPMyShelfBundle\Client\Query;

use Ramsey\Uuid\UuidInterface;
use DAPClientBundle\Client\Query\QueryInterface;

class AllFolders implements QueryInterface
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
                MyShelfFolders {
                    MyShelfFolderName
                    MyShelfFolderTag
                }
            }',
            null !== $this->myShelfFolder
                ? sprintf(' (myShelfFolder="%s")', $this->myShelfFolder->toString())
                : ''
        );
    }
}
