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

namespace OCA\CAFeVDBMembers\Controller;

use Psr\Log\LoggerInterface;

use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\DataResponse;
use OCP\IRequest;
use OCP\IL10N;

use OCA\CAFeVDBMembers\AppInfo\Application;
use OCA\CAFeVDBMembers\Service\NoteService;
use OCA\CAFeVDBMembers\Database\ORM\EntityManager;
use OCA\CAFeVDBMembers\Database\ORM\Entities;

class MemberDataController extends Controller
{
  use \OCA\CAFeVDBMembers\Traits\LoggerTrait;
  use \OCA\CAFeVDBMembers\Traits\ResponseTrait;

  /** @var string */
  private $userId;

  /** @var IL10N */
  private $l;

  /** @var EntityManager */
  private $entityManager;

  public function __construct(
    string $appName
    , IRequest $request
    , $userId
    , IL10N $l10n
    , LoggerInterface $logger
    , EntityManager $entityManager
  ) {
    parent::__construct($this->appName, $request);
    $this->userId = $userId;
    $this->l = $l10n;
    $this->logger = $logger;
    $this->entityManager = $entityManager;
  }

  /**
   * @NoAdminRequired
   */
  public function get()
  {
    $musicians = $this->entityManager->getRepository(Entities\Musician::class)->findAll();
    $this->logInfo('NUMBER OF MUSICIANS ' . count($musicians));
    if (count($musicians) == 0) {
      return self::grumble($this->l->t('No member-data found for user-id "%s".', $this->userId));
    } else if (count($musicians) > 1) {
      return self::grumble($this->l_>t('More than one musiciand found for user-id "%s".', $this->userId));
    }
    /** @var Entities\Musician $musician */
    $musician = $musicians[0];
    $this->logInfo('NAME ' . $musician['publicName']);
    return self::dataResponse($musician->toArray());
  }
}
