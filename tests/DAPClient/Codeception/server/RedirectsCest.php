<?php
namespace DAPClient\Codeception;

use DAPClient\Codeception\Visitor;

class RedirectsCest
{
    public function _before(Visitor $I)
    {
    }

    public function _after(Visitor $I)
    {
    }

    // tests
    public function checkredirectfromcollections(Visitor $I)
    {
        $I->wantTo('Check collection.folger.edu and miranda.folger.edu redirect to https://collections.folger.edu');
        $I->followRedirects(false);
        
        /* Disabled becasue a bug in Header Host https://github.com/Codeception/Codeception/issues/1650#issuecomment-444903648

        $I->setHeader('Host', 'collections.folger.edu');
        $I->amOnPage('/');
        $I->seeResponseCodeIs(200);
        $I->dontSeeHttpHeader('Location');
        $I->dontSeeInTitle('301 Moved Permanently');

        $I->setHeader('Host', 'staging.miranda.folger.edu');
        $I->amOnPage('/');
        $I->seeResponseCodeIs(200);
        $I->dontSeeHttpHeader('Location');
        $I->dontSeeInTitle('301 Moved Permanently');

        $I->setHeader('Host', 'staging.collections.folger.edu');
        $I->amOnPage('/');
        $I->seeResponseCodeIs(200);
        $I->dontSeeHttpHeader('Location');
        $I->dontSeeInTitle('301 Moved Permanently');

        $I->setHeader('Host', 'collection.folger.edu');
        $I->amOnPage('/');
        $I->seeResponseCodeIs(301);
        $I->seeHttpHeader('Location', 'https://collections.folger.edu/');
        $I->seeInTitle('301 Moved Permanently');

        $I->setHeader('Host', 'miranda.folger.edu');
        $I->amOnPage('/');
        $I->seeResponseCodeIs(301);
        $I->seeHttpHeader('Location', 'https://collections.folger.edu/');
        $I->seeInTitle('301 Moved Permanently');
        */
    }
}
