<?php
namespace DAPClient\Codeception\Helper;

// here you can define custom actions
// all public methods declared in helper class will be available in $I

class Redirects extends \Codeception\Module
{

    /**
     * Ensure that a particular URL redirects to another URL
     *
     * @param integer $redirectCode (301 = permanent, 302 = temporary)
     */
    public function verifySchemaRedirect($schema = "https", $redirectCode = 301) {
        $guzzle = $this->getModule('PhpBrowser')->client;
        $response = $guzzle->getInternalResponse();
        $responseCode   = $response->getStatus();
		    $this->assertEquals($responseCode, $redirectCode);
        $request = $guzzle->getHistory()->current();
        $currentUrl = $request->getUri();
        $locationHeader = $response->getHeader('Location');
        $hostHeader = $request->getServer()["HTTP_HOST"];
        $currentUrlParts = parse_url($currentUrl);
        $locationHeaderParts = parse_url($locationHeader);
        $currentUrlParts["scheme"] = $schema;
        $currentUrlParts["host"] = $hostHeader;
        $this->assertEquals($currentUrlParts, $locationHeaderParts);
    }

    /**
     * Ensure that a particular URL redirects to another URL
     *
     * @param string $startUrl
     * @param string $endUrl (should match "Location" header exactly)
     * @param integer $redirectCode (301 = permanent, 302 = temporary)
     */
    public function verifyFqdnRedirect($startUrl, $endUrl, $redirectCode = 301)
    {
        $phpBrowser = $this->getModule('PhpBrowser');
        $guzzle = $phpBrowser->client;

        // Disable the following of redirects
        $guzzle->followRedirects(false);


		$testDomain = \Codeception\Configuration::suiteSettings('redirects', \Codeception\Configuration::config())['modules']['enabled'][1]['REST']['url'];

        $phpBrowser->_loadPage('GET', $startUrl);
        $response = $guzzle->getInternalResponse();
        $responseCode   = $response->getStatus();
        $locationHeader = $response->getHeader('Location');

        $this->assertEquals($responseCode, $redirectCode);
        $this->assertEquals($endUrl, $locationHeader);

        $guzzle->followRedirects(true);
    }

	/**
	 * Toggle redirections on and off.
	 *
	 * By default, BrowserKit will follow redirections, so to check for 30*
	 * HTTP status codes and Location headers, they have to be turned off.
	 *
	 * @since 1.0.0
	 *
	 * @param bool $followRedirects Optional. Whether to follow redirects or not.
	 *                              Default is true.
	 */
	function followRedirects( $followRedirects = true ) {
		$this->getModule('PhpBrowser')->client->followRedirects($followRedirects);
	}


}
