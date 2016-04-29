<?php

/**
 * Print all words and their frequencies
 *
 * @param array $wordFreqs
 */
function printWords(array $wordFreqs)
{
    while (!empty($wordFreqs)) {
        $word = key($wordFreqs);
        $freq = array_shift($wordFreqs);

        print_r($word . ' - ' . $freq . PHP_EOL);
    }
}