<?php

/**
 * Return the top 25 most frequent words
 *
 * @param array $wordList
 * @return array
 */
function top25(array $wordList)
{
    $frequencies = array_count_values($wordList);

    uasort($frequencies, function ($a, $b) {
        return $a < $b;
    });

    $top25 = [];

    $count = 0;

    foreach ($frequencies as $word => $freq) {
        if ($count >= 25) {
            break;
        }

        $top25[$word] = $freq;

        $count++;
    }

    return $top25;
}