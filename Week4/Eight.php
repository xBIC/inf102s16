<?php

function readWordFile($pathToFile, callable $func)
{
    $f = file_get_contents($pathToFile);

    call_user_func($func, $f, 'normalize');
}

function filterChars($strData, callable $func)
{
    call_user_func($func, preg_replace("/[\W_]+/", ' ', $strData), 'scan');
}

function normalize($strData, callable $func)
{
    call_user_func($func, strtolower($strData), 'removeStopWords');
}

function scan($strData, callable $func)
{
    call_user_func($func, explode(' ', $strData), 'frequencies');
}

function removeStopWords(array $wordList, callable $func)
{
    $stopWords = array_merge(explode(',', file_get_contents('../stop_words.txt')), range('a', 'z'));

    call_user_func($func, array_diff($wordList, $stopWords), 'sortFreq');
}

function frequencies(array $wordList, callable $func)
{
    $wf = array_count_values($wordList);

    call_user_func($func, $wf, 'printText');
}

function sortFreq(array $wordFreq, callable $func)
{
    arsort($wordFreq);

    call_user_func($func, $wordFreq, 'noOp');
}

function printText(array $wordFreq, callable $func)
{
    foreach (array_slice($wordFreq, 0, 25) as $word => $frequency) {
        echo $word . ' - ' . $frequency . PHP_EOL;
    }

    call_user_func($func, null);
}

function noOp($func)
{
    return;
}

readWordFile($_SERVER['argv'][1], 'filterChars');