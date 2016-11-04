<?php

//Grab the composer Autoloader
use Gnf\Tests\db\BaseTestTarget;

$autoloader = require __DIR__ . '/../vendor/autoload.php';

$autoloader->add('Gnf\Tests', __DIR__);
$autoloader->add('Gnf', __DIR__ . '/../lib/');

$base = new BaseTestTarget;
