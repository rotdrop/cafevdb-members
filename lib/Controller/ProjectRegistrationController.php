<?php
/**
 * Member's data base connector for CAFEVDB orchetra management app.
 *
 * @author Claus-Justus Heine <himself@claus-justus-heine.de>
 * @copyright Copyright (c) 2023 Claus-Justus Heine
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

use Psr\Log\LoggerInterface;
use DateTimeImmutable;
use DateTimeInterface;

use OCP\AppFramework\Controller;
use OCP\IRequest;
use OCP\IL10N;
use OCP\IDateTimeZone;
use OCP\AppFramework\Http\Template\PublicTemplateResponse;
use OCP\AppFramework\Http\Template\SimpleMenuAction;
use OCP\AppFramework\Services\IInitialState;
use OCP\Calendar\ICalendar;
use OCP\Calendar\IManager as ICalendarMananger;
use OCP\Calendar\ICalendarQuery;
use OCP\IConfig;
use OCP\IURLGenerator;

use OCA\CAFEVDB\Service\ConfigService;

use OCA\CAFeVDBMembers\Constants;
use OCA\CAFeVDBMembers\Database\ORM\EntityManager;
use OCA\CAFeVDBMembers\Database\ORM\Entities;
use OCA\CAFeVDBMembers\Service\EventsService;

/** AJAX endpoints for a project registration form. */
class ProjectRegistrationController extends Controller
{
  use \OCA\CAFeVDBMembers\Toolkit\Traits\ResponseTrait;
  use \OCA\CAFeVDBMembers\Toolkit\Traits\LoggerTrait;
  use \OCA\CAFeVDBMembers\Toolkit\Traits\DateTimeTrait;

  /** @var IL10N */
  private $l;

  /** @var IConfig */
  private $cloudConfig;

  /** @var ICalendarMananger */
  private $calendarManager;

  /** @var IDateTimeZone */
  private $dateTimeZone;

  /** @var IURLGenerator */
  private $urlGenerator;

  /** @var IInitialState */
  private $initialState;

  /** @var EntityManager */
  private $entityManager;

  /** @var EventsService */
  private $eventsService;

  // phpcs:ignore Squiz.Commenting.FunctionComment.Missing
  public function __construct(
    string $appName,
    IRequest $request,
    IL10N $l10n,
    LoggerInterface $logger,
    IConfig $cloudConfig,
    ICalendarMananger $calendarManager,
    IDateTimeZone $dateTimeZone,
    IURLGenerator $urlGenerator,
    IInitialState $initialState,
    EntityManager $entityManager,
    EventsService $eventsService,
  ) {
    parent::__construct($appName, $request);
    $this->l = $l10n;
    $this->logger = $logger;
    $this->cloudConfig = $cloudConfig;
    $this->calendarManager = $calendarManager;
    $this->dateTimeZone = $dateTimeZone;
    $this->urlGenerator = $urlGenerator;
    $this->initialState = $initialState;
    $this->entityManager = $entityManager;
    $this->eventsService = $eventsService;
  }
  // phpcs:enable

  /**
   * @param null|string $projectName
   *
   * @return PublicTemplateResponse
   *
   * @todo Check whether we do want CSRF.
   *
   * @NoAdminRequired
   * @NoCSRFRequired
   * @PublicPage
   */
  public function page(?string $projectName):PublicTemplateResponse
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

    $actionMenu = [
      new SimpleMenuAction('menu-trigger', $this->l->t('Select a Project'), ''),
    ];
    $projectsList = [];
    $activeProject = -1;
    $timezone = $this->dateTimeZone->getTimeZone();

    /** @var Entities\Project $project */
    foreach ($projects as $project) {
      $this->logInfo('PROJECT ' . $project->getName());

      $eventData = $this->eventsService->getProjectEventData($project);
      $this->logInfo('EVENT DATA ' . print_r($eventData, true));

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

      $link = $this->urlGenerator->linkToRoute($this->appName . '.project_registration.page', [ 'projectName' => $project->getName() ]);
      $menuItem = new SimpleMenuAction($project->getName(), $project->getName(), 'icon-group', $link);
      $actionMenu[] = $menuItem;

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

      $projectsList[] = [
        'id' => $project->getId(),
        'name' => $project->getName(),
        'year' => $project->getYear(),
        'deadline' => $deadline,
        'instrumentation' => $flatInstrumentationNumbers,
      ];
    }

    $response = new PublicTemplateResponse($this->appName, 'project-registration', [
      'appName' => $this->appName,
    ]);
    $response->setFooterVisible(false);
    $response->setHeaderTitle($this->l->t('Project Application for %s', $projectName));
    $response->setHeaderActions($actionMenu);

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
