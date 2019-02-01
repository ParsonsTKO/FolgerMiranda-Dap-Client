<?php declare(strict_types=1);

namespace DAPClientBundle\Client\Query;

class FeaturedResult implements QueryInterface
{
    /**
     * @var string
     */
    private $searchText;

    /**
     * @param string $searchText
     */
    public function __construct(string $searchText)
    {
        // Dirty approach to escaping "'s in search text
        $searchText = str_replace('"', '\"', $searchText);

        $this->searchText = $searchText;
    }

    /**
     * {@inheritdoc}
     */
    public function toGQL() : string
    {
        return sprintf(
            'featuredResult(searchText: "%s") {
                title
                thumbnail
                teaser
                link
            }',
            $this->searchText
        );
    }
}