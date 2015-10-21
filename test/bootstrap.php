<?php

$output = '';
$exitCode = '';
exec(__DIR__ . '/../bin/generate-code', $output, $exitCode);

if ($exitCode) {
    echo implode(PHP_EOL, $output);
    echo PHP_EOL;
    echo PHP_EOL;
    echo 'Tests aborted, code-generation failed.' . PHP_EOL;
    exit($exitCode);
}
