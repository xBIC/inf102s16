<?php

/**
 * Read a word file as a string and pass resultant string to filterChars function
 *
 * @param string $pathToFile
 * @param callable $func
 */
function readWordFile($pathToFile, callable $func)
{
    $f = file_get_contents($pathToFile);

    call_user_func($func, $f, 'normalize');
}

/**
 * Replace all non-word characters with a space and pass resultant string to normalize function
 *
 * @param string $strData
 * @param callable $func
 */
function filterChars($strData, callable $func)
{
    call_user_func($func, preg_replace("/[\W_]+/", ' ', $strData), 'scan');
}

/**
 * Lowercase all letters in the string and pass resultant string to scan function
 *
 * @param string $strData
 * @param callable $func
 */
function normalize($strData, callable $func)
{
    call_user_func($func, strtolower($strData), 'removeStopWords');
}

/**
 * Split the string on whitespace and pass the resultant array to removeStopWords function
 *
 * @param string $strData
 * @param callable $func
 */
function scan($strData, callable $func)
{
    call_user_func($func, explode(' ', $strData), 'frequencies');
}

/**
 * Read in the stop words file and remove stop words from the list of words
 * Pass the resultant array to frequencies function
 *
 * @param array $wordList
 * @param callable $func
 */
function removeStopWords(array $wordList, callable $func)
{
    $stopWords = array_merge(explode(',', file_get_contents('../stop_words.txt')), range('a', 'z'));

    call_user_func($func, array_diff($wordList, $stopWords), 'sortFreq');
}

/**
 * Count the number of times each word is used and pass the resultant array to sortFreq function
 *
 * @param array $wordList
 * @param callable $func
 */
function frequencies(array $wordList, callable $func)
{
    $wf = array_count_values($wordList);

    call_user_func($func, $wf, 'printText');
}

/**
 * Sort the word frequency array in descending order by total uses and pass the resultant array to printText function
 *
 * @param array $wordFreq
 * @param callable $func
 */
function sortFreq(array $wordFreq, callable $func)
{
    arsort($wordFreq);

    call_user_func($func, $wordFreq, 'noOp');
}

/**
 * Print the top 25 most used words and call the noOp function
 *
 * @param array $wordFreq
 * @param callable $func
 */
function printText(array $wordFreq, callable $func)
{
    foreach (array_slice($wordFreq, 0, 25) as $word => $frequency) {
        echo $word . ' - ' . $frequency . PHP_EOL;
    }

    call_user_func($func, null);
}

/**
 * Ends a chain of function calls
 *
 * @param $func
 */
function noOp($func)
{
    return;
}

readWordFile($_SERVER['argv'][1], 'filterChars');