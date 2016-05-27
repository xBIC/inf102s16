<?php

$f     = file_get_contents('../stop_words.txt');
$stops = array_merge(explode(',', $f), range('a', 'z'));
$data  = [];

function errorState()
{
    return [
        'Something wrong',
        ['get', 'default', null]
    ];
}

function defaultGetHandler($args)
{
    $rep = 'What would you like to do?';
    $rep .= PHP_EOL . '1 - Quit' . PHP_EOL . '2 - Upload file';

    $links     = new stdClass();
    $links->n1 = ['post', 'execution', null];
    $links->n2 = ['get', 'file_form', null];

    return [$rep, $links];
}

function quitHandler($args)
{
    echo 'Goodbye cruel world...' . PHP_EOL;
    exit;
}

function uploadGetHandler($args)
{
    return [
        'Name of file to upload?',
        ['post', 'file']
    ];
}

function uploadPostHandler($args)
{
    global $data;
    global $stops;

    $createData = function ($filename) use (&$data, $stops) {
        if (in_array($filename, $data)) {
            return;
        }

        $wordFreqs = [];

        preg_match_all("/[a-z]+/", strtolower(file_get_contents($filename)), $words);

        foreach ($words[0] as $word) {
            if (strlen($word) > 0 && !in_array($word, $stops)) {
                (empty($wordFreqs[$word]) ? $wordFreqs[$word] = 1 : $wordFreqs[$word] += 1);
            }
        }

        $wordFreqs1 = [];

        foreach ($wordFreqs as $key => $freq) {
            $wordFreqs1[] = [$key, $freq];
        }

        usort($wordFreqs1, function ($x, $y) {
            return $x[1] < $y[1];
        });

        $data[$filename] = $wordFreqs1;
    };

    if ($args == null) {
        return errorState();
    }

    $filename = $args[0];

    try {
        $createData($filename);
    } catch (Exception $e) {
        return errorState();
    }

    return wordGetHandler([$filename, 0]);
}

function wordGetHandler($args)
{
    global $data;

    $getWord = function ($filename, $wordIndex) use ($data) {
        if ($wordIndex < count($data[$filename])) {
            return $data[$filename][$wordIndex];
        } else {
            return ['no more words', 0];
        }
    };

    $filename  = $args[0];
    $wordIndex = $args[1];
    $wordInfo  = $getWord($filename, $wordIndex);

    $rep = PHP_EOL . sprintf('#%u: %s - %u', $wordIndex + 1, $wordInfo[0], $wordInfo[1]);
    $rep .= PHP_EOL . PHP_EOL . 'What would you like to do next?';
    $rep .= PHP_EOL . '1 - Quit' . PHP_EOL . '2 - Upload file';
    $rep .= PHP_EOL . '3 - See next most-frequently occurring word';

    $links     = new stdClass();
    $links->n1 = ['post', 'execution', null];
    $links->n2 = ['get', 'file_form', null];
    $links->n3 = ['get', 'word', [$filename, $wordIndex + 1]];

    if ($wordIndex > 0) {
        $rep .= PHP_EOL . '4 - See previous most-frequently occurring word';
        $links->n4 = ['get', 'word', [$filename, $wordIndex - 1]];
    }

    return [$rep, $links];
}

$handlers = [
    'post_execution' => 'quitHandler',
    'get_default'    => 'defaultGetHandler',
    'get_file_form'  => 'uploadGetHandler',
    'post_file'      => 'uploadPostHandler',
    'get_word'       => 'wordGetHandler'
];

function handleRequest($verb, $uri, $args)
{
    global $handlers;

    $handlerKey = function ($verb, $uri) {
        return $verb . '_' . $uri;
    };

    if (array_key_exists($handlerKey($verb, $uri), $handlers)) {
        return call_user_func($handlers[$handlerKey($verb, $uri)], $args);
    } else {
        return call_user_func($handlers[$handlerKey('get', 'default')], $args);
    }
}

function renderAndGetInput($stateRepresentation, $links)
{
    global $links;

    echo $stateRepresentation . PHP_EOL;

    if (is_object($links)) {
        $input = 'n' . trim(readline());

        if (property_exists($links, $input)) {
            return $links->$input;
        } else {
            return ['get', 'default', null];
        }
    } elseif (is_array($links)) {
        if ('post' == $links[0]) {
            $input   = trim(readline());
            $links[] = [$input];
            return $links;
        } else {
            return $links;
        }
    } else {
        return ['get', 'default', null];
    }
}

$request = ['get', 'default', null];

while (true) {
    $hr                  = handleRequest($request[0], $request[1], $request[2]);
    $stateRepresentation = $hr[0];
    $links               = $hr[1];

    $request = renderAndGetInput($stateRepresentation, $links);
}
