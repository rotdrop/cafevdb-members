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

use MyCLabs\Enum\Enum as EnumType;

/**
 * Geographical scope for insurances.
 *
 * @method static EnumGeographicalScope DOMESTIC()
 * @method static EnumGeographicalScope CONTINENT()
 * @method static EnumGeographicalScope GERMANY()
 * @method static EnumGeographicalScope EUROPE()
 * @method static EnumGeographicalScope WORLD()
 *
 * @todo Perhaps should be renamed to "COUNTRY/CONTINENT/WORLD"
 */
class EnumGeographicalScope extends EnumType
{
  use \OCA\CAFeVDBMembers\Traits\FakeTranslationTrait;

  public const DOMESTIC = 'Domestic';
  public const CONTINENT = 'Continent';
  public const GERMANY = 'Germany';
  public const EUROPE = 'Europe';
  public const WORLD = 'World';

  static private function translationHack()
  {
    self::t('Domestic');
    self::t('Continent');
    self::t('Germany');
    self::t('Europe');
    self::t('World');
  }
}
