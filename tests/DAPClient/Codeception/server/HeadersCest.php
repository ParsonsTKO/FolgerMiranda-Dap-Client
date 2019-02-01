<?php
namespace DAPClient\Codeception;

use DAPClient\Codeception\Visitor;

class HeadersCest
{
    public function _before(Visitor $I)
    {
    }

    public function _after(Visitor $I)
    {
    }

    public function checkHomePageHeaders(Visitor $I)
    {
        $I->wantTo('Check home page headers (Cache-Control, Content-language, Content-Type) are present and valid');
        $I->amOnPage('/');
        $I->seeHttpHeader('Cache-Control', 'public, s-maxage=3600');
        $I->seeHttpHeader('Content-Type', 'text/html; charset=UTF-8');
    }

    public function checkDetailPageHeaders(Visitor $I)
    {
        $I->wantTo('Check detail page headers (Cache-Control, Content-language, Content-Type) are present and valid');
        $I->amOnPage("/detail/antient-british-portraits-/12e87fc9-e5a3-4a43-a482-5e19cf90506a");
        $I->seeHttpHeader('Cache-Control', 'public, s-maxage=3600');
        $I->seeHttpHeader('Content-Type', 'text/html; charset=UTF-8');
    }

    public function checkMiradorPageHeaders(Visitor $I)
    {
        $I->wantTo('Check Mirador page headers (Cache-Control, Content-language, Content-Type) are present and valid');
        $I->amOnPage("/mirador/56c2a4f4-aed6-473c-8e1d-46c896517e44");
        $I->seeHttpHeader('Cache-Control', 'max-age=3600, public, s-maxage=3600');
        $I->seeHttpHeader('Content-Type', 'text/html; charset=UTF-8');
    }

    public function checkSearchPageHeaders(Visitor $I)
    {
        $I->wantTo('Check Search page headers (Cache-Control, Content-language, Content-Type) are present and valid');
        $I->amOnPage("/search?searchterm=*");
        $I->seeHttpHeader('Cache-Control', 'public, s-maxage=3600');
        $I->seeHttpHeader('Content-Type', 'text/html; charset=UTF-8');
    }

    public function checkDownloadCsvRecordHeaders(Visitor $I)
    {
        $I->wantTo('Check download CSV record headers (Cache-Control, Content-language, Content-Type) are present and valid');
        $I->amOnPage("/download/record/56c2a4f4-aed6-473c-8e1d-46c896517e44/csv");
        $I->seeHttpHeader('Cache-Control', 'max-age=86400, public, s-maxage=86400');
        $I->seeHttpHeader('Content-Type', 'text/html; charset=UTF-8');
    }

    public function checkSecurityHeaders(Visitor $I)
    {
        $I->wantTo('Check security headers (X-Frame-Options, X-Content-Type-Options, X-XSS-Protection) are present and valid in home');
        $I->amOnPage('/');
        $I->seeHttpHeader('X-Frame-Options', 'SAMEORIGIN');
        $I->seeHttpHeader('X-Content-Type-Options', 'nosniff');
        $I->seeHttpHeader('X-XSS-Protection', '1; mode=block');
        $I->seeHttpHeader('Strict-Transport-Security', "max-age=31536000; includeSubDomains");
    }
}
