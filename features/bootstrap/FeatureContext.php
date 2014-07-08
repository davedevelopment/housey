<?php

use Behat\Behat\Context\ClosuredContextInterface,
    Behat\Behat\Context\TranslatedContextInterface,
    Behat\Behat\Context\BehatContext,
    Behat\Behat\Context\Step,
    Behat\Behat\Exception\PendingException;
use Behat\Gherkin\Node\PyStringNode,
    Behat\Gherkin\Node\TableNode;

require_once 'PHPUnit/Framework/Assert/Functions.php';

/**
 * Features context.
 */
class FeatureContext extends BehatContext
{
    /**
     *
     */
    protected static $db = null;

    /**
     * 
     */
    protected static $cache = null;

    /**
     *
     */
    protected static $altMapper;

    /**
     *
     */
    protected static $expMapper;

    /**
     *
     */
    protected static $service;

    /**
     *
     */
    protected static $stats;

    /**
     * Initializes context.
     * Every scenario gets it's own context object.
     *
     * @param   array   $parameters     context parameters (set them up through behat.yml)
     */
    public function __construct(array $parameters)
    {
        $db = \Doctrine\DBAL\DriverManager::getConnection(array(
            'dbname'   => $parameters['database']['params']['dbname'],
            'user'     => $parameters['database']['params']['username'],
            'password' => $parameters['database']['params']['password'],
            'host'     => $parameters['database']['params']['host'],
            'driver'   => $parameters['database']['params']['driver'],
        ));

        static::$db = $db;
        $cache = new \Housey\Cache\Serial;

        static::$cache = $cache;

        static::$altMapper = new \Housey\Mapper\AlternativeMapper(static::$db);
        static::$expMapper = new \Housey\Mapper\ExperimentMapper(static::$db);
        
        static::$service = new \Housey\Service\HouseyService(
            static::$expMapper,
            static::$altMapper,
            static::$cache
        );
    }

    /**
     * @BeforeSuite
     */
    public static function loadFixtures()
    {
        static::$db->query(file_get_contents(__DIR__.'/../../resources/schema.mysql.sql'));
    }

    /**
     * @BeforeScenario
     */
    public static function reset() 
    {
        if (method_exists(static::$cache, 'clear')) {
            static::$cache->clear();
        }

        static::$db->query("TRUNCATE housey_alternatives");
        static::$db->query("TRUNCATE housey_experiments");

        static::$stats = null;
    }

    /**
     * @Given /^the housey service has been bootstrapped$/
     */
    public function theHouseyServiceHasBeenBootstrapped()
    {
        // noop, done in constructor for now
    }

    /**
     * Convert a gherin table to an array suitable to pass to HouseyService::test
     *
     * @param TableNode $table
     * @param array
     */
    public function getAlternativesFromTable(TableNode $table)
    {
        $alts = array();
        foreach($table->getHash() as $row) {
            $weight = isset($row['Weight']) ? $row['Weight'] : 1;
            $alts[(string) $row['Content']] = $weight;
        }

        return $alts;
    }

    /**
     * @When /^I call test with "([^"]*)" and the following alternatives:$/
     */
    public function iCallTestWithAndTheFollowingAlternatives($testName, TableNode $table)
    {
        static::$service->test($testName, $this->getAlternativesFromTable($table));
    }

    /**
     * @When /^I call test with "([^"]*)", conversion name "([^"]*)" and the following alternatives:$/
     */
    public function iCallTestWithConversionNameAndTheFollowingAlternatives($testName, $conversionName, TableNode $table)
    {
        static::$service->test($testName, $this->getAlternativesFromTable($table), array(
            'conversionName' => $conversionName,
        ));
    }

    /**
     * @Given /^I call bingo with "([^"]*)"$/
     */
    public function iCallBingoWith($conversionName)
    {
        static::$service->bingo($conversionName);
    }


    /**
     * @Given /^I call get statistics$/
     */
    public function iCallGetStatistics()
    {
        static::$stats = static::$service->getStatistics();
    }

    /**
     * @Then /^I should see the following experiment statistics$/
     */
    public function iShouldSeeTheFollowingExperimentStatistics(TableNode $table)
    {
        $hash = $table->getHash();
        foreach($hash as $key => $row) {

            $exp = static::$stats[$key];
            assertEquals($row['Test name'], $exp['testName']);
            assertEquals($row['Total Participants'], $exp['totalParticipants']);
            assertEquals($row['Total Conversions'], $exp['totalConversions']);

            if (isset($row['Conversion Rate'])) {
                assertEquals($row['Conversion Rate'], $exp['conversionRate']['decimal']);
            }
        }
    }

    /**
     * @Given /^I call set identity with "([^"]*)"$/
     */
    public function iCallSetIdentityWith($id)
    {
        static::$service->setIdentity($id);
    }


}
