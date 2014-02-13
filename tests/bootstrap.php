<?php

/*
 * This file is part of Methodology.
 * 
 * (c) Tomasz Zduńczyk <tomasz@zdunczyk.org>
 * 
 * For the full copyright and license information, please view the LICENSE 
 * file that was distributed with this source code.
 */

error_reporting(E_ALL);

$loader = require_once __DIR__ . '/../vendor/autoload.php';
$loader->add('Methodology\\', __DIR__);