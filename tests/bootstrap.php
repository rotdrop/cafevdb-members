<?php

require_once __DIR__ . '/../../../tests/bootstrap.php';

require_once __DIR__ . "/../vendor/autoload.php";
$installedVersions = __DIR__ . "/../vendor/composer/InstalledVersions.php";
if (file_exists($installedVersions)) {
  require_once $installedVersions;
}
