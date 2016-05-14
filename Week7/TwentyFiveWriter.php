<?php

/**
 * Database class to use SQLite3
 */
class DB extends SQLite3
{
    public function __construct($filename)
    {
        $this->open($filename);
    }
}

/**
 * Create tables in DB
 *
 * @param DB $connection
 */
function createDBSchema(DB &$connection)
{
    // Below was corrected for proper naming conventions and proper primary key usage
    // This really should have `words`.`document_id` FK to `documents`.`document_id`
    // as well as `characters`.`word_id` FK to `words`.`word_id`
    $ret = $connection->exec("CREATE TABLE `documents` (`document_id` INTEGER PRIMARY KEY AUTOINCREMENT, `document_name`)");
    $connection->exec("CREATE TABLE `words` (`word_id` INTEGER PRIMARY KEY AUTOINCREMENT, `document_id`, `word_value`)");
    $connection->exec("CREATE TABLE `characters` (`characters_id`, `word_id`, `character_value`)");
}

/**
 * Load the file passed file into the database
 *
 * @param string $pathToFile
 * @param DB $connection
 */
function loadFileIntoDatabase($pathToFile, DB &$connection)
{

    // No clue why the code was formatted this way.
    // It would have been simpler (and better) to separate this into it's own function.
    $_extractWords = function ($pathToFile) {
        $f        = file_get_contents($pathToFile);
        $wordList = explode(' ', strtolower(preg_replace("/[\W_]+/", ' ', $f)));

        $f         = file_get_contents('../stop_words.txt');
        $stopWords = array_merge(explode(',', $f), range('a', 'z'));

        return array_diff($wordList, $stopWords);
    };

    $words = $_extractWords($pathToFile);

    $connection->exec("INSERT INTO `documents` (`document_name`) VALUES ('{$pathToFile}')");
    $documentId = $connection->lastInsertRowID();

    foreach ($words as $word) {
        $connection->exec("INSERT INTO `words` (`document_id`, `word_value`) VALUES ({$documentId}, '{$word}')");
        $wordId = $connection->lastInsertRowID();

        $charId = 0;
        foreach (str_split($word) as $char) {
            $connection->exec("INSERT INTO `characters` (`characters_id`, `word_id`, `character_value`) VALUES ({$charId}, {$wordId}, '{$char}')");
            $charId += 1;
        }
    }
}

if (!file_exists('tf.db')) {
    $connection = new DB('tf.db');
    createDBSchema($connection);
    loadFileIntoDatabase($_SERVER['argv'][1], $connection);
} else {
    $connection = new DB('tf.db');
}

$words = $connection->query("SELECT `word_value`, COUNT(1) as `frequency` FROM `words` GROUP BY `words`.`word_value` ORDER BY `frequency` DESC");

for ($i = 0; $i < 25; $i++) {
    $row = $words->fetchArray(SQLITE3_ASSOC);

    if ($row === false) {
        break;
    }

    print_r($row['word_value'] . ' - ' . $row['frequency'] . PHP_EOL);
}