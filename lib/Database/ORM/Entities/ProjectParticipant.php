<?php
/**
 * Member's data base connector for CAFEVDB orchetra management app.
 *
 * @author Claus-Justus Heine <himself@claus-justus-heine.de>
 * @copyright Copyright (c) 2022, 2023, 2023 Claus-Justus Heine
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

namespace OCA\CAFeVDBMembers\Database\ORM\Entities;

use OCA\CAFeVDBMembers\Database\ORM as CAFEVDB;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Entity for project participants.
 *
 * @ORM\Table(name="PersonalizedProjectParticipantsView")
 * @ORM\Entity
 */
class ProjectParticipant implements \ArrayAccess
{
  use CAFEVDB\Traits\ArrayTrait;
  use CAFEVDB\Traits\TimestampableEntity;
  use \OCA\CAFeVDBMembers\Toolkit\Traits\DateTimeTrait;
  use CAFEVDB\Traits\SoftDeleteableEntity;

  /**
   * @ORM\ManyToOne(targetEntity="Project", inversedBy="participants", fetch="EXTRA_LAZY")
   * @ORM\Id
   */
  private $project;

  /**
   * @ORM\ManyToOne(targetEntity="Musician", inversedBy="projectParticipation", fetch="EXTRA_LAZY")
   * @ORM\Id
   */
  private $musician;

  /**
   * @var bool
   *
   * @ORM\Column(type="boolean", nullable=true, options={"default"="0", "comment"="Participant has confirmed the registration."})
   */
  private $registration = '0';

  /**
   * Link to payments
   *
   * @ORM\OneToMany(targetEntity="ProjectPayment", mappedBy="projectParticipant")
   */
  private $payments;

  /**
   * Link to extra fields data
   *
   * @ORM\OneToMany(targetEntity="ProjectParticipantFieldDatum", indexBy="option_key", mappedBy="projectParticipant", fetch="EXTRA_LAZY")
   */
  private $participantFieldsData;

  // /**
  //  * @var \DateTimeImmutable
  //  *
  //  * Tracks changes in the fields data, in particular to catch deleted files
  //  * for the database file-space.
  //  *
  //  * @ORM\Column(type="datetime_immutable", nullable=true)
  //  */
  // private $participantFieldsDataChanged;

  /**
   * Link in the project instruments, may be more than one per participant.
   *
   * @ORM\OneToMany(targetEntity="ProjectInstrument", mappedBy="projectParticipant")
   */
  private $projectInstruments;

  // phpcs:disable Squiz.Commenting.FunctionComment.Missing
  public function __construct()
  {
    $this->arrayCTOR();
    $this->payments = new ArrayCollection();
    $this->participantFieldsData = new ArrayCollection();
    $this->projectInstruments = new ArrayCollection();
  }
  // phpcs:enable

  /**
   * Set project.
   *
   * @param null|Project $project
   *
   * @return ProjectParticipant
   */
  public function setProject(?Project $project):ProjectParticipant
  {
    $this->project = $project;

    return $this;
  }

  /**
   * Get project.
   *
   * @return Project
   */
  public function getProject():?Project
  {
    return $this->project;
  }

  /**
   * Set musician.
   *
   * @param null|Musician $musician
   *
   * @return ProjectParticipant
   */
  public function setMusician(?Musician $musician):ProjectParticipant
  {
    $this->musician = $musician;

    return $this;
  }

  /**
   * Get musician.
   *
   * @return Musician
   */
  public function getMusician():?Musician
  {
    return $this->musician;
  }

  /**
   * Set registration.
   *
   * @param bool $registration
   *
   * @return ProjectParticipant
   */
  public function setRegistration(bool $registration):ProjectParticipant
  {
    $this->registration = $registration;

    return $this;
  }

  /**
   * Get registration.
   *
   * @return bool
   */
  public function getRegistration()
  {
    return $this->registration;
  }

  /**
   * Set projectInstruments.
   *
   * @param Collection $projectInstruments
   *
   * @return ProjectParticipant
   */
  public function setProjectInstruments(Collection $projectInstruments):ProjectParticipant
  {
    $this->projectInstruments = $projectInstruments;

    return $this;
  }

  /**
   * Get projectInstruments.
   *
   * @return Collection
   */
  public function getProjectInstruments():Collection
  {
    return $this->projectInstruments;
  }

  /**
   * Set participantFieldsData.
   *
   * @param Collection $participantFieldsData
   *
   * @return ProjectParticipant
   */
  public function setParticipantFieldsData(Collection $participantFieldsData):ProjectParticipant
  {
    $this->participantFieldsData = $participantFieldsData;

    return $this;
  }

  /**
   * Get participantFieldsData.
   *
   * @return Collection
   */
  public function getParticipantFieldsData():Collection
  {
    return $this->participantFieldsData;
  }

  // /**
  //  * Get participantFieldsDataChanged.
  //  *
  //  * @return Collection
  //  */
  // public function getParticipantFieldsDataChanged():?\DateTimeInterface
  // {
  //   return $this->participantFieldsDataChanged;
  // }

  /**
   * Get one specific participant-field datum indexed by its key
   *
   * @param mixed $key Everything which can be converted to an UUID by
   * Uuid::asUuid().
   *
   * @return null|ProjectParticipantFieldDatum
   */
  public function getParticipantFieldsDatum(mixed $key):?ProjectParticipantFieldDatum
  {
    return $this->getByUuid($this->participantFieldsData, $key, 'optionKey');
  }

  /**
   * Set payments.
   *
   * @param Collection $payments
   *
   * @return ProjectParticipant
   */
  public function setPayments(Collection $payments):ProjectParticipant
  {
    $this->payments = $payments;

    return $this;
  }

  /**
   * Get payments.
   *
   * @return Collection
   */
  public function getPayments():Collection
  {
    return $this->payments;
  }

  /**
   * Set sepaBankAccount.
   *
   * @param null|SepaBankAccountl $sepaBankAccount
   *
   * @return ProjectParticipant
   */
  public function setSepaBankAccount(?SepaBankAccount $sepaBankAccount):ProjectParticipant
  {
    $this->sepaBankAccount = $sepaBankAccount;

    return $this;
  }

  /**
   * Get sepaBankAccount.
   *
   * @return SepaBankAccount|null
   */
  public function getSepaBankAccount():?SepaBankAccount
  {
    return $this->sepaBankAccount;
  }

  /**
   * Set sepaDebitMandate.
   *
   * @param null|SepaDebitMandate $sepaDebitMandate
   *
   * @return ProjectParticipant
   */
  public function setSepaDebitMandate(?SepaDebitMandate $sepaDebitMandate):ProjectParticipant
  {
    $this->sepaDebitMandate = $sepaDebitMandate;

    return $this;
  }

  /**
   * Get sepaDebitMandate.
   *
   * @return SepaDebitMandate|null
   */
  public function getSepaDebitMandate():?SepaDebitMandate
  {
    return $this->sepaDebitMandate;
  }

  // /**
  //  * Return the number of "serious" items which "use" this entity. For
  //  * project participant this is (for now) the number of payments. In
  //  * the long run: only open payments/receivables should count.
  //  */
  // public function usage():int
  // {
  //   return $this->payments->count();
  // }
}
