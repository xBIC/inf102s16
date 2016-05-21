<?php

class MyThread extends Thread
{
    /**
     * @var callable
     */
    protected $process;

    /**
     * ActiveWFObject constructor
     * Starts a new thread and instantiates needed variables
     */
    public function __construct(callable $process)
    {
        $this->process = $process;
    }

    public function run()
    {
        call_user_func($this->process);
    }
}

$wordSpace = new SplQueue();
$freqSpace = new SplQueue();

$stopwords = explode(',', file_get_contents('../stop_words.txt'));

function processWords()
{
    // Used to access global variable above
    global $wordSpace;
    global $freqSpace;
    global $stopWords;

    $wordFreqs = [];

    while (true) {
        try {
            $word = $wordSpace->shift();
        } catch (Exception $e) {
            break;
        }

        if (!in_array($word, $stopWords)) {
            if (array_key_exists($word, $wordFreqs)) {
                $wordFreqs[$word] += 1;
            } else {
                $wordFreqs[$word] = 1;
            }
        }
    }

    $freqSpace->push($wordFreqs);
}

preg_match_all("/[a-z]{2,}/", strtolower(file_get_contents($_SERVER['argv'][1])), $words);

foreach ($words as $word) {
    $wordSpace->push($word);
}

$workers = [];

foreach (range(0, 4) as $i) {
    $workers[] = new MyThread('processWords');
}

foreach ($workers as $worker) {
    $worker->start();
}

foreach ($workers as $t) {
    $t->join();
}

$wordFreqs = [];

while (!$freqSpace->isEmpty()) {
    $freqs = $freqSpace->shift();

    foreach ($freqs as $word => $freq) {
        if (in_array($word, $wordFreqs)) {
            $count = $freqs[$word] + $wordFreqs[$word];
        } else {
            $count = $freqs[$word];
        }

        $wordFreqs[$word] = $count;
    }
}

arsort($wordFreqs);

foreach (array_slice($wordFreqs, 0, 25) as $word => $value) {
    echo $word . ' - ' . $value . PHP_EOL;
}