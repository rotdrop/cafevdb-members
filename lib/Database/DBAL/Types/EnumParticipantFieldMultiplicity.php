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
 * Enum for "participant-field" multiplicity.
 *
 * @method static EnumParticipantFieldMultiplicity SIMPLE()
 * @method static EnumParticipantFieldMultiplicity SINGLE()
 * @method static EnumParticipantFieldMultiplicity MULTIPLE()
 * @method static EnumParticipantFieldMultiplicity PARALLEL()
 * @method static EnumParticipantFieldMultiplicity RECURRING()
 * @method static EnumParticipantFieldMultiplicity GROUPOFPEOPLE()
 * @method static EnumParticipantFieldMultiplicity GROUPSOFPEOPLE()
 *
 */
class EnumParticipantFieldMultiplicity extends EnumType
{
  public const SIMPLE = 'simple';
  public const SINGLE = 'single';
  public const MULTIPLE = 'multiple';
  public const PARALLEL = 'parallel';
  public const RECURRING = 'recurring';
  public const GROUPOFPEOPLE = 'groupofpeople';
  public const GROUPSOFPEOPLE = 'groupsofpeople';
}
