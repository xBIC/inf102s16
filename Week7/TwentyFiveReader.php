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

if (!file_exists('tf.db')) {
    print_r("DATABASE DOES NOT EXIST YET!" . PHP_EOL);
    print_r("RUN: 'php TwentyFiveWriter.php ../pride-and-prejudice.txt'" . PHP_EOL);
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