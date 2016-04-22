<?php

$RECURSION_LIMIT = 1000;

/**
 * Counts the total uses of each word in the $wordList array, excluding words in $stopWords array
 *
 * @param array $wordList
 * @param array $stopWords
 * @param array &$wordFreqs
 */
function countWords(array $wordList, array $stopWords, array &$wordFreqs)
{
    if (empty($wordList)) {
        return;
    }

    $word = $wordList[0];

    if (!in_array($word, $stopWords)) {
        if (array_key_exists($word, $wordFreqs)) {
            $wordFreqs[$word] += 1;
        } else {
            $wordFreqs[$word] = 1;
        }
    }

    countWords(array_slice($wordList, 1), $stopWords, $wordFreqs);
}

/**
 * Print all words in $wordFreqs with their frequency
 *
 * @param array $wordFreqs
 */
function printWords(array $wordFreqs)
{
    if (empty($wordFreqs)) {
        return;
    }

    $word = key($wordFreqs);
    $freq = $wordFreqs[$word];

    echo $word . ' - ' . $freq . PHP_EOL;

    printWords(array_slice($wordFreqs, 1));
}

/**
 * Parse the csv $stopWordsString into an array of stop words
 *
 * @param string $stopWordsString
 * @param array $stopWords
 */
function loadStopWords($stopWordsString, array &$stopWords)
{
    if (strpos($stopWordsString, ',') === false) {
        return;
    }

    $commaPosition = strpos($stopWordsString, ',');

    $stopWords[] = substr($stopWordsString, 0, $commaPosition);

    loadStopWords(substr($stopWordsString, $commaPosition + 1), $stopWords);
}

$wordFrequencies = [];
$stopWords       = [];

preg_match_all("/[a-z]{2,}/", strtolower(file_get_contents($_SERVER['argv'][1])), $words);

loadStopWords(file_get_contents('../stop_words.txt'), $stopWords);

/**
 * This was not added due to the recursion limit, but instead due to a memory limit (stopped execution first).
 * PHP is attempting to create a new instance of the $wordList each recursive step.
 * As such, the max memory allowed will be exceeded.
 * In disabling the memory limit, we exceed 16GB of RAM usage (limit of my laptop).
 * To solve this issue, I am handling the word list much like if there was a recursion limit.
 * By splitting it into 1000 word blocks, we prevent the memory usage from exceeding 134217728 bytes (default limit).
 */
foreach (range(0, count($words[0]), $RECURSION_LIMIT) as $i) {
    countWords(array_slice($words[0], $i, $RECURSION_LIMIT), $stopWords, $wordFrequencies);
}

arsort($wordFrequencies);

printWords(array_slice($wordFrequencies, 0, 25));