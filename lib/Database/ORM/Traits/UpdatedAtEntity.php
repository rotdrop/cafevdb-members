<?php
/**
 * Member's data base connector for CAFEVDB orchetra management app.
 *
 * @author Claus-Justus Heine <himself@claus-justus-heine.de>
 * @copyright Copyright (c) 2022, 2023 Claus-Justus Heine
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

use Gedmo\Mapping\Annotation as Gedmo;

/** Helper for Gedmo time-stampable entities. */
trait UpdatedAtEntity
{
  use \OCA\CAFeVDBMembers\Traits\DateTimeTrait;

  /**
   * @var \DateTimeImmutable
   * @ORM\Column(type="datetime_immutable", nullable=true)
   */
  protected $updated;

  /**
   * Sets updated.
   *
   * @param string|int|\DateTimeInterface $updated
   *
   * @return self
   */
  public function setUpdated($updated)
  {
    $this->updated = self::convertToDateTime($updated);
    return $this;
  }

  /**
   * Returns updated.
   *
   * @return \DateTimeImmutable
   */
  public function getUpdated():?\DateTimeInterface
  {
    return $this->updated;
  }
}
