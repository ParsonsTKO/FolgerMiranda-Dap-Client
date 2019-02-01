<?php declare(strict_types=1);

namespace DAPClientBundle\Client\Query;

class Pagination implements QueryInterface
{
    /**
     * @var int
     */
    private $pageNumber;

    /**
     * @param int $pageNumber
     */
    public function __construct(int $pageNumber)
    {
        $this->pageNumber = $pageNumber;
    }

    /**
     * {@inheritdoc}
     */
    public function toGQL() : string
    {
        return sprintf(
            'pagination(offset: "%d") {
                index
                count
                total
            }',
            $this->pageNumber
        );
    }
}