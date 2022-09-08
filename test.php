<?php
require_once 'ConsoleTaskHandler.php';
$args = $GLOBALS['argv'];
$separator = $args[1];
$command = $args[2];

$res = new ConsoleTaskHandler($separator, $command);

$res->getResult();