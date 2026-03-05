<?php
/**
 * Member's data base connector for CAFEVDB orchetra management app.
 *
 * @author Claus-Justus Heine <himself@claus-justus-heine.de>
 * @copyright Copyright (c) 2022-2024, 2026 Claus-Justus Heine
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

namespace OCA\CAFeVDBMembers\Tests\Unit\Database\ORM;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes;
use PHPUnit\Framework\MockObject\MockObject;

use OCP\IConfig;
use Psr\Container\ContainerInterface;

use OCA\CAFEVDB\Database\Doctrine\ORM\Entities\MusicianRowAccessToken;
use OCA\CAFeVDBMembers\Database\DBAL\Logging\CloudLogger;
use OCA\CAFeVDBMembers\Database\ORM\EntityManager;
use OCA\CAFeVDBMembers\Service\AuthenticationService;
use OCA\CAFeVDBMembers\Settings\ConfigConstants;
use OCA\CAFeVDBMembers\Tests\MockProvider;
use OCA\RotDrop\Tests\DatabaseProvider;
use OCA\RotDrop\Tests\DeprecationException;
use OCA\RotDrop\Tests\EnumDatabasePurpose;

/** Test aspects of the entity manager */
#[Attributes\CoversClass(EntityManager::class)]
#[Attributes\UsesClass(\OCA\CAFeVDBMembers\AppInfo\Application::class)]
#[Attributes\UsesClass(\OCA\CAFeVDBMembers\Database\DBAL\Logging\CloudLogger::class)]
#[Attributes\UsesClass(\OCA\CAFeVDBMembers\Database\ORM\Listeners\GedmoTranslatableListener::class)]
#[Attributes\UsesClass(\OCA\CAFeVDBMembers\Toolkit\AppInfo\AbstractApplication::class)]
class EntityManagerTest extends TestCase
{
  private MockProvider $mockProvider;

  private EntityManager $entityManager;

  private AuthenticationService $authenticationService;

  private IConfig $cloudConfig;

  private ContainerInterface $appContainer;

  private string $rowAccessToken = '';

  /** {@inheritdoc} */
  public function setup(): void
  {
    error_reporting(E_ALL);
    DeprecationException::throwOnDeprecations(exclude: '/OCP\\\\IConfig\\:\\:(get|set|delete)AppValue/');

    $this->mockProvider = $this->mocProvider ?? MockProvider::create($this);

    $this->authenticationService = $this->getMockBuilder(AuthenticationService::class)
      ->disableOriginalConstructor()
      ->getMock();

    $appName = $this->mockProvider->appName;

    $this->appContainer = $this->mockProvider->getAppContainer();

    // install config values
    /** @var DatabaseProvider $databaseProvider */
    $databaseProvider = \OCP\Server::get(DatabaseProvider::class);
    $databaseName = $databaseProvider->databaseName(EnumDatabasePurpose::CLOUD_CONNECTOR);

    $this->cloudConfig = $this->mockProvider->getCloudConfig();
    $this->cloudConfig->setAppValue(
      $appName,
      ConfigConstants::USER_VIEWS_DATABASE_KEY,
      $databaseName,
    );
    $databaseConfig = $databaseProvider->getDatabaseConfig();
    $this->cloudConfig->setSystemValue('dbuser', DatabaseProvider::CLOUD_DB_USER);
    $this->cloudConfig->setSystemValue('dbpassword', $databaseConfig->databasePassword);
    $this->cloudConfig->setSystemValue('dbhost', $databaseConfig->databaseServer);
  }

  /**
   * @param string $token
   *
   * @return void
   */
  private function generateEntityManager(string $token): void
  {
    $this->rowAccessToken = $token;
    $this->authenticationService
      ->expects($this->once())
      ->method('getRowAccessToken')
      ->willReturnCallback(fn() => $this->rowAccessToken);

    $this->entityManager = new EntityManager(
      appContainer: $this->appContainer,
      appName: $this->mockProvider->appName,
      authenticationService: $this->authenticationService,
      cloudConfig: $this->cloudConfig,
      l: $this->mockProvider->getL10N(),
      logger: $this->mockProvider->getLoggerInterface(),
      session: $this->mockProvider->getSession(),
      sqlLogger: $this->appContainer->get(CloudLogger::class),
      userId: MockProvider::CLOUD_USER_UID,
    );
  }

  /** @return void */
  public function testSetup(): void
  {
    $rowAccessTokens = $this->mockProvider->getRowAccessTokens();
    $this->assertNotNull($rowAccessTokens[MockProvider::CLOUD_USER_UID]['access_token_hash'] ?? null);
    $this->assertEquals(
      MusicianRowAccessToken::HASH_LENGTH / 4, // hexified binary, one nibble per char.
      strlen($rowAccessTokens[MockProvider::CLOUD_USER_UID]['access_token_hash']),
    );
    $rowAccessToken = $rowAccessTokens[MockProvider::CLOUD_USER_UID]['access_token_hash'];
    $this->generateEntityManager($rowAccessToken);
    $this->assertTrue($this->entityManager->connected());
  }

  /** @return void */
  public function tearDown(): void
  {
    restore_error_handler();
  }
}
