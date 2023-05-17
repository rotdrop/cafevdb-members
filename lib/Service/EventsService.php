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

use DateTimeInterface;
use DateTimeImmutable;

use Sabre\VObject;
use Sabre\VObject\Recur\EventIterator;
use Sabre\VObject\Recur\MaxInstancesExceededException;
use Sabre\VObject\Recur\NoInstancesException;
use Sabre\VObject\Component as VComponent;
use Sabre\VObject\Component\VCalendar;
use Sabre\VObject\Component\VEvent;
use Sabre\VObject\Property\ICalendar as VProperty;

use Psr\Log\LoggerInterface;
use OCP\IL10N;
use OCP\Calendar\ICalendar;
use OCP\Calendar\IManager as ICalendarManager;
use OCP\IConfig as ICloudConfig;
use OCP\IDateTimeZone;
use OCP\IDateTimeFormatter;

use OCA\CAFeVDBMembers\Database\ORM\EntityManager;
use OCA\CAFeVDBMembers\Database\ORM\Entities;
use OCA\CAFeVDBMembers\Database\DBAL\Types\EnumVCalendarType as VCalendarType;
use OCA\CAFeVDBMembers\Constants;

use OCA\CAFEVDB\Service\ConfigService;

/**
 * More or less a service provider for a public page giving unauthenticated
 * access to rehearsal and concert events.
 *
 * This is a stripped down "clone" of OCA\CAFEVDB\Service\EventsService, with
 * some merged in support functions of the
 * OCA\CAFEVDB\Service\VCalendarService.
 */
class EventsService
{
  use \OCA\CAFeVDBMembers\Toolkit\Traits\LoggerTrait;

  private const VTODO = VCalendarType::VTODO;
  private const VEVENT = VCalendarType::VEVENT;
  private const VCARD = VCalendarType::VCARD;
  private const VJOURNAL = VCalendarType::VJOURNAL;

  /** @var IL10N */
  private $l;

  /** @var IDateTimeZone */
  private $dateTimeZone;

  /** @var IDateTimeFormatter */
  private $dateTimeFormatter;

  /** @var ICloudConfig */
  private $cloudConfig;

  /** @var ICalendarManager */
  private $calendarManager;

  /** @var EntityManager */
  private $entityManager;

  /** @var CalDavService */
  private $calDavService;

  // phpcs:disable Squiz.Commenting.FunctionComment.Missing
  public function __construct(
    LoggerInterface $logger,
    IL10N $l10n,
    IDateTimeZone $dateTimeZone,
    IDateTimeFormatter $dateTimeFormatter,
    ICloudConfig $cloudConfig,
    ICalendarManager $calendarManager,
    EntityManager $entityManager,
    CalDavService $calDavService,
  ) {
    $this->logger = $logger;
    $this->l = $l10n;
    $this->dateTimeZone = $dateTimeZone;
    $this->dateTimeFormatter = $dateTimeFormatter;
    $this->cloudConfig = $cloudConfig;
    $this->calendarManager = $calendarManager;
    $this->entityManager = $entityManager;
    $this->calDavService = $calDavService;
  }
  // phpcs:enable

  /**
   * Return event data for given project id and calendar id. Used in
   * an API call from Redaxo.
   *
   * @param int|Entities\Project $projectOrId
   *
   * @param null|string|array $calendarUris null to get the events from all
   * calendars or the 'uri' component from
   * OCA\CAFEVDB\Service\ConfigService::CALENDARS.
   *
   * @param null|string $timezone
   *
   * @param null|string $locale
   *
   * @return array
   * ```
   * [
   *   [ 'events' => EVENT_DATA1 ],
   *   [ 'events' => EVENT_DATA2 ],
   *   ...
   * ]
   * ```
   */
  public function getProjectEventData(
    int|Entities\Project $projectOrId,
    ?array $calendarUris = null,
    ?string $timezone = null,
    ?string $locale = null,
  ):array {

    if ($calendarUris === null) {
      $calendarUris = array_filter(array_map(fn(array $cal) => $cal['public'] ? $cal['uri'] : null, ConfigService::CALENDARS));
    }

    $events = $this->getProjectEvents($projectOrId, $calendarUris);

    $result = [];

    $shareOwner = $this->cloudConfig->getAppValue(Constants::CAFEVDB_APP_ID, ConfigService::SHAREOWNER_KEY);
    if (empty($shareOwner)) {
      return $result;
    }
    $principalUri = 'principals/users/' . $shareOwner;

    $calendars = $this->calendarManager->getCalendarsForPrincipal($principalUri, $calendarUris);

    /** @var ICalendar $calendar */
    foreach ($calendars as $calendar) {
      $result[$calendar->getUri()] = [
        'name' => $calendar->getDisplayName(),
        'events' => [],
      ];
    }

    foreach ($events as $event) {
      $calendarUri = $event['calendarUri'];
      $result[$calendarUri]['events'][] = $this->getBriefEventData($event, $timezone, $locale);
    }

    return $result;
  }


  /**
   * @param bool $public Hide non-public calendars.
   *
   * @return array The IDs of the default calendars.
   *
   * @see ConfigService::CALENDARS
   */
  public function getDefaultCalendars(bool $public = false):array
  {
    $result = [];
    foreach (ConfigService::CALENDARS as $cal) {
      if ($public && !$cal['public']) {
        continue;
      }
      $result[$cal['uri']] = $this->getConfigValue($cal['uri'].'calendar'.'id');
    }
    return $result;
  }

  /**
   * Fetch the list of events associated with $projectId. This
   * functions fetches all the data, not only the pivot-table. Time
   * stamps from the data-base are converted to PHP DateTime()-objects
   * with UTC time-zone.
   *
   * @param int|Entities\Project $projectOrId Database entity or its id.
   *
   * @param null|array $calendarUris
   *
   * @return array Event-data as generated by self::makeEvent().
   */
  public function getProjectEvents(int|Entities\Project $projectOrId, ?array $calendarUris):array
  {
    $criteria = [
      'project' => $projectOrId,
      'type' => VCalendarType::VEVENT,
    ];
    if (!empty($calendarUris)) {
      $criteria['calendarUri'] = $calendarUris;
    }
    $projectEvents = $this->entityManager->getRepository(Entities\ProjectEvent::class)->findBy($criteria);

    $events = [];
    /** @var Entities\ProjectEvent $projectEvent */
    foreach ($projectEvents as $projectEvent) {
      $eventData = $this->getEventData($projectEvent);
      if (empty($eventData)) {
        continue;
      }
      $events[] = $eventData;
    }

    usort($events, function($a, $b) {
      return (($a['start'] == $b['start'])
              ? 0
              : (($a['start'] < $b['start']) ? -1 : 1));
    });

    return $events;
  }

  /**
   * Form an array with the most relevant event data.
   *
   * @param array $eventObject The corresponding event object from fetchEvent() or events().
   *
   * @param null|string $timezone Explicit time zone to use, otherwise fetched
   * from user-settings.
   *
   * @param null|string $locale Explicit language setting to use, otherwise
   * fetched from user-settings.
   *
   * @return array
   * ```
   * [
   *   'times' => $this->getEventTimes($eventObject, $timezone, $locale),
   *   'summary' => $eventObject['summary'],
   *   'location' => $eventObject['location'],
   *   'description' => $eventObject['description'],
   * ]
   * ```
   */
  private function getBriefEventData(array $eventObject, ?string $timezone = null, ?string $locale = null):array
  {
    $times = $this->getEventTimes($eventObject, $timezone, $locale);

    $quoted = array('\,' => ',', '\;' => ';');
    $summary = strtr($eventObject['summary'], $quoted);
    $location = strtr($eventObject['location'], $quoted);
    $description = strtr($eventObject['description'], $quoted);

    return [
      'times' => $times,
      'summary' => $summary,
      'location' => $location,
      'description' => $description
    ];
  }

  /**
   * Form start and end date and time in given timezone and locale.
   *
   * @param array $eventObject The corresponding event object from fetchEvent() or events().
   *
   * @param null|string $timezone Explicit time zone to use, otherwise fetched
   * from user-settings.
   *
   * @param null|string $locale Explicit language setting to use, otherwise
   * fetched from user-settings.
   *
   * @return array
   * ```
   * [ 'start' => array('date' => ..., 'time' => ..., 'allday' => ...), 'end' => ... ]
   * ```
   *
   * @todo Perhaps convert to DateTime class instead of using strftime().
   */
  private function getEventTimes(array $eventObject, ?string $timezone = null, ?string $locale = null):array
  {
    if ($timezone === null) {
      $timezone = $this->dateTimeZone->getTimeZone();
    }
    if ($locale === null) {
      $locale = $this->l->getLocaleCode();
    }

    /** @var DateTimeInterface $start */
    $start = $eventObject['start'];
    /** @var DateTimeInterface $end */
    $end   = $eventObject['end'];
    $allDay = $eventObject['allday'];

    $startStamp = $start->getTimestamp();

    $endStamp = $end->getTimestamp();

    $startDate = $this->dateTimeFormatter->formatDate($start, 'short', $timezone);
    $startTime = $start->format('H:i');
    $endTime = $end->format('H:i');
    if ($endTime == '00:00') {
      // make whole-day events a little more readable
      $endTime = '24:00';
      $endDate = $this->dateTimeFormatter->formatDate($endStamp - 1, 'short', $timezone);
    } else {
      $endDate = $this->dateTimeFormatter->formatDate($endStamp, 'short', $timezone);
    }

    return [
      'timezone' => $timezone,
      'locale' => $locale,
      'allday' => $allDay,
      'start' => [
        'stamp' => $startStamp,
        'date' => $startDate,
        'time' => $startTime,
      ],
      'end' => [
        'stamp' => $endStamp,
        'date' => $endDate,
        'time' => $endTime,
      ],
    ];
  }

  /**
   * Augment the database entity by calendar data.
   *
   * @param Entities\ProjectEvent $projectEvent Database entity.
   *
   * @return array|null Returns null if the calendar object cannot be
   * found, otherwise an array
   * ```
   * [
   *   'projectid' => PROJECT_ID,
   *   'uri' => EVENT_URI,
   *   'uid' => EVENT_UID,
   *   'calendarid' => CALENDAR_ID,
   *   'start' => \DateTime,
   *   'end' => \DateTime,
   *   'allday' => BOOL
   *   'summary' => SUMMARY,
   *   'description' => DESCRTION,
   *   'location' => LOCATION,
   * ]
   * ```
   */
  public function getEventData(Entities\ProjectEvent $projectEvent):?array
  {
    $event = [];
    $event['projectid'] = $projectEvent->getProject()->getId();
    $event['uri'] = $projectEvent->getEventUri();
    $event['uid'] = $projectEvent->getEventUid();
    $event['calendarid'] = $projectEvent->getCalendarId();
    $event['calendarId'] = $event['calendarid'];
    $event['calendarUri'] = $projectEvent->getCalendarUri();
    $event['sequence'] = $projectEvent->getSequence();
    $event['recurrenceId'] = $projectEvent->getRecurrenceId();
    $event['seriesUid'] = (string)$projectEvent->getSeriesUid();
    $absenceField = $projectEvent->getAbsenceField();
    $softDeleteableState = $this->entityManager->disableFilter(EntityManager::SOFT_DELETEABLE_FILTER);
    $event['absenceField'] = !empty($absenceField) && $absenceField->getDeleted() == null ? $absenceField->getId() : 0;
    $this->entityManager->enableFilter(EntityManager::SOFT_DELETEABLE_FILTER, $softDeleteableState);
    $calendarObject = $this->calDavService->getCalendarObject($event['calendarid'], $event['uri']);
    if (empty($calendarObject)) {
      $this->logDebug('Orphan project event found: ' . print_r($event, true) . (new Exception())->getTraceAsString());
      return null;
    }
    /** @var VCalendar $vCalendar */
    $vCalendar = VObject\Reader::read($calendarObject['calendardata']);
    // /** @var VEvent $sibling */
    $siblings = $this->getVEventSiblings($event['calendarid'], $vCalendar);
    $vEvent = $siblings[$event['recurrenceId']] ?? null;
    if ($vEvent === null) {
      $this->logError('Unable to find the event-sibling for uri ' . $event['uri'] . ' and recurrence-id ' . $event['recurrenceId'] . ' ' . print_r(array_keys($siblings), true));
      return null;
    }
    $this->fillEventDataFromVObject($vEvent, $event);

    $vObject = self::getVObject($vCalendar);
    $event['seriesStart'] = $vObject->DTSTART->getDateTime();


    return $event;
  }

  /**
   * Convert the given VEvent object to a simpler flat array structure.
   *
   * @param VEvent $vObject
   *
   * @param array $event Output array to fill.
   *
   * @return array Just return $event, with the following data filled in:
   * ```
   * [
   *   ...
   *   'start' => \DateTime,
   *   'end' => \DateTime,
   *   'allday' => BOOL
   *   'summary' => SUMMARY,
   *   'description' => DESCRTION,
   *   'location' => LOCATION,
   *   ...
   * ]
   * ```
   */
  private function fillEventDataFromVObject(VEvent $vObject, array &$event = []):array
  {
    $dtStart = $vObject->DTSTART;
    $dtEnd   = self::getDTEnd($vObject);

    $start = $dtStart->getDateTime();
    $end = $dtEnd->getDateTime();
    $allDay = !$dtStart->hasTime();

    $timeZone = $this->dateTimeZone->getTimeZone();
    if (!$allDay) {
      if ($dtStart->isFloating()) {
        $start = $start->setTimezone($timeZone);
      }
      if ($dtEnd->isFloating()) {
        $end = $end->setTimezone($timeZone);
      }
    } else {
      // ??
      $start = new DateTimeImmutable($start->format('Y-m-d H:i:s'), $timeZone);
      $end = new DateTimeImmutable($end->format('Y-m-d H:i:s'), $timeZone);
    }

    $event['start'] = $start;
    $event['end'] = $end;
    $event['allday'] = $allDay;

    // description + summary?
    $event['summary'] = (string)$vObject->SUMMARY;
    $event['description'] = (string)$vObject->DESCRIPTION;
    $event['location'] = (string)$vObject->LOCATION;
    $event['categories'] = self::getCategories($vObject);
    $recurrenceId = $vObject->{'RECURRENCE-ID'};
    if ($recurrenceId !== null) {
      $event['recurrenceId'] = $recurrenceId->getDateTime()->getTimestamp();
    }
    $sequence = $vObject->SEQUENCE;
    if ($sequence) {
      $event['sequence'] = (string)$sequence;
    }

    return $event;
  }

  /**
   * Use the EventIterator to generate all siblings of a recurring event. For
   * non-recurring events return a single element array containing just this
   * VEvent instance.
   *
   * @param int $calendarId
   *
   * @param VCalendar $vCalendar
   *
   * @return array<int, VEvent>
   */
  private function getVEventSiblings(int $calendarId, VCalendar $vCalendar):array
  {
    $vObject = self::getVObject($vCalendar);
    $uid = (string)$vObject->UID;
    $sequence = (int)(string)($vObject->SEQUENCE ?? 0);
    $siblings = $this->eventSiblings[$calendarId][$uid][$sequence] ?? null;
    if ($siblings !== null) {
      return $siblings;
    }
    if (!self::isEventRecurring($vObject)) {
      $siblings = [ 0 => $vObject ];
    } else {
      $vEvents = self::getAllVObjects($vCalendar);
      try {
        // there is also the expand() method on the VCalendar ...
        $siblings = [];
        $eventIterator = new EventIterator($vEvents);
        while ($eventIterator->valid()) {
          $sibling = $eventIterator->getEventObject();
          $recurrenceId = $sibling->{'RECURRENCE-ID'}->getDateTime()->getTimestamp();
          $siblings[$recurrenceId] = $sibling;
          $eventIterator->next();
        }
      } catch (NoInstancesException $e) {
        // This event is recurring, but it doesn't have a single
        // instance. We are skipping this event from the output
        // entirely.
        $siblings = [];
      } catch (MaxInstancesExceededException $e) {
        // hopefully happens never, but ... just live with the sequence we
        // have.
      }
    }
    $this->eventSiblings[$calendarId][$uid][$sequence] = $siblings;
    return $siblings;
  }

  /**
   * Return the "master" wrapped object, where the VCalendar object here is
   * rather not an entire calendar, but one item from the database. Still it
   * may contain more than one Component, e.g. for recurring events. In this
   * case the "master" item is returned.
   *
   * @param VCalendar $vCalendar VCalendar object.
   *
   * @return The inner object, or one of the inner objects.
   */
  private static function getVObject(VCalendar $vCalendar):VComponent
  {
    if (isset($vCalendar->VEVENT)) {
      $vObject = $vCalendar->VEVENT;
      foreach ($vObject as $instance) {
        if ($instance->{'RECURRENCE-ID'} === null) {
          break;
        }
        $instance = null;
      }
      if ($instance) {
        $vObject = $instance;
      }
    } elseif (isset($vCalendar->VTODO)) {
      $vObject = $vCalendar->VTODO;
    } elseif (isset($vCalendar->VJOURNAL)) {
      $vObject = $vCalendar->VJOURNAL;
    } elseif (isset($vCalendar->VCARD)) {
      $vObject = $vCalendar->VCARD;
    } else {
      throw new Exception('Called with empty or no VComponent');
    }
    return $vObject;
  }

  /**
   * Get all components of a given type from a VCalendar object and return
   * them as flat array.
   *
   * @param VCalendar $vCalendar VCalendar object.
   *
   * @param string $type Defaults to 'VEVENT'
   *
   * @return array
   */
  private static function getAllVObjects(VCalendar $vCalendar, string $type = self::VEVENT):array
  {
    $vObjects = [];
    foreach ($vCalendar->children() as $child) {
      if (!($child instanceof VComponent)) {
        continue;
      }

      if ($child->name !== $type) {
        continue;
      }

      $vObjects[] = $child;
    }
    return $vObjects;
  }

  /**
   * Determine if the given event is recurring or not.
   *
   * @param VComponent $vComponent Sabre VCalendar or VEvent object.
   *
   * @return bool
   */
  private static function isEventRecurring(VComponent $vComponent):bool
  {
    if ($vComponent instanceof VCalendar) {
      // get the inner object
      $vObject = self::getVObject($vComponent);
    } else {
      $vObject = $vComponent;
    }
    return isset($vObject->RRULE) || isset($vObject->RDATE);
  }

  /**
   * @param VComponent $vObject VEvent or anything else with DTEND or DTSTART
   * + DURATION.
   *
   * @return mixed DTEND property if set, otherwise DTSTART + duration.
   */
  private static function getDTEnd(VComponent $vObject):VProperty\DateTime
  {
    if ($vObject->DTEND) {
      return $vObject->DTEND;
    }
    $dtEnd = clone $vObject->DTSTART;
    if ($vObject->DURATION) {
      $duration = strval($vObject->DURATION);
      $invert = 0;
      if ($duration[0] == '-') {
        $duration = substr($duration, 1);
        $invert = 1;
      }
      if ($duration[0] == '+') {
        $duration = substr($duration, 1);
      }
      $interval = new DateInterval($duration);
      $interval->invert = $invert;
      $dtEnd->getDateTime()->add($interval);
    }
    return $dtEnd;
  }

  /**
   * Return the category list for the given object
   *
   * @param VComponent $vComponent Sabre VCalendar or VEvent object.
   *
   * @return An array with the categories for the object.
   */
  private static function getCategories(VComponent $vComponent):array
  {
    if ($vComponent instanceof VCalendar) {
      // get the inner object
      $vObject = self::getVObject($vComponent);
    } else {
      $vObject = $vComponent;
    }
    return isset($vObject->CATEGORIES) ? $vObject->CATEGORIES->getParts() : [];
  }
}
