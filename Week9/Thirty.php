<?php

/**
 * PHP does not have a built in map function that will map a function to an iterator
 * As such, I had to use the functional-php library
 */
include 'functional-php/src/Functional/_import.php';

function partition($dataStr, $nlines)
{
    $lines = explode(PHP_EOL, $dataStr);

    foreach (range(0, count($lines), $nlines) as $i) {
        yield implode(PHP_EOL, array_slice($lines, $i, $nlines));
    }
}

function splitWords($dataStr)
{
    $_scan = function ($strData) {
        return explode(' ', strtolower(preg_replace("/[\W_]+/", ' ', $strData)));
    };

    $_removeStopWords = function ($wordList) {
        $f         = file_get_contents('../stop_words.txt');
        $stopWords = array_merge(explode(',', $f), range('a', 'z'));

        return array_diff($wordList, $stopWords);
    };

    $result = [];
    $words  = $_removeStopWords($_scan($dataStr));

    foreach ($words as $word) {
        $result[] = [$word, 1];
    }

    return $result;
}

function countWords($pairsList1, $pairsList2)
{
    $mapping = [];

    if (!empty($pairsList1)) {
        foreach ($pairsList1 as $item) {
            $mapping[$item[0]] = $item[1];
        }
    }

    foreach ($pairsList2 as $p) {
        if (array_key_exists($p[0], $mapping)) {
            $mapping[$p[0]] += $p[1];
        } else {
            $mapping[$p[0]] = 1;
        }
    }

    $itemMapping = [];

    foreach ($mapping as $word => $freq) {
        $itemMapping[] = [$word, $freq];
    }

    return $itemMapping;
}

function readAFile($pathToFile)
{
    $f = file_get_contents($pathToFile);

    return $f;
}

function mySort($wordFreq)
{
    $formattedWordFreqs = [];
    foreach ($wordFreq as $item) {
        $formattedWordFreqs[$item[0]] = $item[1];
    }

    arsort($formattedWordFreqs);
    return $formattedWordFreqs;
}

$splits = \Functional\map(partition(readAFile($_SERVER['argv'][1]), 200), 'splitWords');
array_unshift($splits, []);
$wordFreqs = mySort(array_reduce($splits, 'countWords'));

foreach (array_slice($wordFreqs, 0, 25) as $word => $freq) {
    echo $word . ' - ' . $freq . PHP_EOL;
}