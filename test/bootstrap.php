<?php
namespace tests;

require_once dirname(__DIR__) . '/vendor/autoload.php';
require_once __DIR__ . '/common/widgets/Menu.php';
$loader = new \Composer\Autoload\ClassLoader();
$loader->add('test', dirname(__DIR__));
$loader->register();
