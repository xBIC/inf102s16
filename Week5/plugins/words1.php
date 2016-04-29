<?php

/**
 * Return an array of words from the input file, excluding the stop words
 *
 * @param string $pathToFile
 * @return array
 */
function extractWords($pathToFile)
{
    $wf       = file_get_contents($pathToFile);
    $wordList = explode(' ', strtolower(preg_replace("/[\W_]+/", ' ', $wf)));

    $swf       = file_get_contents('../stop_words.txt');
    $stopWords = array_merge(explode(',', $swf), range('a', 'z'));

    return array_diff($wordList, $stopWords);
}