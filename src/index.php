<?php

namespace Tiknil\Skipper;

$version = \Composer\InstalledVersions::getPrettyVersion('tiknil/skipper');

$app = new CliApplication('Tiknil Skipper ⛵️', $version ?? 'dev');

$app->registerCommands();

$app->setDefaultCommand('man');

$app->run();
