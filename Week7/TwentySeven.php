<?php

/**
 * Character generator for the input file
 *
 * @param string $filename
 * @return Generator
 */
function characters($filename)
{
    $f = fopen($filename, "r");
    while (($line = fgets($f)) !== false) {
        foreach (str_split($line) as $c) {
            yield $c;
        }
    }

    fclose($f);
}

/**
 * Word generator for input file
 *
 * @param string $filename
 * @return Generator
 */
function allWords($filename)
{
    $startChar = true;

    foreach (characters($filename) as $c) {
        if ($startChar == true) {
            $word = "";

            if (ctype_alnum($c)) {
                $word      = strtolower($c);
                $startChar = false;
            } else {
                continue;
            }
        } else {
            if (ctype_alnum($c)) {
                $word .= strtolower($c);
            } else {
                $startChar = true;
                yield $word;
            }
        }
    }
}

/**
 * Non-stop words generator for input file
 *
 * @param string $filename
 * @return Generator
 */
function nonStopWords($filename)
{
    $f         = file_get_contents('../stop_words.txt');
    $stopWords = array_merge(explode(',', $f), range('a', 'z'));

    foreach (allWords($filename) as $word) {
        if (!in_array($word, $stopWords)) {
            yield $word;
        }
    }
}

/**
 * Generate a sorted list of words and their frequency
 *
 * @param string $filename
 * @return Generator
 */
function countAndSort($filename)
{
    $freqs = [];
    $i     = 1;

    foreach (nonStopWords($filename) as $word) {
        if (array_key_exists($word, $freqs)) {
            $freqs[$word] += 1;
        } else {
            $freqs[$word] = 1;
        }

        if ($i % 5000 === 0) {
            arsort($freqs);
            yield $freqs;
        }

        $i += 1;
    }

    yield $freqs;
}

foreach (countAndSort($_SERVER['argv'][1]) as $wordFreqs) {
    print "-----------------------------" . PHP_EOL;

    foreach (array_slice($wordFreqs, 0, 25) as $word => $freqs) {
        print_r($word . ' - ' . $freqs . PHP_EOL);
    }
}