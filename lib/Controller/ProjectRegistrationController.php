<?php
/**
 * Member's data base connector for CAFEVDB orchetra management app.
 *
 * @author Claus-Justus Heine <himself@claus-justus-heine.de>
 * @copyright Copyright (c) 2023, 2024 Claus-Justus Heine
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

namespace OCA\CAFeVDBMembers\Controller;

use DateTimeImmutable;
use DateTimeInterface;
use DateTime;

use Psr\Log\LoggerInterface;

use OCP\AppFramework\Controller;
use OCP\IRequest;
use OCP\IL10N;
use OCP\IDateTimeZone;
use OCP\AppFramework\Http\TemplateResponse;
use OCP\AppFramework\Http\Template\PublicTemplateResponse;
use OCP\AppFramework\Http\Template\SimpleMenuAction;
use OCP\AppFramework\Services\IInitialState;
use OCP\Calendar\ICalendar;
use OCP\Calendar\IManager as ICalendarMananger;
use OCP\Calendar\ICalendarQuery;
use OCP\IConfig;
use OCP\IURLGenerator;
use OCP\IUserSession;

use OCA\CAFEVDB\Service\ConfigService;

use OCA\CAFeVDBMembers\Constants;
use OCA\CAFeVDBMembers\Database\DBAL\Types\EnumParticipantFieldDataType as FieldDataType;
use OCA\CAFeVDBMembers\Database\DBAL\Types\EnumParticipantFieldMultiplicity as FieldMultiplicity;
use OCA\CAFeVDBMembers\Database\ORM\EntityManager;
use OCA\CAFeVDBMembers\Database\ORM\Entities;
use OCA\CAFeVDBMembers\Service\EventsService;

/** AJAX endpoints for a project registration form. */
class ProjectRegistrationController extends Controller
{
  use \OCA\CAFeVDBMembers\Toolkit\Traits\ResponseTrait;
  use \OCA\CAFeVDBMembers\Toolkit\Traits\LoggerTrait;
  use \OCA\CAFeVDBMembers\Toolkit\Traits\DateTimeTrait;

  // phpcs:ignore Squiz.Commenting.FunctionComment.Missing
  public function __construct(
    string $appName,
    IRequest $request,
    private IUserSession $userSession,
    private IL10N $l,
    protected LoggerInterface $logger,
    private IConfig $cloudConfig,
    private ICalendarMananger $calendarManager,
    private IDateTimeZone $dateTimeZone,
    private IURLGenerator $urlGenerator,
    private IInitialState $initialState,
    private EntityManager $entityManager,
    private EventsService $eventsService,
  ) {
    parent::__construct($appName, $request);
  }
  // phpcs:enable

  /**
   * @param null|string $projectName
   *
   * @return TemplateResponse
   *
   * @todo Check whether we do want CSRF.
   *
   * @NoAdminRequired
   * @NoCSRFRequired
   * @PublicPage
   */
  public function page(?string $projectName):TemplateResponse
  {
    $nowDate = self::convertToTimezoneDate(new DateTimeImmutable, $this->dateTimeZone->getTimeZone());
    $currentYear = $nowDate->format('Y');

    $this->logInfo('YEAR ' . $currentYear);

    $projects = $this->entityManager->getRepository(Entities\Project::class)->findBy(
      criteria: [
        '>=year' => $currentYear,
      ],
      orderBy: [
        'year' => 'DESC',
        'name' => 'ASC',
      ],
    );

    $projectsList = [];
    $activeProject = -1;
    $timezone = $this->dateTimeZone->getTimeZone();

    /** @var Entities\Project $project */
    foreach ($projects as $project) {
      $this->logInfo('PROJECT ' . $project->getName());

      $startDate = $project->getRegistrationStartDate();
      if (empty($startDate)) {
        // there must be a registration start date, otherwise the registration
        // is considered not open.
        continue;
      }
      $startDate = self::convertToTimezoneDate($startDate, $timezone);
      if ($nowDate < $startDate) {
        continue;
      }

      $deadline = $this->getProjectRegistrationDeadline($project);
      if (empty($deadline)) {
        // no events configured yet, no explicit deadline -> registration is
        // not yet open.
        continue;
      }
      $deadline = self::convertToTimezoneDate($deadline, $timezone);
      if ($nowDate > $deadline) {
        continue;
      }

      if (empty($projectName)) {
        $projectName = $project->getName();
      }

      if ($project->getName() == $projectName) {
        $activeProject = count($projectsList);
      }

      $instrumentationNumbers = $project->getInstrumentationNumbers();
      $flatInstrumentationNumbers = [];
      /** @var Entities\ProjectInstrumentationNumber $instrumentationNumber */
      foreach ($instrumentationNumbers as $instrumentationNumber) {
        $flatData = $instrumentationNumber->toArray();
        unset($flatData['instruments']);
        $flatData['project'] = $project->getId();
        $instrument = $instrumentationNumber->getInstrument();
        $flatInstrument = $instrument->toArray();
        unset($flatInstrument['musicianInstruments']);
        $flatInstrument['families'] = [];
        foreach ($instrument->getFamilies() as $family) {
          $flatFamily = $family->toArray();
          unset($flatFamily['instruments']);
          $flatInstrument['families'][] = $flatFamily;
        }
        usort($flatInstrument['families'], fn($a, $b) => strcmp($a['family'], $b['family']));
        $flatData['instrument'] = $flatInstrument;
        $flatInstrumentationNumbers[] = $flatData;
      }

      $participantFields = $project->getParticipantFields();
      $flatParticipantFields = [];
      /** @var Entities\ProjectParticipantField $participantField */
      foreach ($participantFields as $participantField) {
        switch ($participantField->getDataType()) {
        }
        $flatData = $participantField->toArray();
        $flatData['project'] = $project->getId();
        // needed:
        // - options
        // - default value
        // - absence field if there
        $flatData['dataOptions'] = [];
        /** @var Entities\ProjectParticipantFieldDataOption $option */
        foreach ($participantField->getDataOptions() as $option) {
          $flatOption = $option->toArray();
          $flatOption['field'] = $participantField->getId();
          $flatOption['fieldData'] = [];
          /** @var Entities\ProjectParticipantFieldDatum $fieldDatum */
          foreach ($option->getFieldData() as $fieldDatum) {
            $flatOption['fieldData'][] = [
              'field' => $participantField->getId(),
              'project' => $project->getId(),
              'musician' => $fieldDatum->getMusician()->getId(),
              'optionKey' => $fieldDatum->getOptionKey(),
            ];
          }
          $flatData['dataOptions'][] = $flatOption;
        }
        $flatData['fieldData'] = [];
        /** @var Entities\ProjectParticipantFieldDatum $datum */
        foreach ($participantField->getFieldData() as $datum) {
          $flatDatum = $datum->toArray();
          $flatDatum['field'] = $participantField->getId();
          $flatDatum['project'] = $project->getId();
          $flatDatum['musician'] = $datum->getMusician()->getId();
          $flatData['fieldData'][] = $flatDatum;
        }
        unset($flatData['payments']);
        $flatParticipantFields[$participantField->getId()] = $flatData;
      }

      $calendarEvents = $project->getCalendarEvents();
      $flatCalendarEvents = [];
      /** @var Entities\ProjectEvent $projectEvent */
      foreach ($calendarEvents as $projectEvent) {
        $flatData = $projectEvent->toArray();
        unset($flatData['project']);
        $flatData['project'] = $project->getId();
        $absenceField = $projectEvent->getAbsenceField();
        $flatData['absenceField'] = $absenceField ? $absenceField->getId() : -1;
        $eventData = $this->eventsService->getEventData($projectEvent);
        if ($eventData['allday']) {
          $eventData['start'] = $eventData['start']->format('Y-m-d');
          $eventData['end'] = $eventData['end']->format('Y-m-d');
        } else {
          $eventData['start'] = $eventData['start']->format(DateTime::W3C);
          $eventData['end'] = $eventData['end']->format(DateTime::W3C);
        }
        unset($eventData['sibling']);
        unset($eventData['calendardata']);
        $flatData['calendarObject'] = $eventData;
        $flatCalendarEvents[] = $flatData;
      }

      $projectsList[] = [
        'id' => $project->getId(),
        'name' => $project->getName(),
        'year' => $project->getYear(),
        'startDate' => $startDate->format('Y-m-d'),
        'deadline' => $deadline->format('Y-m-d'),
        'instrumentation' => $flatInstrumentationNumbers,
        'participantFields' => $flatParticipantFields,
        'projectEvents' => $flatCalendarEvents,
      ];
    }

    if (!empty($this->userSession->getUser())) {
      $response = new TemplateResponse($this->appName, 'project-registration', [
        'appName' => $this->appName,
        'public' => false,
      ]);
    } else {
      $response = new PublicTemplateResponse($this->appName, 'project-registration', [
        'appName' => $this->appName,
        'public' => true,
      ]);
      $response->setHeaderTitle($this->l->t('Project Application for %s', $projectName));
      $response->setFooterVisible(false);
    }

    $this->initialState->provideInitialState('projects', $projectsList);
    $this->initialState->provideInitialState('activeProject', $activeProject);

    // provide the available instruments
    $instruments = $this->entityManager->getRepository(Entities\Instrument::class)->findBy(
      [],
      [
        'sortOrder' => 'ASC',
      ],
    );
    $flatInstruments = [];
    foreach ($instruments as $instrument) {
      $flatInstrument = $instrument->toArray();
      unset($flatInstrument['musicianInstruments']);
      $flatInstrument['families'] = [];
      foreach ($instrument->getFamilies() as $family) {
        $flatFamily = $family->toArray();
        unset($flatFamily['instruments']);
        $flatInstrument['families'][] = $flatFamily;
      }
      usort($flatInstrument['families'], fn($a, $b) => strcmp($a['family'], $b['family']));
      $flatInstruments[] = $flatInstrument;
    }

    $this->initialState->provideInitialState('instruments', $flatInstruments);

    // provide the project instruments
    // @todo

    // provide country names
    $displayLocale = $this->l->getLocaleCode();
    $displayRegion = locale_get_region($displayLocale);
    if (empty($displayRegion)) {
      $displayRegion = strtoupper($displayLocale);
      $displayLocale = $displayLocale . '_' . $displayRegion;
    }
    $this->logInfo('LOCALE ' . $displayLocale);

    $locales = resourcebundle_locales('');
    $countryCodes = [];
    foreach ($locales as $locale) {
      $country = locale_get_region($locale);
      if ($country) {
        $countryCodes[$country] = [
          'code' => $country,
          'name' => locale_get_display_region($locale, $displayLocale),
        ];
      }
    }
    usort($countryCodes, fn($a, $b) => strcmp($a['name'], $b['name']));
    $this->initialState->provideInitialState('countries', array_values($countryCodes));
    $this->initialState->provideInitialState('displayLocale', [
      'code' => $displayLocale,
      'region' => $displayRegion,
      'language' => locale_get_primary_language($displayLocale),
    ]);

    return $response;
  }

  /**
   * @param Entities\Project $project
   *
   * @return null|DateTimeInterface
   */
  private function getProjectRegistrationDeadline(Entities\Project $project):?DateTimeInterface
  {
    $deadline = $project->getRegistrationDeadline();
    if (!empty($deadline)) {
      return $deadline;
    }

    $shareOwner = $this->cloudConfig->getAppValue(Constants::CAFEVDB_APP_ID, ConfigService::SHAREOWNER_KEY);
    if (empty($shareOwner)) {
      return null;
    }

    $principalUri = 'principals/users/' . $shareOwner;
    $projectCategory = $project->getName();
    $query = $this->calendarManager->newQuery($principalUri);
    $query->addSearchProperty(ICalendarQuery::SEARCH_PROPERTY_CATEGORIES);
    $query->setSearchPattern($projectCategory);
    $query->addSearchCalendar(ConfigService::REHEARSALS_CALENDAR_URI);
    $query->addSearchCalendar(ConfigService::CONCERTS_CALENDAR_URI);

    $calendarObjects = $this->calendarManager->searchForPrincipal($query);

    if (empty($calendarObjects)) {
      return null;
    }

    $startDates = [];

    foreach ($calendarObjects as $objectInfo) {
      foreach ($objectInfo['objects'] as $calendarObject) {
        $startDates[] = $calendarObject['DTSTART'][0];
        $this->logInfo('START ' . print_r($calendarObject['DTSTART'][0], true));
      }
    }

    $deadline = min($startDates)->modify('-1 day');

    return $deadline;
  }
}
