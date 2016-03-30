<?PHP

class Frequency
{
    const MIN_WORD_LENGTH = 2;
    const NUM_TOP_RESULTS = 25;
    const STOP_WORD_FILE = "stop_words.txt";

    /**
     * Contains a key value pair of [ word : count ]
     *
     * @var array
     */
    protected $frequencyArray = [];

    protected $stopWords = [];

    protected $inputFile = "";

    protected $stopWordsFile = "";

    public function __construct()
    {
        $this->inputFile = $inputFile = __DIR__ . "/../" . $this->getInputFile();
        $this->stopWordsFile = $stopWordsFile = __DIR__ . "/../" . self::STOP_WORD_FILE;
    }

    public function run()
    {
        if (!file_exists($this->inputFile)) {
            throw new Exception("Input file does not exist: {$this->inputFile}");
        }

        $file = fopen($this->inputFile, "r");

        if (empty($file)) {
            throw new Exception('File was not opened properly');
        }

        if (file_exists($this->stopWordsFile)) {
            $stopWordsString = file_get_contents($this->stopWordsFile);
        }

        if (!empty($stopWordsString)) {
            $this->stopWords = explode(',', $stopWordsString);
        }

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
    }

    /**
     * Parse a line from the input file and add the words to the frequencyArray
     *
     * @param string $line
     */
    private function parseLine($line)
    {
        // Includes numeric characters
        //$wordArray = preg_split("/\W+|_/", $line);

        // Does not account for hyphenated words
        // Accounts for apostrophes in words (do not want as per teacher's code)
        //$wordArray = preg_split("/[^a-z']/", $line);

        // Does account for hyphenated words (do not want as per teacher's code)
        // Accounts for apostrophes in words (do not want as per teacher's code)
        //$wordArray = preg_split("/[^a-z']*[^-a-z']/", $line); // Accounts for hyphenated words


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
     * Retrieves the input file name from the command line (first argument)
     *
     * @return mixed
     * @throws Exception
     */
    private function getInputFile()
    {
        if (empty($_SERVER['argv'][1])) {
            throw new Exception('Must specify input file to run script');
        }

        return $_SERVER['argv'][1];
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
}

$frequency = new Frequency();
$frequency->run();