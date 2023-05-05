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
use OCP\Calendar\ICalendar;
use OCP\Calendar\IManager as ICalendarMananger;
use OCP\Calendar\ICalendarQuery;
use OCP\IConfig;

use OCA\CAFEVDB\Service\ConfigService;

use OCA\CAFeVDBMembers\Constants;
use OCA\CAFeVDBMembers\Database\ORM\EntityManager;
use OCA\CAFeVDBMembers\Database\ORM\Entities;

/** AJAX endpoints for a project registration form. */
class ProjectRegistrationController extends Controller
{
  use \OCA\CAFeVDBMembers\Toolkit\Traits\ResponseTrait;
  use \OCA\CAFeVDBMembers\Toolkit\Traits\LoggerTrait;
  use \OCA\CAFeVDBMembers\Toolkit\Traits\DateTimeTrait;

  /** @var IConfig */
  private $cloudConfig;

  /** @var ICalendarMananger */
  private $calendarManager;

  /** @var IDateTimeZone */
  private $dateTimeZone;

  /** @var EntityManager */
  private $entityManager;

  // phpcs:ignore Squiz.Commenting.FunctionComment.Missing
  public function __construct(
    string $appName,
    IRequest $request,
    IL10N $l10n,
    LoggerInterface $logger,
    IConfig $cloudConfig,
    ICalendarMananger $calendarManager,
    IDateTimeZone $dateTimeZone,
    EntityManager $entityManager,
  ) {
    parent::__construct($appName, $request);
    $this->l = $l10n;
    $this->logger = $logger;
    $this->cloudConfig = $cloudConfig;
    $this->calendarManager = $calendarManager;
    $this->dateTimeZone = $dateTimeZone;
    $this->entityManager = $entityManager;
  }
  // phpcs:enable

  /**
   * @return PublicTemplateResponse
   *
   * @todo Check whether we do want CSRF.
   *
   * @NoAdminRequired
   * @NoCSRFRequired
   * @PublicPage
   */
  public function page():PublicTemplateResponse
  {
    $response = new PublicTemplateResponse($this->appName, 'project-registration', [
      'appName' => $this->appName,
      'foo' => 'bar',
    ]);

    $response->setFooterVisible(false);

    $nowDate = self::convertToTimezoneDate(new DateTimeImmutable, $this->dateTimeZone->getTimeZone());
    $currentYear = $nowDate->format('Y');

    $projects = $this->entityManager->getRepository(Entities\Project::class)->findBy([
      '>=year' => $currentYear,
    ]);

    $actionMenu = [];

    /** @var Entities\Project $project */
    foreach ($projects as $project) {
      $this->logInfo('NAME ' . $project->getName());
      $deadline = $this->getProjectRegistrationDeadline($project);
      if (empty($deadline)) {
        continue;
      }
      $deadline = self::convertToTimezoneDate($deadline, $this->dateTimeZone->getTimeZone());
      if ($nowDate > $deadline) {
        continue;
      }

      $actionMenu[] = new SimpleMenuAction($project->getName(), $project->getName(), 'icon-download');
    }

    $response->setHeaderActions($actionMenu);

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
