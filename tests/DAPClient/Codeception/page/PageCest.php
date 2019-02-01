<?php
namespace DAPClient\Codeception;

use DAPClient\Codeception\Visitor;

class PageCest
{
    public function checkMetaTagsInHomePage(Visitor $I)
    {
        $I->wantTo('Check the meta tags in the home page');
        $I->amOnPage("/");
        $I->seeResponseCodeIs(200);
        $I->dontSeeElement('meta[name=robots]');
        $I->seeElement('meta[name=viewport]');
        $I->seeElement('meta[name=description]');
        $I->seeElement('meta[name=keywords]');
        $I->seeElement('meta[name=author]');
        $I->seeElement('meta[property="og:url"]');
        $I->seeElement('meta[property="og:title"]');
    }

    public function checkMetaTagsInMiradorPage(Visitor $I)
    {
        $I->wantTo('Check the meta tags in the mirador page');
        $I->amOnPage("/mirador/bc3728bc-3ef0-4a29-be54-0736cda1aa0b");
        $I->seeResponseCodeIs(200);
        $I->dontSeeElement('meta[name=robots]');
        $I->seeElement('meta[name=viewport]');
        $I->seeElement('meta[name=description]');
        $I->seeElement('meta[name=keywords]');
        $I->seeElement('meta[name=author]');
        $I->seeElement('meta[property="og:url"]');
        $I->seeElement('meta[property="og:title"]');
    }
    
    public function checkMetaTagsInDetailPage(Visitor $I)
    {
        $I->wantTo('Check the meta tags in the detail page');
        $I->amOnPage("/detail/oeuvres-choisies-de-shakespeare/bc3728bc-3ef0-4a29-be54-0736cda1aa0b");
        $I->seeResponseCodeIs(200);
        $I->dontSeeElement('meta[name=robots]');
        $I->seeElement('meta[name=viewport]');
        $I->seeElement('meta[name=description]');
        $I->seeElement('meta[name=keywords]');
        $I->seeElement('meta[name=author]');
        $I->seeElement('meta[property="og:url"]');
        $I->seeElement('meta[property="og:title"]');
    }

    public function checkMetaTagsInSearchPage(Visitor $I)
    {
        $I->wantTo('Check the meta tags in the search page');
        $I->amOnPage("/search?searchterm=*");
        $I->seeResponseCodeIs(200);
        $I->dontSeeElement('meta[name=robots]');
        $I->seeElement('meta[name=viewport]');
        $I->seeElement('meta[name=description]');
        $I->seeElement('meta[name=keywords]');
        $I->seeElement('meta[name=author]');
        $I->seeElement('meta[property="og:url"]');
        $I->seeElement('meta[property="og:title"]');
    }
}
