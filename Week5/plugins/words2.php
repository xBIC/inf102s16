<?php

/**
 * Return an array of words from the input file, excluding the stop words
 *
 * @param string $pathToFile
 * @return array
 */
function extractWords($pathToFile)
{
    preg_match_all("/[a-z]{2,}/", strtolower(file_get_contents($pathToFile)), $wordList);

    $stopWords = explode(',', file_get_contents('../stop_words.txt'));

    $wordsArray = [];

    foreach ($wordList[0] as $word) {
        if (!in_array($word, $stopWords)) {
            $wordsArray[] = $word;
        }
    }

    return $wordsArray;
}