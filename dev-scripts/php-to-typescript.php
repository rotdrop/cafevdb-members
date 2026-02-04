#!/usr/bin/env php
<?php
/**
 * Member's data base connector for CAFEVDB orchetra management app.
 *
 * @author Claus-Justus Heine <himself@claus-justus-heine.de>
 * @copyright Copyright (c) 2026 Claus-Justus Heine
 * @license AGPL-3.0-or-later
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */

// phpcs:disable PSR1.Files.SideEffects

ini_set('display_errors', 'stderr');

try {
  require_once(__DIR__ . '/lib/scripts/console-setup.php');
  require_once(__DIR__ . '/../vendor/autoload.php');
  require_once(__DIR__ . '/../vendor-bin/typescript-transformer/vendor/autoload.php');
} catch (\Throwable $t) {
  echo 'composer_not_set_up' . PHP_EOL;
  return 1;
}

use OCA\CAFeVDBMembers\Toolkit\Console\ConsoleOutput;
use OCA\RotDrop\DevScripts\PhpToTypeScript;

use Spatie\TypeScriptTransformer\Transformers;

// store output of different transformers in different files

$outputPrefix = __DIR__ . '/../build/ts-types/php-';
$outputSuffix = '.d.ts';
$sourcePrefix = __DIR__ . '/../';

$outputFiles = [
  'types' => [
    'transformers' => [
      Transformers\EnumTransformer::class,
      PhpToTypeScript\ClassConstantsTransformer::class,
      Transformers\DtoTransformer::class,
    ],
    'paths' => [
      'lib',
    ],
  ],
];

$excludes = [
  'lib/Database/ORM/Proxies',
];

$scopedNamespaces = [];


$phpToTypeScript = new PhpToTypeScript\PhpToTypeScript(
  devScriptsFolder: __DIR__,
  configInfo: $outputFiles,
  excludes: $excludes,
  scopedNamespaces: $scopedNamespaces,
);

$phpToTypeScript->run(
  input: new \Symfony\Component\Console\Input\ArgvInput,
  output: \OCP\Server::get(ConsoleOutput::class),
);
