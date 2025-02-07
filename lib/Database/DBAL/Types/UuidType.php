<?php
/**
 * Member's data base connector for CAFEVDB orchetra management app.
 *
 * @copyright Copyright (c) 2022 Claus-Justus Heine <himself@claus-justus-heine.de>
 *
 * @author Claus-Justus Heine <himself@claus-justus-heine.de>
 *
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
 *
 */

namespace OCA\CAFeVDBMembers\Database\DBAL\Types;

use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\Doctrine\UuidBinaryType;
use Doctrine\DBAL\Platforms\AbstractPlatform;

/**
 * Like UuidBinaryType, but implement a more allowing
 * convertToPHPValue() which accepts also string inputs.
 */
class UuidType extends UuidBinaryType
{
  /** {@inheritdoc} */
  public function convertToPHPValue($value, AbstractPlatform $platform)
  {
    if (is_string($value) && strlen($value) == 36) {
      try {
        $uuid = Uuid::fromString($value);
        return $uuid;
      } catch (\InvalidArgumentException $e) {
        // pass through
      }
    }
    return parent::convertToPHPValue($value, $platform);
  }

  /** {@inheritdoc} */
  public function convertToDatabaseValue($value, AbstractPlatform $platform)
  {
    if (is_string($value) && strlen($value) == 16) {
      try {
        return Uuid::fromBytes($value)->getBytes();
      } catch (\InvalidArgumentException $e) {
        // pass through
      }
    }
    return parent::convertToDatabaseValue($value, $platform);
  }

}

// Local Variables: ***
// c-basic-offset: 2 ***
// indent-tabs-mode: nil ***
// End: ***
