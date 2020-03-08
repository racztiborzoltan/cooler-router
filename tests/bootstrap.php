<?php
declare(strict_types=1);

$composer = include __DIR__ . '/../vendor/autoload.php';

$composer->addPsr4('Test\\', __DIR__ . '/classes');
