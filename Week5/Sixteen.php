<?php

// USING HOMEWORK: Week3/Fifteen.php

/**
 * Class EventManager
 *
 * Manage all events
 */
class EventManager
{
    /**
     * EventManager constructor
     */
    public function __construct()
    {
        $this->_subscriptions = [];
    }

    /**
     * Subscribe a handler to be run when an event occurs
     *
     * @param string $eventType
     * @param array $handler
     */
    public function subscribe($eventType, array $handler)
    {
        if (array_key_exists($eventType, $this->_subscriptions)) {
            $this->_subscriptions[$eventType][] = $handler;
        } else {
            $this->_subscriptions[$eventType] = [$handler];
        }
    }

    /**
     * Publish data for an event
     *
     * @param array $event
     */
    public function publish(array $event)
    {
        $event_type = $event[0];

        if (array_key_exists($event_type, $this->_subscriptions)) {
            foreach ($this->_subscriptions[$event_type] as $h) {
                $class = $h[0];
                $method = $h[1];

                $class->$method($event);
            }
        }
    }
}

/**
 * Class DataStorage
 *
 * Data for the application
 */
class DataStorage
{
    /**
     * DataStorage constructor
     *
     * @param EventManager $eventManager
     */
    public function __construct(EventManager $eventManager)
    {
        $this->_eventManager = $eventManager;
        $this->_eventManager->subscribe('load', [$this, 'load']);
        $this->_eventManager->subscribe('start', [$this, 'produceWords']);
    }

    /**
     * Load a text file
     *
     * @param array $event
     */
    public function load(array $event)
    {
        $pathToFile = $event[1];
        $f = file_get_contents($pathToFile);
        $this->_data = explode(' ', strtolower(preg_replace("/[\W_]+/", ' ', $f)));
    }

    /**
     * Publish words to the EventManager
     *
     * @param array $event
     */
    public function produceWords(array $event)
    {
        foreach ($this->_data as $w) {
            $this->_eventManager->publish(['word', $w]);
        }

        $this->_eventManager->publish(['eof', null]);
    }
}

/**
 * Class StopWordFilter
 *
 * Filter out stop words
 */
class StopWordFilter
{
    /**
     * StopWordFilter constructor
     *
     * @param EventManager $eventManager
     */
    public function __construct(EventManager $eventManager)
    {
        $this->_stopWords = [];
        $this->_eventManager = $eventManager;
        $this->_eventManager->subscribe('load', [$this, 'load']);
        $this->_eventManager->subscribe('word', [$this, 'isStopWord']);
    }

    /**
     * Load the stop words file
     *
     * @param array $event
     */
    public function load(array $event)
    {
        $f = file_get_contents('../stop_words.txt');
        $this->_stopWords = array_merge(explode(',', $f), range('a', 'z'));
    }

    /**
     * Check if the word is a stop word. If it is not, publish the word as a valid word.
     *
     * @param array $event
     */
    public function isStopWord(array $event)
    {
        $word = $event[1];

        if (!in_array($word, $this->_stopWords)) {
            $this->_eventManager->publish(['valid_word', $word]);
        }
    }
}

/**
 * Class WordFrequencyCounter
 *
 * Count the frequency of each word in a loaded file
 */
class WordFrequencyCounter
{
    /**
     * WordFrequencyCounter constructor
     *
     * @param EventManager $eventManager
     */
    public function __construct(EventManager $eventManager)
    {
        $this->_wordFreqs = [];
        $this->_eventManager = $eventManager;
        $this->_eventManager->subscribe('valid_word', [$this, 'incrementCount']);
        $this->_eventManager->subscribe('print', [$this, 'printFreqs']);
    }

    /**
     * Increment the word frequency counter for a word
     *
     * @param array $event
     */
    public function incrementCount(array $event)
    {
        $word = $event[1];

        if (array_key_exists($word, $this->_wordFreqs)) {
            $this->_wordFreqs[$word] += 1;
        } else {
            $this->_wordFreqs[$word] = 1;
        }
    }

    /**
     * Output the top 25 most frequent words
     *
     * @param array $event
     */
    public function printFreqs(array $event)
    {
        $wordFreqs = $this->_wordFreqs;
        arsort($wordFreqs);

        foreach (array_splice($wordFreqs, 0, 25) as $k => $w) {
            echo $k . ' - ' . $w . PHP_EOL;
        }
    }
}

/**
 * Class WordFrequencyApplication
 */
class WordFrequencyApplication
{
    /**
     * WordFrequencyApplication constructor
     *
     * @param EventManager $eventManager
     */
    public function __construct(EventManager $eventManager)
    {
        $this->_eventManager = $eventManager;
        $this->_eventManager->subscribe('run', [$this, 'run']);
        $this->_eventManager->subscribe('eof', [$this, 'stop']);
    }

    /**
     * Run the word frequency application
     *
     * @param array $event
     */
    public function run(array $event)
    {
        $pathToFile = $event[1];
        $this->_eventManager->publish(['load', $pathToFile]);
        $this->_eventManager->publish(['start', null]);
    }

    /**
     * Stop the application and print the top words
     *
     * @param array $event
     */
    public function stop(array $event)
    {
        $this->_eventManager->publish(['print', null]);
    }
}

$em = new EventManager();
new DataStorage($em);
new StopWordFilter($em);
new WordFrequencyCounter($em);
new WordFrequencyApplication($em);

$em->publish(['run', $_SERVER['argv'][1]]);

// CODE FOR PRINTING OUT THE CLASSES AND METHODS BELOW

print_r(PHP_EOL);

$tokens = token_get_all(file_get_contents(__FILE__));

$classes = [];

$classFound = false;

foreach ($tokens as $key => $token) {
    // If token is empty or there is no token[1] field, skip to next token
    if (empty($token) || empty($token[1]) || ' ' == $token[1]) {
        continue;
    }

    if ($classFound) {
        $classFound = false;
        $methods = get_class_methods($token[1]);

        print_r('Class : ' . $token[1] . PHP_EOL);

        $methodString = '';

        if (!empty($methods)) {
            foreach ($methods as $key => $method) {
                if (empty($methodString)) {
                    $methodString = $method;
                } else {
                    $methodString .= ', ' . $method;
                }
            }

            print_r('Methods : ' . $methodString . PHP_EOL);
        }

        print_r(PHP_EOL);
    }

    if ('class' == $token[1]) {
        $classFound = true;
    }
}