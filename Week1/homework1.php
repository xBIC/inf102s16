<?PHP

/**
 * Parses a file into words, and returns the top used (or bottom used) N number of words
 *
 * Class Frequency
 */
class Frequency
{
    /**
     * Smallest size word to allow
     */
    const MIN_WORD_LENGTH = 2;

    /**
     * How many results to display at the end
     */
    const NUM_TOP_RESULTS = 25;

    /**
     * Name of the stopwords file
     */
    const STOP_WORD_FILE = "stop_words.txt";

    /**
     * Contains a key value pair of [ word : count ]
     *
     * @var array
     */
    protected $frequencyArray = [];

    /**
     * Array of all the stop words to ignore
     *
     * @var array
     */
    protected $stopWords = [];

    /**
     * The standard run function to execute the script
     *
     * @throws Exception
     */
    public function run()
    {
        try {
            $inputFilePath = $this->getInputFilePath();
            $stopWordsFile = $this->formatStopWordsPath();

            $file = fopen($inputFilePath, "r");

            if (empty($file)) {
                throw new Exception('File was not opened properly');
            }

            // Having a stopwords file is not a requirement to run the script
            // Although the expected response would not be returned, the input file would still be parsed
            if (file_exists($stopWordsFile)) {
                $stopWordsString = file_get_contents($stopWordsFile);
            }

            if (!empty($stopWordsString)) {
                $this->stopWords = explode(',', $stopWordsString);
            }

            // Loop through the file line by line until the EOF is reached
            while (!feof($file)) {
                $line = fgets($file);

                if (empty($line)) {
                    continue;
                } else {
                    $line = strtolower($line);
                }

                $this->parseLine($line);
            }

            $this->sortFrequencyArray();

            $this->printTopResults();
        } catch (Exception $e) {
            $this->handleException($e);
        }
    }

    /**
     * Parse a line from the input file and add the words to the frequencyArray
     *
     * @param string $line
     */
    private function parseLine($line)
    {
        // Split on numbers, non-words characters, and underscores
        $wordArray = preg_split("/[0-9\W_]/", $line);

        if (empty($wordArray)) {
            return;
        }

        foreach ($wordArray as $word) {
            // If the word length is less than the minimum, skip to next word
            if (strlen($word) < self::MIN_WORD_LENGTH) {
                continue;
            }

            // If the word is a stop word, skip to next word
            if ($this->isStopWord($word)) {
                continue;
            }

            if (array_key_exists($word, $this->frequencyArray)) {
                $this->frequencyArray[$word] += 1;
            } else {
                $this->frequencyArray[$word] = 1;
            }
        }
    }

    /**
     * Check if a word is in the stopWords array
     *
     * @param $word
     * @return bool
     */
    private function isStopWord($word)
    {
        if (empty($this->stopWords)) {
            return false;
        }

        return in_array($word, $this->stopWords);
    }

    /**
     * Sort the frequencyArray in specified order
     *
     * @param string $order
     */
    private function sortFrequencyArray($order = "desc")
    {
        if (empty($this->frequencyArray)) {
            return;
        }

        if ($order == "desc") {
            arsort($this->frequencyArray);
        } elseif ($order == "asc") {
            asort($this->frequencyArray);
        }
    }


    /**
     * Retrieves the input file path from the command line (first argument)
     *
     * @return mixed
     * @throws Exception
     */
    private function getInputFilePath()
    {
        if (empty($_SERVER['argv'][1])) {
            throw new Exception('Must specify input file to run script');
        }

        $inputFile = $_SERVER['argv'][1];

        if (!is_string($inputFile)) {
            throw new Exception("Input file path must be a string, not " . gettype($inputFile));
        }

        if (strpos($inputFile, '../') !== 0 && basename(getcwd()) === basename(__DIR__)) {
            $inputFile = "../{$inputFile}";
        }

        if (!file_exists($inputFile)) {
            throw new Exception("Input file does not exist: " . getcwd() . "/{$inputFile}");
        }

        return $inputFile;
    }
    
    /**
     * Format the stop words path depending on which directory the script was run from
     *
     * @return string
     * @throws Exception
     */
    private function formatStopWordsPath()
    {
        if (empty(self::STOP_WORD_FILE)) {
            throw new Exception('The stopwords file path has not been set in the script');
        }

        if (basename(getcwd()) === basename(__DIR__)) {
            return '../' . self::STOP_WORD_FILE;
        }

        return self::STOP_WORD_FILE;
    }

    private function printTopResults()
    {
        if (empty($this->frequencyArray)) {
            throw new Exception('No words found to output');
        }

        $totalOutput = 0;

        foreach ($this->frequencyArray as $word => $frequency) {
            if ($totalOutput >= self::NUM_TOP_RESULTS) {
                break;
            }

            print_r($word . "  -  " . $frequency . PHP_EOL);

            $totalOutput++;
        }
    }

    /**
     * Handle any exception that is thrown during the running of the script and output a formatted error response
     *
     * @param Exception $e
     */
    private function handleException(Exception $e)
    {
        print_r(PHP_EOL . 'The script has exited early with an error on line #' . $e->getLine() . PHP_EOL . PHP_EOL);
        print_r('Error: ' . $e->getMessage() . PHP_EOL . PHP_EOL);
        print_r('Stack Trace:' . PHP_EOL);
        print_r($e->getTraceAsString() . PHP_EOL . PHP_EOL);
        print_r('*** Please see the readme.md file for information on how to run this script ***' . PHP_EOL . PHP_EOL);
    }
}

$frequency = new Frequency();
$frequency->run();