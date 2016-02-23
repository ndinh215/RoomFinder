<?php
require __DIR__.'/../../vendor/autoload.php';

use Behat\Behat\Context\Context;
use Behat\Behat\Context\SnippetAcceptingContext;
use GuzzleHttp\Client;

/**
 * Finder context
 */
class FeatureContext extends \PHPUnit_Framework_TestCase implements Context, SnippetAcceptingContext
{
    const URL_PATTERN = 'http://127.0.0.1:8000/api/offers/%s';

    protected $client = null;
    protected $date = null;
    protected $request = null;
    protected $response = null;

    public function __construct()
    {
        $this->client = new Client();
    }

    /**
     * @Given /^I search for room names available at "([0-9]{4}\-[0-9]{2}\-[0-9]{2})"$/
     */
    public function iSearchForRoomNamesAvailableAt($date)
    {
        $this->date = $date;
    }

    /**
     * @When /^I send a "([A-Z]+)" request$/
     */
    public function iSendARequest($method)
    {
        $url = sprintf(self::URL_PATTERN, $this->date);
        $this->response = $this->client->request($method, $url);
        \PHPUnit_Framework_TestCase::assertEquals(200, $this->response->getStatusCode());
    }

    /**
     * @Then /^I should see "([^"]*)"$/
     */
    public function iShouldSee($expectedResult)
    {
        $content = $this->response->getBody()->getContents();
        \PHPUnit_Framework_TestCase::assertContains($expectedResult, $content);
    }
}
