<?php

class TFQuarantine
{
    /**
     * TFQuarantine constructor
     *
     * @param callable $func
     */
    public function __construct(callable $func)
    {
        // Added for 24.2
        print_r('ADDING TO FUNCTION CHAIN: ' . $func . PHP_EOL);

        $this->_funcs = [$func];
    }

    /**
     * Add a function to the function array for execution later
     *
     * @param callable $func
     * @return $this
     */
    public function bind(callable $func)
    {
        // Added for 24.2
        print_r('ADDING TO FUNCTION CHAIN: ' . $func . PHP_EOL);

        $this->_funcs[] = $func;
        return $this;
    }

    /**
     * Execute all functions in the function array in order of entry
     */
    public function execute()
    {
        $guardCallable = function ($v) {
            if (is_callable($v)) {
                return call_user_func($v);
            } else {
                return $v;
            }
        };

        $value = function () {};

        foreach ($this->_funcs as $func) {
            // Added for 24.2
            print_r('CALLING FUNCTION FROM CHAIN: ' . $func . PHP_EOL);

            $value = call_user_func($func, $guardCallable($value));
        }

        print_r($guardCallable($value));
    }
}

/**
 * Get the input file path from command line
 *
 * @return Closure
 */
function getInput()
{
    $_f = function () {
        // Added for 24.2
        print_r('EXECUTED: getInput' . PHP_EOL);

        return $_SERVER['argv'][1];
    };

    return $_f;
}

/**
 * Extract the words from the input file
 *
 * @param string $pathToFile
 * @return Closure
 */
function extractWords($pathToFile)
{
    $_f = function () use ($pathToFile) {
        // Added for 24.2
        print_r('EXECUTED: extractWords' . PHP_EOL);
        $f = file_get_contents($pathToFile);
        return explode(' ', strtolower(preg_replace("/[\W_]+/", ' ', $f)));
    };

    return $_f;
}

/**
 * Remove the stop words from the word list
 *
 * @param array $wordList
 * @return Closure
 */
function removeStopWords(array $wordList)
{
    $_f = function () use ($wordList) {
        // Added for 24.2
        print_r('EXECUTED: removeStopWords' . PHP_EOL);

        $f         = file_get_contents('../stop_words.txt');
        $stopWords = array_merge(explode(',', $f), range('a', 'z'));

        return array_diff($wordList, $stopWords);
    };

    return $_f;
}

/**
 * Count the number of uses of each word in the word list
 *
 * @param array $wordList
 * @return array
 */
function frequencies(array $wordList)
{
    // Added for 24.2
    print_r('EXECUTED: frequencies' . PHP_EOL);

    return array_count_values($wordList);
}

/**
 * Sort the word frequency array in descending order by frequency
 *
 * @param array $wordFreqs
 * @return array
 */
function sortFreqs(array $wordFreqs)
{
    // Added for 24.2
    print_r('EXECUTED: sortFreqs' . PHP_EOL);

    arsort($wordFreqs);

    return $wordFreqs;
}

/**
 * Print the top 25 most frequent words (for 24.1 + 24.2)
 *
 * @param array $wordFreqs
 * @return string
 */
function top25Freqs(array $wordFreqs)
{
    // Added for 24.2
    print_r('EXECUTED: top25Freqs' . PHP_EOL . PHP_EOL);

    $top25 = '';

    foreach (array_slice($wordFreqs, 0, 25) as $word => $freq) {
        $top25 .= $word . ' - ' . $freq . PHP_EOL;
    }

    return $top25;
}

/**
 * Print the top 25 most frequent words (for 24.3)
 *
 * @param array $wordFreqs
 */
function top25Freqs2(array $wordFreqs)
{
    // Added for 24.2
    print_r('EXECUTED: top25Freqs2' . PHP_EOL . PHP_EOL);

    $_f = function () use ($wordFreqs) {
        foreach (array_slice($wordFreqs, 0, 25) as $word => $freq) {
            print_r($word . ' - ' . $freq . PHP_EOL);
        }
    };

    return $_f;
}

(new TFQuarantine('getInput'))
    ->bind('extractWords')
    ->bind('removeStopWords')
    ->bind('frequencies')
    ->bind('sortFreqs')
    ->bind('top25Freqs2')
    ->execute();