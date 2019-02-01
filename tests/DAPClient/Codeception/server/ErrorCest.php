<?php
namespace DAPClient\Codeception;
use DAPClient\Codeception\Visitor;

class ErrorCest
{
    public function checkErrorPages(Visitor $I)
    {
        $I->wantTo('Check the error pages for some URLs');
        $I->followRedirects(false);
        $I->amOnPage('/page_not_fount_404');
        $I->seeResponseCodeIs(404);
        $I->dontSee('nginx');
        $I->amOnPage('/index.php');
        $I->seeResponseCodeIs(404);
        $I->dontSee('nginx');
        $I->amOnPage('/any/other/php/path.php');
        $I->seeResponseCodeIs(404);
        $I->dontSee('nginx');
        $I->amOnPage('/any/image/path.jpg');
        $I->seeResponseCodeIs(404);
        $I->dontSee('nginx');
        $I->amOnPage('/any/other/path.path');
        $I->seeResponseCodeIs(404);
        $I->dontSee('nginx');
        /* This should pass, needs configuration in NGinx
        $I->amOnPage('/app.php');
        $I->seeResponseCodeIs(404);
        $I->dontSee('nginx');
        $I->amOnPage('/app.php/search?searchterm=*');
        $I->seeResponseCodeIs(404);
        $I->dontSee('nginx');
        */
    }
}
