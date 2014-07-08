<?php

namespace Housey\Service;

use Housey\Entity\Experiment;
use Housey\Entity\Alternative;
use Housey\Mapper\ExperimentMapperInterface;
use Housey\Mapper\AlternativeMapperInterface;
use Housey\Cache\CacheInterface;

/**
 * One class to rule them all... Needs refactoring into a couple more service
 * classes. 
 *
 * @author      Dave Marshall <david.marshall@atstsolutions.co.uk>
 */
class HouseyService
{
    /**
     * @var ExperimentMapperInterface
     */
    protected $experimentMapper;

    /**
     * @var AlternativeMapperInterface
     */
    protected $alternativeMapperInterface;

    /**
     * @var CacheInterface
     */
    protected $cache;

    /**
     * @var string
     */
    protected $identity;

    /**
     * @var array
     */
    public $options = array(
        'salt' => 'Not really necessary',
        'humansOnly' => true,
    );

    /**
     * Constructor
     *
     * @param ExperimentMapperInterface $experimentMapper
     * @param AlternativeMapperInterface $alternativeMapper
     * @param CacheInterface $cache
     * @param array $options
     */
    public function __construct(ExperimentMapperInterface $experimentMapper, AlternativeMapperInterface $alternativeMapper, CacheInterface $cache, array $options = null)
    {
        $this->experimentMapper = $experimentMapper;
        $this->alternativeMapper = $alternativeMapper;
        $this->cache = $cache;

        if (!empty($options)) {
            $this->setOptions($options);
        }
    }

    /**
     * Test
     *
     * This is the main method and is used to create and execute tests
     *
     * Options currently only accepts conversionName 
     *
     * @param string $testName
     * @param array $alternatives
     * @param array $options
     * @return mixed
     */
    public function test($testName, array $alternatives, array $options = null)
    {
        $defaults = array('conversionName' => $testName);
        $options = array_merge($defaults, (array) $options);

        $shortCircuit = $this->cache->get("Housey::Experiment::ShortCircuit::" . $testName);
        if (null !== $shortCircuit) {
            return $shortCircuit;
        }

        $conversionNames = (array) $options['conversionName'];

        foreach ($conversionNames as $conversionName) {
            $experimentName = $testName.':'.$conversionName;
            $exists = $this->cache->get("Housey::Experiment::Exists::$experimentName");
            if (!$exists) {
                $experiment = $this->experimentMapper->find($experimentName);
                if (null === $experiment) {
                    $lockKey = "Housey::LockForCreation::$experimentName";
                    while(false === $this->cache->add($lockKey, true, 5)) {
                        sleep(0.1);
                    }

                    // lock achieved, check it still doesn't exist
                    $experiment = $this->experimentMapper->find($experimentName);
                    if (null === $experiment) {
                        $experimentOptions = $options;
                        $experimentOptions['conversionName'] = $conversionName;
                        $experiment = $this->startExperiment($experimentName, $this->parseAlternatives($alternatives), $experimentOptions);
                    }

                    $this->cache->delete($lockKey);
                }
            }
        }

        $choice = $this->findAlternativeForUser($testName, $alternatives);
        $participationKey = "Housey::ParticpatingTests::" . $this->getIdentity();
        $participating = $this->cache->get($participationKey);

        if (!is_array($participating)) {
            $participating = array();
        }

        foreach ($conversionNames as $conversionName) {
            $experimentName = $testName.':'.$conversionName;
            if (!in_array($experimentName, $participating)) {
                $participating[] = $experimentName;
                $this->scoreParticipation($testName, $experimentName);
            }
        }
        $this->cache->set($participationKey, $participating);

        /**
         * This next block is copied from the start experiment method, it's a
         * very volatile piece of caching. Most of the cached values allow the
         * test to continue if they're lost (maybe some people get double
         * counted), but this pretty much ends the test as no conversions are
         * recorded...
         */
        foreach ($conversionNames as $conversionName) {
            $experimentName = $testName.':'.$conversionName;
            $listeners = $this->cache->get("Housey::TestsListeningToConversion::$conversionName");

            if (!is_array($listeners)) {
                $listeners = array();
            }

            if (!in_array($experimentName, $listeners)) {
                $listeners[] = $experimentName;
                $listeners = $this->cache->set("Housey::TestsListeningToConversion::$conversionName", $listeners);
            }
        }

        return $choice;
    }

    /**
     * Bingo!
     *
     * Record any conversions 
     *
     * @param array|string $conversionName - Could be a testName or conversionName
     * @param array $options - not currently used
     * @return void
     */
    public function bingo($conversionName, array $options = null)
    {
        if (is_array($conversionName)) {
            foreach($conversionName as $name) {
                $this->bingo($name, $options);
            }
            return;
        }
       
        /**
         * Check for conversion name
         */
        $listeners = $this->cache->get("Housey::TestsListeningToConversion::$conversionName");
        if (is_array($listeners)) {
            foreach($listeners as $name) {
                $this->scoreConversion($conversionName, $name);
            }
            return;
        }

        return $this->scoreConversion($conversionName, $conversionName.':'.$conversionName);
    }

    /**
     * Get Statistics
     *
     * @return array
     */
    public function getStatistics()
    {
        $experiments = $this->experimentMapper->getAll();
        $stats = array();
        foreach($experiments as $experiment) {
            $expData = array(
                'testName' => $experiment->testName,
                'status' => $experiment->status,
                'alternatives' => array(),
                'totalParticipants' => 0,
                'totalConversions' => 0,
                'conversionRate' => array(
                    'decimal' => 'N/A',
                    'pretty' => 'N/A',
                ),
                'winner' => null,
                'significance' => array(
                    'zscore' => 'N/A',
                    'pretty'  => 'N/A',
                ),
            );

            $alternatives = $this->alternativeMapper->getByExperimentTestName($experiment->testName);
            $best = 0; 
            foreach($alternatives as $alternative) {
                $altData = array(
                    'id' => $alternative->id,
                    'content' => $alternative->content,
                    'weight'  => $alternative->weight,
                    'participants' => $alternative->participants,
                    'conversions' => $alternative->conversions,
                    'conversionRate' => array(
                        'decimal' => 'N/A',
                        'pretty' => 'N/A',
                    ),
                );

                if ($alternative->participants > 0) {
                    $altData['conversionRate']['decimal'] = (1.0 * $alternative->conversions)/$alternative->participants;
                    $altData['conversionRate']['pretty'] = sprintf("%4.2f%%", $altData['conversionRate']['decimal'] * 100);

                    if ($altData['conversionRate']['decimal'] > $best) {
                        $expData['winner'] = $alternative->id;
                        $best = $altData['conversionRate']['decimal'];
                    }
                }

                $expData['alternatives'][] = $altData;
                $expData['totalConversions'] += $alternative->conversions;
                $expData['totalParticipants'] += $alternative->participants;
            }

            /**
             */
            if ($expData['totalParticipants'] > 0) {
                $expData['conversionRate']['decimal'] = (1.0 * $expData['totalConversions'])/$expData['totalParticipants'];
                $expData['conversionRate']['pretty'] = sprintf("%4.2f%%", $expData['conversionRate']['decimal'] * 100);
            }

            /**
             * @todo abstract this out somewhere and test it
             */
            if (count($alternatives) == 2) {
                if ($alternatives[0]->participants > 0 && $alternatives[1]->participants > 0) {
                    
                    $cr1 = $expData['alternatives'][0]['conversionRate']['decimal'];
                    $cr2 = $expData['alternatives'][1]['conversionRate']['decimal'];

                    $n1 = $expData['alternatives'][0]['participants'];
                    $n2 = $expData['alternatives'][1]['participants'];

                    /** Might need some division by zero checks? **/
                    $numerator = $cr1 - $cr2;
                    $frac1 = $cr1 * (1 - $cr1) / $n1;
                    $frac2 = $cr2 * (1 - $cr2) / $n2;

                    $denominator = pow($frac1 + $frac2, 0.5);
                    $zscore = $denominator !== 0 ? abs($numerator / pow($frac1 + $frac2, 0.5)) : 0;
                    $expData['significance']['zscore'] = $zscore;
                    $zscoreCheatsheet = array(
                        array(0.10, 1.29),
                        array(0.05, 1.65),
                        array(0.01, 2.33),
                        array(0.001, 3.08),
                    );

                    $pValue = null;
                    foreach($zscoreCheatsheet as $zs) {
                        if ($zscore > $zs[1]) {
                            $pValue = $zs[0];
                        }
                    }
                   
                    if ($pValue !== null) {
                        $expData['significance']['pretty'] = (100 * (1 - $pValue)) . "%";
                    }
                }
            }

            $stats[] = $expData;
        }

        return $stats;
    }

    /**
     * Retrieve alternatives 
     *
     * @param string $testName
     * @param array $alternatives
     * @return array
     */
    protected function retrieveAlternatives($testName, $alternatives)
    {
        $alts = $this->cache->get("Housey::Experiment::$testName::Alternatives");
        if (!is_array($alts)) {
            $alts = $this->parseAlternatives($alternatives);
            $this->cache->set("Housey::Experiment::$testName::Alternatives", $alts);
        }

        return $alts;
    }

    /**
     * Find alternative for user
     *
     * @param string $testName
     * @param array $alternatives
     * @return mixed
     */
    protected function findAlternativeForUser($testName, $alternatives)
    {
        $alternativesArray = $this->retrieveAlternatives($testName, $alternatives);
        return $alternativesArray[$this->moduloChoice($testName, count($alternativesArray))];
    }

    /**
     * Modulo Choice
     *
     * @param string $testName
     * @param array $choiceCount
     * @return int
     */
    protected function moduloChoice($testName, $choiceCount)
    {
        /**
         * We can't have gigantic ints like ruby
         */
        $hash = substr(md5($this->options['salt'] . $testName . $this->getIdentity()), 0, 10);
        $num  = (int) hexdec($hash);
        if ($num < 0) {
            $num = 0 - $num;
        }
        return $num % $choiceCount;
    }


    /**
     * Score Participation in an experiment
     *
     * @TODO HUMAN ONLY
     *
     * @param string $testName
     * @return bool
     */
    protected function scoreParticipation($testName, $experimentName)
    {
        $alternative = $this->findAlternativeForUser($testName, $this->findAlternativesForTest($testName));
        $this->alternativeMapper->incrementParticipants($this->calculateLookup($alternative, $experimentName));
    }

    /**
     * Score conversion
     *
     * @param string $testName
     * @return bool
     */
    protected function scoreConversion($conversionName, $experimentName)
    {
        $testName = str_replace(":".$conversionName, '', $experimentName);
        $participationKey = "Housey::ParticpatingTests::" . $this->getIdentity();
        $participating = $this->cache->get($participationKey);

        if (!is_array($participating)) {
            $participating = array();
        }

        if (in_array($experimentName, $participating)) {
            $cacheKey = "Housey::Conversions::$experimentName::" . $this->getIdentity();
            $scored = $this->cache->get($cacheKey);
            /**
             * No multiple conversions at the minute
             */
            if (!$scored) {
                $alternative = $this->findAlternativeForUser($testName, $this->findAlternativesForTest($testName));
                $this->alternativeMapper->incrementConversions($this->calculateLookup($alternative, $experimentName));
                $this->cache->set($cacheKey, 1);
            }
        }
    }

    /**
     * Get Alternatives for a test
     *
     * @param string $testName
     * @return array
     */
    protected function findAlternativesForTest($testName)
    {
        $cacheKey = "Housey::$testName::Alternatives";
        $alternatives = $this->cache->get($cacheKey);
        if (!is_array($alternatives)) {
            $alternatives = $this->alternativeMapper->getByExperimentTestName($testName);
            $tmpArray = array();
            foreach($alternatives as $alt) {
                $tmpArray[$alt->content] = $alt->weight;
            }
            $alternatives = $this->parseAlternatives($tmpArray);
            $this->cache->set($cacheKey, $alternatives);
        }

        return $alternatives;
    }

    /**
     * Start experiment
     *
     * @a $alternatives should be an array where the key is the content and the
     * value is an integer weight
     *
     * @param string $testName
     * @param array $alternatives
     * @param array $options
     * @return null|Experiment
     */
    protected function startExperiment($testName, array $alternatives, array $options = null)
    {
        $defaults = array('conversionName' => $testName);
        $options = array_merge($defaults, (array) $options);

        /**
         * @todo A/Bingo uses a transaction here, but that would probably
         * require us to abstract out the connection used by the mappers or
         * something, which I can't be bothered with right now
         */
        $experiment = $this->experimentMapper->find($testName);
        if (null === $experiment) {
            $experiment = new Experiment;
            $experiment->testName = $testName;
            $experiment->status = 'Live';
            $this->experimentMapper->insert($experiment);
        } else {
            $experiment->status = 'Live';
            $this->experimentMapper->update($experiment);
        }

        // delete and re-add alternatives
        $this->alternativeMapper->deleteByExperimentId($experiment->id);

        $altClones = $alternatives;
        while(count($altClones) > 0) {
            $alt = $altClones[0];
            $weight = count($altClones) - count(array_filter($altClones, function($a) use ($alt) { return $a != $alt; }));

            $alternative = new Alternative;
            $alternative->experimentId = $experiment->id;
            $alternative->content = $alt;
            $alternative->weight = $weight;
            $alternative->lookup = $this->calculateLookup($alt, $testName);
            $this->alternativeMapper->insert($alternative);

            $altClones = array_values(array_filter($altClones, function($a) use ($alt) { return $a != $alt; }));
        }

        $this->cache->set("Housey::Experiment::Exists::$testName", true);

        $conversionNames = (array) $options['conversionName'];

        foreach ($conversionNames as $conversionName) {
            $listeners = $this->cache->get("Housey::TestsListeningToConversion::$conversionName");

            if (!is_array($listeners)) {
                $listeners = array();
            }

            if (!in_array($testName, $listeners)) {
                $listeners[] = $testName;
                $listeners = $this->cache->set("Housey::TestsListeningToConversion::$conversionName", $listeners);
            }
        }

        return $experiment;
    }

    /**
     * Parse alternatives
     *
     * @param mixed $alternatives
     */
    public static function parseAlternatives($alternatives)
    {
        if (is_array($alternatives)) {
            /**
             * If this is a zero indexed array
             */
            if (array_values($alternatives) === $alternatives) {
                return $alternatives;
            }
       
            $newAlternatives = array();
            foreach($alternatives as $alt => $weight) {
                $newAlternatives = array_merge($newAlternatives, array_fill(0, $weight, $alt));
            }

            return $newAlternatives;
        }

        if (is_integer($alternatives)) {
            return range(1, $alternatives);
        }

        throw new \InvalidArgumentException("I don't know how to turn [$alternatives] into an array of alternatives.");
    }

    /**
     * Calculate Lookup
     *
     * @param string $alternative
     * @param string $testName
     * @return string
     */
    protected function calculateLookup($alt, $testName)
    {
        return md5($this->options['salt'] . $alt . $testName);
    }


    /**
     * Set identity
     *
     * @param string
     */
    public function setIdentity($identity)
    {
        $this->identity = $identity;
        return $this;
    }

    
    /**
     * Get Identity
     */
    public function getIdentity()
    {
        if (null === $this->identity) {
            $this->identity = (string) rand(0, pow(10,10));
        }

        return $this->identity;
    }

    /**
     * Set Options
     *
     * @param array $options
     * @return HouseyService
     */
    public function setOptions(array $options)
    {
        $this->options = array_merge($this->options, $options);
        return $this;
    }

}



