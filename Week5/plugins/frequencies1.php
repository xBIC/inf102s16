<?php

/**
 * Return the top 25 most frequent words
 *
 * @param array $wordList
 * @return array
 */
function top25(array $wordList)
{
    $wordFreqs = [];

    foreach ($wordList as $word) {
        if (array_key_exists($word, $wordFreqs)) {
            $wordFreqs[$word] += 1;
        } else {
            $wordFreqs[$word] = 1;
        }
    }

    arsort($wordFreqs);

    return array_slice($wordFreqs, 0, 25);
}