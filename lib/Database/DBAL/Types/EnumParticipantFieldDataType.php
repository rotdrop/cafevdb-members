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
 * Enum for "participant-fields" data-types.
 *
 * @see \OCA\CAFEVDB\Database\Doctrine\ORM\Entities\ProjectParticipantField
 *
 * @method static EnumParticipantFieldDataType TEXT()
 *   Plain UTF-8 text field.
 * @method static EnumParticipantFieldDataType HTML()
 *   HTML text field.
 * @method static EnumParticipantFieldDataType BOOLEAN()
 *   Yes/no value.
 * @method static EnumParticipantFieldDataType INTEGER()
 *   Integral number.
 * @method static EnumParticipantFieldDataType FLOAT()
 *   Floating point number.
 * @method static EnumParticipantFieldDataType DATE()
 *   A date without time.
 * @method static EnumParticipantFieldDataType DATETIME()
 *   A date with time information.
 *
 * @method static EnumParticipantFieldDataType SERVICE_FEE()
 * A service-fee value with the convention that positive values denote
 * receivables and negative values denote liabilities (from the view
 * of the orchestra.
 *
 * @method static EnumParticipantFieldDataType CLOUD_FILE()
 * Single-file upload data which is stored in the storage of the
 * ambient cloud software.
 *
 * @method static EnumParticipantFieldDataType CLOUD_FOLDER()
 * Multi-file upload data which is stored in the storage of the
 * ambient cloud software under a folder with the configured name.
 *
 * @method static EnumParticipantFieldDataType DB_FILE()
 * Single-file upload data which is stored as blob in the
 * database. The total encoded size is limited by the used database
 * backend and its associated data-type.
 */
class EnumParticipantFieldDataType extends EnumType
{
  public const TEXT = 'text';
  public const HTML = 'html';
  public const BOOLEAN = 'boolean';
  public const INTEGER = 'integer';
  public const FLOAT = 'float';
  public const DATE = 'date';
  public const DATETIME = 'datetime';
  public const SERVICE_FEE = 'service-fee';
  public const CLOUD_FILE = 'cloud-file';
  public const CLOUD_FOLDER = 'cloud-folder';
  public const DB_FILE = 'db-file';
};
