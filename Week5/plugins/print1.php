<?php

/**
 * Print all words and their frequencies
 *
 * @param array $wordFreqs
 */
function printWords(array $wordFreqs)
{
    foreach ($wordFreqs as $word => $freq) {
        print_r($word . ' - ' . $freq . PHP_EOL);
    }
}