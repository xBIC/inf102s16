<?php

class ActiveWFObject extends Thread
{
    /**
     * ActiveWFObject constructor
     * Starts a new thread and instantiates needed variables
     */
    public function __construct()
    {
        self::__construct();
        $this->name  = get_class($this);
        $this->queue = new SplQueue();
        $this->_stop = false;
        $this->start();
    }

    /**
     * Calls dispatch on items in the queue until thread is stopped
     */
    public function run()
    {
        while (false === $this->_stop) {
            $message = $this->queue->shift();
            $this->_dispatch($message);
            if ('die' == $message[0]) {
                $this->_stop = true;
            }
        }
    }
}

function send($receiver, $message)
{
    $receiver->queue->push($message);
}

class DataStorageManager extends ActiveWFObject
{
    public $_data = '';

    /**
     * Handle a message based on it's first parameter
     *
     * @param array $message
     */
    public function _dispatch(array $message)
    {
        if ('init' == $message[0]) {
            $this->_init(array_slice($message, 1));
        } elseif ('send_word_freqs' == $message[0]) {
            $this->_processWords(array_slice($message, 1));
        } else {
            send($this->_stopWordManager, $message);
        }
    }

    /**
     * DataStorageManager constructor
     * Load word file
     *
     * @param array $message
     */
    public function _init(array $message)
    {
        $pathToFile             = $message[0];
        $this->_stopWordManager = $message[1];

        $f           = file_get_contents($pathToFile);
        $this->_data = strtolower(preg_replace("/[\W_]+/", ' ', $f));
    }

    /**
     * Process the words from the word file
     *
     * @param array $message
     */
    public function _processWords(array $message)
    {
        $recipient = $message[0];
        $dataStr   = $this->_data;
        $words     = explode(' ', $dataStr);

        foreach ($words as $word) {
            send($this->_stopWordManager, ['filter', $word]);
        }

        send($this->_stopWordManager, ['top25', $recipient]);
    }
}

class StopWordManager extends ActiveWFObject
{
    public $_stopWords = [];

    /**
     * Handle a message based on it's first parameter
     *
     * @param array $message
     */
    public function _dispatch(array $message)
    {
        if ('init' == $message[0]) {
            $this->_init(array_slice($message, 1));
        } elseif ('filter' == $message[0]) {
            return $this->_filter(array_slice($message, 1));
        } else {
            send($this->_wordFreqsManager, $message);
        }
    }

    /**
     * StopWordManager constructor.
     * Load stop words file
     *
     * @param array $message
     */
    public function _init(array $message)
    {
        $f                      = file_get_contents('../stop_words.txt');
        $this->_stopWords       = array_merge(explode(',', $f), range('a', 'z'));
        $this->_wordFreqManager = $message[0];
    }

    /**
     * Send only non stopwords
     *
     * @param array $message
     */
    public function _filter(array $message)
    {
        $word = $message[0];

        if (!in_array($word, $this->_stopWords)) {
            send($this->_wordFreqManager, ['word', $word]);
        }
    }
}

class WordFrequencyManager extends ActiveWFObject
{
    public $_wordFreqs = [];

    /**
     * Handle a message based on it's first parameter
     *
     * @param array $message
     */
    public function _dispatch(array $message)
    {
        if ('word' == $message[0]) {
            $this->_incrementCount(array_slice($message, 1));
        } elseif ('top25' == $message[0]) {
            $this->_top25(array_slice($message, 1));
        }
    }

    /**
     * Count the use of a word
     *
     * @param array $message
     */
    public function _incrementCount(array $message)
    {
        $word = $message[0];

        if (array_key_exists($word, $this->_wordFreqs)) {
            $this->_wordFreqs[$word] += 1;
        } else {
            $this->_wordFreqs[$word] = 1;
        }
    }

    /**
     * Sort and send the top 25 most used words
     *
     * @param array $message
     */
    public function _top25(array $message)
    {
        $recipient   = $message[0];
        $freqsSorted = $this->_wordFreqs;
        arsort($freqsSorted);

        send($recipient, ['top25', $freqsSorted]);
    }
}

class WordFrequencyController extends ActiveWFObject
{
    public function _dispatch(array $message)
    {
        if ('run' == $message[0]) {
            $this->_run(array_slice($message, 1));
        } elseif ('top25' == $message[0]) {
            $this->_display(array_slice($message, 1));
        } else {
            throw new Exception('Message not understood ' . $message[0]);
        }
    }

    public function _run(array $message)
    {
        $this->_storageManager = $message[0];
        send($this->_storageManager, ['send_word_freqs', $this]);
    }

    public function _display(array $message)
    {
        $wordFreqs = $message[0];

        foreach ($wordFreqs as $word => $freq) {
            echo $word . ' - ' . $freq . PHP_EOL;
        }

        send($this->_storageManager, ['die']);
        $this->_stop = true;
    }
}

$wordFreqManager = new WordFrequencyManager();

$stopWordManager = new StopWordManager();
send($stopWordManager, ['init', $wordFreqManager]);

$storageManager = new DataStorageManager();
send($storageManager, ['init', $_SERVER['argv'][1], $stopWordManager]);

$wfcontroller = new WordFrequencyController();
send($wfcontroller, ['run', $storageManager]);

foreach ([$wordFreqManager, $stopWordManager, $storageManager, $wfcontroller] as $t) {
    $t->join();
}


