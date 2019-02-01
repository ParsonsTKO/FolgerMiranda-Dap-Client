<?php declare(strict_types=1);

namespace DAPMyShelfBundle\Client\Query;

use Ramsey\Uuid\UuidInterface;
use DAPClientBundle\Client\Query\QueryInterface;

class EmptyAll implements QueryInterface
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
            'EmptyShelf%s {
                success
            }',
            null !== $this->myShelfFolder
                ? sprintf(' (shelfTag:"%s")', $this->myShelfFolder)
                : ''
        );
    }
}
