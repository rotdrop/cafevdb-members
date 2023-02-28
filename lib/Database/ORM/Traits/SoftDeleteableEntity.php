<?php
/**
 * Member's data base connector for CAFEVDB orchetra management app.
 *
 * @author Claus-Justus Heine <himself@claus-justus-heine.de>
 * @copyright Copyright (c) 2022 Claus-Justus Heine
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

namespace OCA\CAFeVDBMembers\Database\ORM\Traits;

use Doctrine\ORM\Mapping as ORM;

use OCA\CAFeVDBMembers\Database\ORM as CAFEVDB;

/** Helper for Gedmo soft-deleteable entities. */
trait SoftDeleteableEntity
{
  use \OCA\CAFeVDBMembers\Toolkit\Traits\DateTimeTrait;

  /**
   * @ORM\Column(type="datetime_immutable", nullable=true)
   *
   * @var DateTimeImmutable|null
   */
  protected $deleted;

  /**
   * Set or clear the deleted at timestamp.
   *
   * @param string|int|\DateTimeInterface $deleted
   *
   * @return self
   */
  public function setDeleted($deleted = null)
  {
    $this->deleted = self::convertToDateTime($deleted);
    return $this;
  }

  /**
   * Get the deleted at timestamp value. Will return null if
   * the entity has not been soft deleted.
   *
   * @return \DateTimeImmutable|null
   */
  public function getDeleted()
  {
    return $this->deleted;
  }

  /**
   * Check if the entity has been soft deleted.
   *
   * @return bool
   */
  public function isDeleted()
  {
    return null !== $this->deleted;
  }
}
