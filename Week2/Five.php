<?php

/**
 * Get the contents of a file
 *
 * @param string $filePath
 * @return string
 */
function readAFile($filePath)
{
    return file_get_contents($filePath);
}

/**
 * Replace all non-alphanumeric chars with a space
 *
 * @param string $data
 * @return mixed
 */
function filterCharsAndNormalize($data)
{
    return strtolower(preg_replace("/[\W_]+/", ' ', $data));
}

/**
 * Split the string into an array on space
 *
 * @param string $data
 * @return array
 */
function scan($data)
{
    return explode(' ', $data);
}

/**
 * Remove stopwords from the word list
 *
 * @param array $wordList
 * @return mixed
 */
function removeStopWords(array $info)
{
    $wordList = $info[0];
    $stopWordsPath = $info[1];

    $stopWords = file_get_contents($stopWordsPath);
    $stopWordsList = explode(',', $stopWords);

    foreach ($wordList as $key => $word) {
        if (in_array($word, $stopWordsList)) {
            unset($wordList[$key]);
        }
    }

    return $wordList;
}

/**
 * Determine the frequency of use for each word in the word list
 *
 * @param array $wordList
 * @return array
 */
function frequencies(array $wordList)
{
    $frequencyList = [];

    foreach ($wordList as $word) {
        if (strlen($word) < 2) {
            continue;
        }

        if (array_key_exists($word, $frequencyList)) {
            $frequencyList[$word] += 1;
        } else {
            $frequencyList[$word] = 1;
        }
    }

    return $frequencyList;
}

/**
 * Sort the frequency list in descending order
 *
 * @param array $frequencyList
 * @return bool
 */
function sortDesc(array $frequencyList)
{
    arsort($frequencyList);
    return $frequencyList;
}

/**
 * Output the frequency list array
 *
 * @param array $frequencyList
 */
function printAll(array $frequencyList)
{
    if (!empty($frequencyList)) {
        echo key($frequencyList) . '  -  ' . current($frequencyList) . PHP_EOL;
        printAll(array_slice($frequencyList, 1));
    }
}

printAll(array_slice(sortDesc(frequencies(removeStopWords([scan(filterCharsAndNormalize(readAFile($_SERVER['argv'][1]))), $_SERVER['argv'][2]]))), 0, 25));