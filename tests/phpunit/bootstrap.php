<?php
/**
 * Member's data base connector for CAFEVDB orchetra management app.
 *
 * @author Claus-Justus Heine <himself@claus-justus-heine.de>
 * @copyright Copyright (c) 2022-2026 Claus-Justus Heine
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

namespace OCA\CAFeVDBMembers\Tests;

date_default_timezone_set('UTC');

putenv('TEST_DONT_LOAD_APPS=1');
require_once __DIR__ . '/../../../../tests/bootstrap.php';

define('PHPUNIT_NC_APP_NAME', \OCA\CAFeVDBMembers\AppInfo\Application::getAppName());

require_once __DIR__ . "/../../vendor-bin/phpunit/vendor/autoload.php";
require_once __DIR__ . "/../../vendor/autoload.php";

$wantedApps = [
  'files',
  'files_sharing',
  // \OCA\CAFeVDBMembers\AppInfo\Application::getOrchestraAppName(),
  \PHPUNIT_NC_APP_NAME,
];

$appManager = \OCP\Server::get(\OCP\App\IAppManager::class);
foreach ($wantedApps as $app) {
  $appManager->loadApp($app);
}

// Perhaps: use a real database, populated with some rows of data.
//
// Reasoning: the cafevdbmembers app has - up to the project registration --
// only read access to the database. The only table which can ever rightfully
// modified is the ProjectRegistration table.

define('PHPUNIT_APPDIR', realpath(\OCA\CAFEVDB\Toolkit\Service\AppInfoService::getAppFolderPath()));
define('PHPUNIT_ARTIFACTS', PHPUNIT_APPDIR . '/build/artifacts/tests/phpunit');

\OCP\Server::get(\OCA\RotDrop\Toolkit\Service\ExecutableFinder::class);
$databaseProvider = \OCP\Server::get(\OCA\RotDrop\Tests\DatabaseProvider::class);

echo 'Starting database server ...';
$databaseProvider->startServer();
echo ' ... OK' . PHP_EOL;
echo 'Loading test databases ...';
$databaseProvider->loadSql(
  \OCA\RotDrop\Tests\EnumDatabasePurpose::APP,
  __DIR__ . '/data/app.sql',
);
$databaseProvider->loadSql(
  \OCA\RotDrop\Tests\EnumDatabasePurpose::CLOUD_CONNECTOR,
  __DIR__ . '/data/cloud_connector.sql',
);
echo ' ... OK' . PHP_EOL;


// stop and cleanup potentially running db-servers
register_shutdown_function([$databaseProvider, 'stopServer']);

error_reporting(E_ALL);
