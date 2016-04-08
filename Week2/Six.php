<?php

preg_match_all("/[a-z]{2,}/", strtolower(file_get_contents($_SERVER['argv'][1])), $wo);
$wo = array_count_values(array_diff($wo[0], explode(',', file_get_contents('../stop_words.txt'))));
arsort($wo);
foreach (array_splice($wo, 0, 25) as $k => $w) echo $k . ' - ' . $w . PHP_EOL;