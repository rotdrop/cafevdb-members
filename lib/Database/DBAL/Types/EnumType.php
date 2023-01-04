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

namespace OCA\CAFeVDBMembers\Database\DBAL\Types;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Acelaya\Doctrine\Type\PhpEnumType;

use function call_user_func;
use function implode;
use function sprintf;

/** General enum base-class. */
class EnumType extends PhpEnumType
{
  /** {@inheritdoc} */
  public function getSQLDeclaration(array $fieldDeclaration, AbstractPlatform $platform):string
  {
    $values = call_user_func([$this->enumClass, 'toArray']);
    $values = array_map(fn($val) => "'".$val."'", $values);
    return "enum(".implode(",", $values).")";
  }

  /** {@inheritdoc} */
  public function getValues()
  {
    return call_user_func([$this->enumClass, 'toArray']);
  }
}
