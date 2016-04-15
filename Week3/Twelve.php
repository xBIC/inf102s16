<?php

/**
 * Extract all words from an input file and add them to the data field of an object
 *
 * @param stdClass $obj
 * @param string $path_to_file
 */
function extractWords(stdClass $obj, $path_to_file)
{
    $f         = file_get_contents($path_to_file);
    $obj->data = explode(' ', strtolower(preg_replace("/[\W_]+/", ' ', $f)));
}

/**
 * Load the stop words from a file and add them to the stop_words field of an object
 *
 * @param stdClass $obj
 */
function loadStopWords(stdClass $obj)
{
    $f              = file_get_contents('../stop_words.txt');
    $obj->stopWords = explode(',', $f);
}

/**
 * Update the word frequency in the object for the passed word
 *
 * @param stdClass $obj
 * @param string $w
 */
function incrementCount(stdClass $obj, $w)
{
    if (empty($obj->freqs[$w])) {
        $obj->freqs[$w] = 1;
    } else {
        $obj->freqs[$w] += 1;
    }
}

$dataStorageObject        = new stdClass();
$dataStorageObject->data  = [];
$dataStorageObject->init  = function ($pathToFile) use ($dataStorageObject) {
    extractWords($dataStorageObject, $pathToFile);
};
$dataStorageObject->words = function () use ($dataStorageObject) {
    return $dataStorageObject->data;
};

$stopWordsObject             = new stdClass();
$stopWordsObject->stopWords  = [];
$stopWordsObject->init       = function () use ($stopWordsObject) {
    loadStopWords($stopWordsObject);
};
$stopWordsObject->isStopWord = function ($word) use ($stopWordsObject) {
    return in_array($word, $stopWordsObject->stopWords);
};

$wordFrequencyObject                 = new stdClass();
$wordFrequencyObject->freqs          = [];
$wordFrequencyObject->incrementCount = function ($w) use ($wordFrequencyObject) {
    incrementCount($wordFrequencyObject, $w);
};
$wordFrequencyObject->sorted         = function () use ($wordFrequencyObject) {
    arsort($wordFrequencyObject->freqs);
    return $wordFrequencyObject->freqs;
};

$dsoInit = $dataStorageObject->init;
$dsoInit($_SERVER['argv'][1]);

$swoInit = $stopWordsObject->init;
$swoInit();

$dsoWords = $dataStorageObject->words;

foreach ($dsoWords() as $w) {
    $swoIsStopWord = $stopWordsObject->isStopWord;

    if (strlen($w) >= 2 && !$swoIsStopWord($w)) {
        $wfoIncrementCount = $wordFrequencyObject->incrementCount;
        $wfoIncrementCount($w);
    }
}

$wordFrequencyObject->top25 = function () use ($wordFrequencyObject) {
    $wfoSorted = $wordFrequencyObject->sorted;
    $wordFreqs = $wfoSorted();

    foreach (array_splice($wordFreqs, 0, 25) as $k => $w) {
        echo $k . ' - ' . $w . PHP_EOL;
    }
};

$wfoTop25 = $wordFrequencyObject->top25;
$wfoTop25();
