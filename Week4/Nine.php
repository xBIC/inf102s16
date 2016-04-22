<?php

/**
 * Class TFTheOne
 *
 * Handles storing the data, binding operations, and printing the data
 */
class TFTheOne
{
    /**
     * TFTheOne constructor
     *
     * @param $v
     */
    function __construct($v)
    {
        $this->_value = $v;
    }

    /**
     * Update the value by running the bound function with the previous value
     *
     * @param callable $func
     * @return $this
     */
    function bind(callable $func)
    {
        $this->_value = call_user_func($func, $this->_value);
        return $this;
    }

    /**
     * Print out the value to the console
     */
    function printMe()
    {
        echo $this->_value;
    }
}

/**
 * Read a text file
 *
 * @param string $pathToFile
 * @return string
 */
function readWordFile($pathToFile)
{
    $data = file_get_contents($pathToFile);

    return $data;
}

/**
 * Replace non-word characters with an empty space
 *
 * @param string $strData
 * @return mixed
 */
function filterChars($strData)
{
    return preg_replace("/[\W_]+/", ' ', $strData);
}

/**
 * Lowercase all letters in the string
 *
 * @param string $strData
 * @return string
 */
function normalize($strData)
{
    return strtolower($strData);
}

/**
 * Split the string into an array on whitespace characters
 *
 * @param string $strData
 * @return array
 */
function scan($strData)
{
    return explode(' ', $strData);
}

/**
 * Remove the stop words from the word list
 *
 * @param array $wordList
 * @return array
 */
function removeStopWords(array $wordList)
{
    $stopWords = array_merge(explode(',', file_get_contents('../stop_words.txt')), range('a', 'z'));

    return array_diff($wordList, $stopWords);
}

/**
 * Count the total uses of each word in the word list
 *
 * @param array $wordList
 * @return array
 */
function frequencies(array $wordList)
{
    return array_count_values($wordList);
}

/**
 * Sort the word frequency array in descending order by value
 *
 * @param array $wordFreq
 * @return array
 */
function sortWords(array $wordFreq)
{
    arsort($wordFreq);

    return $wordFreq;
}

/**
 * Add the top 25 most used words to a formatted string
 *
 * @param array $wordFreqs
 * @return string
 */
function top25Freqs(array $wordFreqs)
{
    $top25 = "";

    foreach (array_slice($wordFreqs, 0, 25) as $word => $frequency) {
        $top25 .= $word . ' - ' . $frequency . PHP_EOL;
    }

    return $top25;
}

(new TFTheOne($_SERVER['argv'][1]))
    ->bind('readWordFile')
    ->bind('filterChars')
    ->bind('normalize')
    ->bind('scan')
    ->bind('removeStopWords')
    ->bind('frequencies')
    ->bind('sortWords')
    ->bind('top25Freqs')
    ->printMe();