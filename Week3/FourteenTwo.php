<?php

/**
 * Class WordFrequencyFramework
 *
 * Allows event handlers to be registered and executed
 */
class WordFrequencyFramework
{
    protected $_loadEventHandlers = [];
    protected $_doworkEventHandlers = [];
    protected $_endEventHandlers = [];

    /**
     * Register the handler for callback when a load event is triggered
     *
     * @param array $handler
     */
    public function registerForLoadEvent(array $handler)
    {
        $this->_loadEventHandlers[] = $handler;
    }

    /**
     * Register the handler for callback when a dowork event is triggered
     *
     * @param array $handler
     */
    public function registerForDoworkEvent(array $handler)
    {
        $this->_doworkEventHandlers[] = $handler;
    }

    /**
     * Register the handler for callback when an end event is triggered
     *
     * @param array $handler
     */
    public function registerForEndEvent(array $handler)
    {
        $this->_endEventHandlers[] = $handler;
    }

    /**
     * Called to run the framework and execute all registered event handlers
     *
     * @param string $pathToFile
     */
    public function run($pathToFile)
    {
        foreach ($this->_loadEventHandlers as $h) {
            $class  = $h[0];
            $method = $h[1];
            $class->$method($pathToFile);
        }

        foreach ($this->_doworkEventHandlers as $h) {
            $class  = $h[0];
            $method = $h[1];
            $class->$method();
        }

        foreach ($this->_endEventHandlers as $h) {
            $class  = $h[0];
            $method = $h[1];
            $class->$method();
        }
    }
}

/**
 * Class DataStorage
 *
 * Stories all the word data for the application
 */
class DataStorage
{
    protected $_data = [];
    protected $_stopWordFilter = null;
    protected $_wordEventHandler = [];

    /**
     * DataStorage constructor.
     *
     * @param WordFrequencyFramework $wfapp
     * @param StopWordFilter $stopWordFilter
     */
    public function __construct(WordFrequencyFramework $wfapp, StopWordFilter $stopWordFilter)
    {
        $this->_stopWordFilter = $stopWordFilter;
        $wfapp->registerForLoadEvent([$this, '__load']);
        $wfapp->registerForDoworkEvent([$this, '__produceWords']);
    }

    /**
     * Load the text file and parse the words
     *
     * @param string $pathToFile
     */
    public function __load($pathToFile)
    {
        $f           = file_get_contents($pathToFile);
        $this->_data = explode(' ', strtolower(preg_replace("/[\W_]+/", ' ', $f)));
    }

    /**
     * Iterates through the array of words in storage calling back handlers for each word
     */
    public function __produceWords()
    {
        foreach ($this->_data as $w) {
            if (strlen($w) >= 2 && !$this->_stopWordFilter->isStopWord($w)) {
                foreach ($this->_wordEventHandler as $h) {
                    $class  = $h[0];
                    $method = $h[1];
                    $class->$method($w);
                }
            }
        }
    }

    /**
     * Register a handler to receive word event callbacks
     *
     * @param array $handler
     */
    public function registerForWordEvent(array $handler)
    {
        $this->_wordEventHandler[] = $handler;
    }
}

/**
 * Class StopWordFilter
 *
 * Stores the stop words and included functionality to detect them
 */
class StopWordFilter
{
    protected $_stopWords = [];

    /**
     * StopWordFilter constructor.
     *
     * @param WordFrequencyFramework $wfapp
     */
    public function __construct(WordFrequencyFramework $wfapp)
    {
        $wfapp->registerForLoadEvent([$this, '__load']);
    }

    /**
     * Loads the stop words text file and adds all single letter lowercase characters from a to z
     *
     * @param string $ignore
     */
    public function __load($ignore)
    {
        $f = file_get_contents('../stop_words.txt');

        $this->_stopWords = array_merge(explode(',', $f), range('a', 'z'));
    }

    /**
     * Check if a word is a stop word
     *
     * @param string $word
     * @return bool
     */
    public function isStopWord($word)
    {
        return in_array($word, $this->_stopWords);
    }
}

/**
 * Class WordFrequencyCounter
 *
 * Has functionality to count the frequency of a word and print out the most frequent words
 */
class WordFrequencyCounter
{
    protected $_wordFreqs = [];

    /**
     * WordFrequencyCounter constructor.
     *
     * @param WordFrequencyFramework $wfapp
     * @param DataStorage $dataStorage
     */
    public function __construct(WordFrequencyFramework $wfapp, DataStorage $dataStorage)
    {
        $dataStorage->registerForWordEvent([$this, '__incrementCount']);
        $wfapp->registerForEndEvent([$this, '__printFreqs']);
    }

    /**
     * Increment the word frequency counter for a word
     *
     * @param string $word
     */
    public function __incrementCount($word)
    {
        if (array_key_exists($word, $this->_wordFreqs)) {
            $this->_wordFreqs[$word] += 1;
        } else {
            $this->_wordFreqs[$word] = 1;
        }
    }

    /**
     * Output the top 25 most frequent words
     */
    public function __printFreqs()
    {
        $wordFreqs = $this->_wordFreqs;
        arsort($wordFreqs);

        foreach (array_splice($wordFreqs, 0, 25) as $k => $w) {
            echo $k . ' - ' . $w . PHP_EOL;
        }
    }
}

/**
 * Class ZWordFrequencyCounter
 *
 * Has functionality to count the frequency of "z" words and print out the total
 */
class ZWordFrequencyCounter
{
    protected $_zWordFreqs = 0;

    /**
     * ZWordFrequencyCounter constructor.
     *
     * @param WordFrequencyFramework $wfapp
     * @param DataStorage $dataStorage
     */
    public function __construct(WordFrequencyFramework $wfapp, DataStorage $dataStorage)
    {
        $dataStorage->registerForWordEvent([$this, '__incrementCount']);
        $wfapp->registerForEndEvent([$this, '__printFreqs']);
    }

    /**
     * Increment the "z" words frequency counter
     *
     * @param string $word
     */
    public function __incrementCount($word)
    {
        if (strpos($word, 'z') !== false) {
            $this->_zWordFreqs += 1;
        }
    }

    /**
     * Output the total number of "z" words
     */
    public function __printFreqs()
    {
        echo PHP_EOL . 'Total "z" words - ' . $this->_zWordFreqs . PHP_EOL;
    }
}

$wfapp                 = new WordFrequencyFramework();
$stopWordFilter        = new StopWordFilter($wfapp);
$dataStorage           = new DataStorage($wfapp, $stopWordFilter);
$wordFrequencyCounter  = new WordFrequencyCounter($wfapp, $dataStorage);
$zWordFrequencyCounter = new ZWordFrequencyCounter($wfapp, $dataStorage);
$wfapp->run($_SERVER['argv'][1]);