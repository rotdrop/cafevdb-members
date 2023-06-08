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

namespace OCA\CAFeVDBMembers\Service;

use OCA\DAV\CalDAV\CalDavBackend;

/**
 * Service class in order to interface to the dav app of Nextcloud.
 *
 * @todo: replace the stuff below by more persistent APIs. As it shows
 * (Mai. 2023) the only option would be http calls to the dav service. We
 * actually need the calendar-data in order to use the Sabre EventIterator.
 */
class CalDavService
{
  /** @var CalDavBackend */
  private $calDavBackend;

  // phpcs:disable Squiz.Commenting.FunctionComment.Missing
  public function __construct(
    CalDavBackend $calDavBackend,
  ) {
    $this->calDavBackend = $calDavBackend;
  }
  // phpcs:enable

  /**
   * Fetch an event object by its local URI.
   *
   * The return value is an array with the following keys:
   *   * calendardata - The iCalendar-compatible calendar data
   *   * uri - a unique key which will be used to construct the uri. This can
   *     be any arbitrary string, but making sure it ends with '.ics' is a
   *     good idea. This is only the basename, or filename, not the full
   *     path.
   *   * lastmodified - a timestamp of the last modification time
   *   * etag - An arbitrary string, surrounded by double-quotes. (e.g.:
   *   '"abcdef"')
   *   * size - The size of the calendar objects, in bytes.
   *   * component - optional, a string containing the type of object, such
   *     as 'vevent' or 'vtodo'. If specified, this will be used to populate
   *     the Content-Type header.
   *   * calendarid - The passed argument $calendarId
   *
   * @param int $calendarId
   *
   * @param string $objectIdentifier Either the URI or the UID of the
   * object. If $objectIdentifier ends with '.ics' it is assumed to be an URI,
   * otherwise the UID.
   *
   * @return array|null
   *
   * @bug This function uses internal APIs. This could be changed to a
   * CalDav call which would then only return the serialized data,
   * respectively an arry/proxy object with calendarId, uri and the
   * calendar data.
   */
  public function getCalendarObject(int $calendarId, string $localUri):?array
  {
    $result = $this->calDavBackend->getCalendarObject($calendarId, $localUri);
    if (!empty($result)) {
      $result['calendarid'] = $calendarId;
    }
    return $result;
  }
}
