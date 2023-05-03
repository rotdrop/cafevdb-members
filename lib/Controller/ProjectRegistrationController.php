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

use OCP\AppFramework\Controller;
use OCP\IRequest;
use OCP\IL10N;
use OCP\AppFramework\Http\Template\PublicTemplateResponse;
use OCP\AppFramework\Http\Template\SimpleMenuAction;

use OCA\CAFeVDBMembers\Database\ORM\EntityManager;
use OCA\CAFeVDBMembers\Database\ORM\Entities;

/** AJAX endpoints for a project registration form. */
class ProjectRegistrationController extends Controller
{
  use \OCA\CAFeVDBMembers\Toolkit\Traits\ResponseTrait;
  use \OCA\CAFeVDBMembers\Toolkit\Traits\LoggerTrait;

  /** @var EntityManager */
  private $entityManager;

  // phpcs:ignore Squiz.Commenting.FunctionComment.Missing
  public function __construct(
    string $appName,
    IRequest $request,
    IL10N $l10n,
    LoggerInterface $logger,
    EntityManager $entityManager,
  ) {
    parent::__construct($appName, $request);
    $this->l = $l10n;
    $this->logger = $logger;
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

    $projects = $this->entityManager->getRepository(Entities\Project::class)->findAll();

    $actionMenu = [];
    /** @var Entities\Project $project */
    foreach ($projects as $project) {
      $actionMenu[] = new SimpleMenuAction($project->getName(), $project->getName(), 'icon-download');
    }

    $response->setHeaderActions($actionMenu);

    return $response;
  }
}
