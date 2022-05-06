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
      return self::grumble($this->l_>t('More than one musician found for user-id "%s".', $this->userId));
    }
    /** @var Entities\Musician $musician */
    $musician = $musicians[0];

    $this->logInfo('NAME ' . $musician->getPublicName() . ' #Instruments ' . $musician->getInstruments()->count());
    $musicianData = $musician->toArray();

    $musicianData['personalPublicName'] = $musician->getPublicName(firstNameFirst: true);
    $musicianData['instruments'] = [];
    /** @var Entities\Instrument $instrument */
    /** @var Entities\MusicianInstrument $musicianInstrument */
    foreach ($musician->getInstruments() as $musicianInstrument) {
      $instrument = $musicianInstrument->getInstrument();
      $flatInstrument = $instrument->toArray();
      unset($flatInstrument['musicianInstruments']);
      $flatInstrument['ranking'] = $musicianInstrument->getRanking();
      $flatInstrument['families'] = [];
      foreach ($instrument->getFamilies() as $family) {
        $flatFamily = $family->toArray();
        unset($flatFamily['instruments']);
        $flatInstrument['families'][] = $flatFamily;
      }
      $musicianData['instruments'][] = $flatInstrument;
    }

    /** @var Entities\SepaBankAccount $bankAccount */
    $musicianData['sepaBankAccounts'] = [];
    unset($musicianData['sepaDebitMandates']);
    foreach ($musician->getSepaBankAccounts() as $bankAccount) {
      $flatBankAccount = $bankAccount->toArray();
      unset($flatBankAccount['musician']);
      $flatBankAccount['musicianId'] = $musician->getId();
      $flatBankAccount['sepaDebitMandates'] = [];
      /** @var Entities\SepaDebitMandate $debitMandate */
      foreach ($bankAccount->getSepaDebitMandates() as $debitMandate) {
        $flatDebitMandate = $debitMandate->toArray();
        unset($flatDebitMandate['sepaBankAccount']);
        $flatDebitMandate['bankAccountSequence'] = $bankAccount->getSequence();
        unset($flatDebitMandate['musician']);
        $flatDebitMandate['musicianId'] = $musician->getId();
        $flatDebitMandate['project'] = $debitMandate->getProject()->toArray();
        $flatBankAccount['sepaDebitMandates'][] = $flatDebitMandate;
      }
      $musicianData['sepaBankAccounts'][] = $flatBankAccount;
    }

    /** @var Entities\ProjectParticipant $participant */
    $musicianData['projectParticipation'] = [];
    foreach ($musician->getProjectParticipation() as $participant) {
      $this->logInfo('PROJECT: ' . $participant->getProject()->getName());
      $flatParticipant = $participant->toArray();
      unset($flatParticipant['musician']);
      $flatParticipant['musicianId'] = $musician->getId();
      $flatParticipant['project'] = $participant->getProject()->toArray();
      unset($flatParticipant['musicianInstruments']);
      unset($flatParticipant['sepaBankAccount']);
      unset($flatParticipant['sepaDebitMandate']);

      /** @var Entities\ProjectInstrument $projectInstrument */
      $flatParticipant['projectInstruments'] = [];
      foreach ($participant->getProjectInstruments() as $projectInstrument) {
        $instrument = $projectInstrument->getInstrument();
        $flatInstrument = $instrument->toArray();
        unset($flatInstrument['projectInstruments']);
        $flatInstrument['voice'] = $projectInstrument->getVoice();
        $flatInstrument['sectionLeader'] = $projectInstrument->getSectionLeader();
        // unset most of the instrument, too much Data
        unset($flatInstrument['families']);
        unset($flatInstrument['sortOrder']);
        unset($flatInstrument['deleted']);
        unset($flatInstrument['musicianInstruments']);

        $flatParticipant['projectInstruments'][] = $flatInstrument;
      }
      $musicianData['projectParticipation'][] = $flatParticipant;
    }

    usort($musicianData['projectParticipation'], function($pp1, $pp2) {
      $p1 = $pp1['project'];
      $p2 = $pp2['project'];
      $t1 = $p1['type'];
      $t2 = $p2['type'];
      if ($t1 == $t2) {
        $y1 = $p1['year'];
        $y2 = $p2['year'];
        if ($y1 == $y2) {
          return strcmp($p1['name'], $p2['name']);
        } else {
          return $y2 < $y1 ? -1 : 1;
        }
      } else {
        if ($t1 == 'template') {
          return 1;
        } else if ($t1 == 'permanent') {
          return -1;
        } else {
          // $t1 == 'temporary'
          if ($t2 == 'template') {
            return -1;
          } else {
            // $t2 == 'permanent'
            return 1;
          }
        }
      }
    });

    return self::dataResponse($musicianData);
  }
}
