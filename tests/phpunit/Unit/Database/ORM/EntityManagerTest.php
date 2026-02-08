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

use OCA\CAFeVDBMembers\Database\ORM\EntityManager;
use OCA\CAFeVDBMembers\Tests\MockProvider;

/** Test aspects of the entity manager */
#[Attributes\CoversClass(EntityManager::class)]
#[Attributes\UsesClass(\OCA\CAFeVDBMembers\Toolkit\AppInfo\AbstractApplication::class)]
class EntityManagerTest extends TestCase
{
  private MockProvider $mockProvider;

  /** {@inheritdoc} */
  public function setup(): void
  {
    $this->mockProvider = $this->mocProvider ?? MockProvider::create($this);
  }

  /** @return void */
  public function testSetup(): void
  {
    $this->expectNotToPerformAssertions();
  }
}
