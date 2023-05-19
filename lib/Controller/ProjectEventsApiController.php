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

use DateTimeZone;

use Psr\Log\LoggerInterface as ILogger;

use OCP\AppFramework\Http;
use OCP\AppFramework\ApiController;
use OCP\IRequest;
use OCP\AppFramework\Http\DataResponse;
use OCP\IL10N;
use OCP\IDateTimeZone;

use OCA\CAFeVDBMembers\Database\ORM\EntityManager;
use OCA\CAFeVDBMembers\Database\ORM\Entities;
use OCA\CAFeVDBMembers\Service\EventsService;

/**
 * Public API for project events. This is somehow "legacy" and could be
 * improved. It is used by the Redaxo CMS in order to automatically blend in
 * rehearsal and concert events.
 */
class ProjectEventsApiController extends ApiController
{
  use \OCA\CAFeVDBMembers\Toolkit\Traits\LoggerTrait;

  const INDEX_BY_PROJECT = 'byProject';
  const INDEX_BY_WEB_PAGE = 'byWebPage';

  /** @var IL10N */
  private $l;

  /** @var IDateTimeZone */
  private $dateTimeZone;

  /** @var EventsService */
  private $eventsService;

  /** @var EntityManager */
  private $entityManager;

  // phpcs:disable Squiz.Commenting.FunctionComment.Missing
  public function __construct(
    ?string $appName,
    IRequest $request,
    IL10N $l10n,
    IDateTimeZone $dateTimeZone,
    ILogger $logger,
    EventsService $eventsService,
    EntityManager $entityManager,
  ) {
    parent::__construct($appName, $request);
    $this->eventsService = $eventsService;
    $this->entityManager = $entityManager;
    $this->l = $l10n;
    $this->dateTimeZone = $dateTimeZone;
    $this->logger = $logger;
  }
  // phpcs:enable

  /**
   * @param string $indexObject
   *
   * @param int $objectId
   *
   * @param string $calendar
   *
   * @param string $timezone
   *
   * @param string $locale
   *
   * @return DataResponse
   *
   * @CORS
   * @NoCSRFRequired
   * @NoAdminRequired
   * @PublicPage
   */
  public function serviceSwitch(
    string $indexObject,
    int $objectId,
    string $calendar,
    ?string $timezone,
    ?string $locale,
  ):DataResponse {
    // OC uses symphony which rawurldecodes the request URL. This
    // implies that in order to pass a slash / we caller must
    // urlencode that thingy twice, and Symphony consequently will
    // only deliver encoded data in this case.

    switch ($indexObject) {
      case self::INDEX_BY_PROJECT:
        $projectId = $objectId;
        if ($calendar == 'all') {
          $calendar = null;
        } else {
          $calendar = [ $calendar ];
        }

        $timezone = $this->getTimezone($timezone);
        $locale = $this->getLocale($locale);

        $eventData = $this->eventsService->getProjectEventData($projectId, $calendar, $timezone, $locale);
        return new DataResponse($eventData, Http::STATUS_OK);
      case self::INDEX_BY_WEB_PAGE:
        $articleId = $objectId;

        if ($calendar == 'all') {
          $calendar = null;
        } else {
          $calendar = [ $calendar ];
        }

        $timezone = $this->getTimezone($timezone);
        $locale = $this->getLocale($locale);

        $articles = $this->entityManager->getRepository(Entities\ProjectWebPage::class)->findBy([
          'articleId' => $articleId
        ], [
          'project' => 'ASC', 'articleId' => 'ASC',
        ]);

        $data = [];
        /** @var Entities\ProjectWebPage $article */
        foreach ($articles as $article) {
          $project = $article->getProject();
          $data[$project->getName()] = $this->eventsService->getProjectEventData($project, $calendar, $timezone, $locale);
        }
        return new DataResponse($data, Http::STATUS_OK);
      default:
        $this->logInfo('NOT FOUND ' . $indexObject);
        return new DataResponse([], Http::STATUS_NOT_FOUND);
    }
  }

  /**
   * @param null|string $timezone
   *
   * @return DateTimeZone
   */
  private function getTimeZone(?string $timezone):DateTimeZone
  {
    if ($timezone === null) {
      return $this->dateTimeZone->getTimeZone();
    } else {
      $timezone = rawurldecode($timezone);
      return new DateTimeZone($timezone);
    }
  }

  /**
   * @param null|string $locale
   *
   * @return string
   */
  private function getLocale(?string $locale):string
  {
    if ($locale == null) {
      $locale = $this->l->getLocaleCode();
      $lang = locale_get_primary_language($locale);
      if ($lang == $locale) {
        $locale = $lang.'_'.strtoupper($lang);
      }
      if (strpos($locale, '.') === false) {
        $locale .= '.UTF-8';
      }
    } else {
      $locale = rawurldecode($locale);
    }
    return $locale;
  }
}
