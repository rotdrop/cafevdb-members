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

namespace OCA\CAFeVDBMembers\Traits;

/** Support traits for date-time stuff */
trait DateTimeTrait
{
  /**
   * Set
   *
   * @param string|int|\DateTimeInterface $dateTime
   *
   * @return null|\DateTimeImmutable
   */
  static public function convertToDateTime($dateTime):?\DateTimeImmutable
  {
    if ($dateTime === null || $dateTime === '') {
      return null;
    } else if (!($dateTime instanceof \DateTimeInterface)) {
      $timeStamp = filter_var($dateTime, FILTER_VALIDATE_INT, [ 'min_range' => 0 ]);
      if ($timeStamp === false) {
        $timeStamp = filter_var($dateTime, FILTER_VALIDATE_FLOAT, [ 'min_range' => 0 ]);
      }
      if ($timeStamp !== false) {
        return (new \DateTimeImmutable())->setTimestamp($timeStamp);
      } else if (is_string($dateTime)) {
        return new \DateTimeImmutable($dateTime);
      } else {
        throw new \InvalidArgumentException('Cannot convert input to DateTime.');
      }
    } else if ($dateTime instanceof \DateTime) {
      return \DateTimeImmutable::createFromMutable($dateTime);
    } else if ($dateTime instanceof \DateTimeImmutable) {
      return $dateTime;
    } else {
      throw new \InvalidArgumentException('Unsupported date-time class: '.get_class($dateTime));
    }
    return null; // not reached
  }
}