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
  use \OCA\CAFeVDBMembers\Toolkit\Traits\FakeTranslationTrait;

  public const DOMESTIC = 'Domestic';
  public const CONTINENT = 'Continent';
  public const GERMANY = 'Germany';
  public const EUROPE = 'Europe';
  public const WORLD = 'World';

  /**
   * This is just here to inject the translation keys for l10n.
   *
   * @return void
   */
  protected function translationHack():void
  {
    self::t('Domestic');
    self::t('Continent');
    self::t('Germany');
    self::t('Europe');
    self::t('World');
  }
}
