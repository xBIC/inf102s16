<?php

/**
 * Load plugins
 */
function loadPlugins()
{
    $config = parse_ini_file('config.ini');

    $wordsPlugin       = $config['plugins']['words'];
    $frequenciesPlugin = $config['plugins']['frequencies'];
    $printPlugin       = $config['plugins']['print'];

    require_once($wordsPlugin);
    require_once($frequenciesPlugin);
    require_once($printPlugin);
}

loadPlugins();
$wordFreqs = top25(extractWords($_SERVER['argv'][1]));

printWords($wordFreqs);