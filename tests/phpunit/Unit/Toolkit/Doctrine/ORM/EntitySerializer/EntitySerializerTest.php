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

namespace OCA\CAFeVDBMembers\Tests\Unit\Toolkit\Doctrine\ORM\EntitySerializer;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes;
use PHPUnit\Framework\MockObject\MockObject;

use OCA\CAFeVDBMembers\Database\ORM\Entities;
use OCA\CAFEVDB\Database\Doctrine\ORM\Entities\MusicianRowAccessToken;
use OCA\CAFeVDBMembers\Tests\MockProvider;
use OCA\CAFeVDBMembers\Toolkit\Doctrine\ORM\EntitySerializer\EntitySerializer;
use OCA\RotDrop\Tests\DeprecationException;

/** Test aspects of the EntitySerializer. */
#[Attributes\CoversClass(EntitySerializer::class)]
#[Attributes\UsesClass(\OCA\CAFeVDBMembers\AppInfo\Application::class)]
#[Attributes\UsesClass(\OCA\CAFeVDBMembers\Database\DBAL\Logging\CloudLogger::class)]
#[Attributes\UsesClass(\OCA\CAFeVDBMembers\Database\DBAL\Types\AbstractEnumType::class)]
#[Attributes\UsesClass(\OCA\CAFeVDBMembers\Database\DBAL\Types\UuidType::class)]
#[Attributes\UsesClass(\OCA\CAFeVDBMembers\Database\ORM\Entities\Musician::class)]
#[Attributes\UsesClass(\OCA\CAFeVDBMembers\Database\ORM\EntityManager::class)]
#[Attributes\UsesClass(\OCA\CAFeVDBMembers\Database\ORM\Listeners\Encryption::class)]
#[Attributes\UsesClass(\OCA\CAFeVDBMembers\Database\ORM\Listeners\GedmoTranslatableListener::class)]
#[Attributes\UsesClass(\OCA\CAFeVDBMembers\Database\ORM\Repositories\EntityRepository::class)]
#[Attributes\UsesClass(\OCA\CAFeVDBMembers\Toolkit\AppInfo\AbstractApplication::class)]
#[Attributes\UsesClass(\OCA\CAFeVDBMembers\Toolkit\Doctrine\ORM\AbstractEntityManager::class)]
#[Attributes\UsesClass(\OCA\CAFeVDBMembers\Toolkit\Doctrine\ORM\EntitySerializer\EntityReference::class)]
#[Attributes\UsesClass(\OCA\CAFeVDBMembers\Toolkit\Doctrine\ORM\EntitySerializer\EntityReferenceCollection::class)]
#[Attributes\UsesTrait(\OCA\CAFeVDBMembers\Database\ORM\Traits\ArrayTrait::class)]
#[Attributes\UsesTrait(\OCA\CAFeVDBMembers\Database\ORM\Traits\CreatedAt::class)]
#[Attributes\UsesTrait(\OCA\CAFeVDBMembers\Database\ORM\Traits\UpdatedAt::class)]
#[Attributes\UsesTrait(\OCA\CAFeVDBMembers\Database\ORM\Traits\UuidTrait::class)]
#[Attributes\UsesTrait(\OCA\CAFeVDBMembers\Toolkit\Doctrine\ORM\FindLikeTrait::class)]
class EntitySerializerTest extends TestCase
{
  use \OCA\CAFeVDBMembers\Tests\Unit\Database\ORM\GenerateEntityManagerTrait;

  private EntitySerializer $entitySerializer;

  /** {@inheritdoc} */
  public function setup(): void
  {
    error_reporting(E_ALL);
    DeprecationException::throwOnDeprecations(exclude: '/OCP\\\\IConfig\\:\\:(get|set|delete)AppValue/');

    $this->mockProvider = $this->mockProvider ?? MockProvider::create($this);

    $rowAccessTokens = $this->mockProvider->getRowAccessTokens();
    $this->assertNotNull($rowAccessTokens[MockProvider::CLOUD_USER_UID]['access_token_hash'] ?? null);
    $this->assertEquals(
      MusicianRowAccessToken::HASH_LENGTH / 4, // hexified binary, one nibble per char.
      strlen($rowAccessTokens[MockProvider::CLOUD_USER_UID]['access_token_hash']),
    );
    $rowAccessToken = $rowAccessTokens[MockProvider::CLOUD_USER_UID]['access_token_hash'];

    $this->generateEntityManager($rowAccessToken);

    $this->entitySerializer = new EntitySerializer(
      entityManager: $this->entityManager,
      l: $this->mockProvider->getL10N(),
      logger: $this->mockProvider->getLoggerInterface(),
    );
  }

  /** @return void */
  public function testAddEntity(): void
  {
    $musicians = $this->entityManager->getRepository(Entities\Musician::class)->findAll();
    $this->assertEquals(1, count($musicians));
    $this->entitySerializer->addEntity($musicians[0]);
  }

  /** @return void */
  public function tearDown(): void
  {
    restore_error_handler();
  }
}
