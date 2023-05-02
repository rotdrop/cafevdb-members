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

namespace OCA\CAFeVDBMembers\Database\ORM\Entities;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;

use OCA\CAFeVDBMembers\Database\ORM as CAFEVDB;
use OCA\CAFeVDBMembers\Database\DBAL\Types;

/**
 * Projects
 *
 * @ORM\Table(name="PersonalizedProjectsView")
 * @ORM\Entity
 */
class Project implements \ArrayAccess
{
  use CAFEVDB\Traits\ArrayTrait;
  use CAFEVDB\Traits\TimestampableEntity;
  use CAFEVDB\Traits\SoftDeleteableEntity;

  /**
   * @var int
   *
   * @ORM\Column(type="integer", nullable=false)
   * @ORM\Id
   * @ORM\GeneratedValue(strategy="IDENTITY")
   */
  private $id;

  /**
   * @var int
   *
   * @ORM\Column(type="integer", nullable=false, options={"unsigned"=true})
   */
  private $year;

  /**
   * @var string
   *
   * @ORM\Column(type="string", length=64, nullable=false)
   */
  private $name;

  /**
   * @var Types\EnumProjectTemporalType
   *
   * @ORM\Column(type="EnumProjectTemporalType", nullable=false)
   */
  private $type = 'temporary';

  /**
   * @var \DateTimeImmutable
   *
   * Optional registration deadline. If null then the date one day before the
   * first rehearsal is used, if set. Otherwise no registration dead-line is
   * imposed.
   *
   * @ORM\Column(type="date_immutable", nullable=true)
   */
  private $registrationDeadline;

  /**
   * @var bool
   *
   * @ORM\Column(type="boolean")
   */
  private $clubMembers;

  /**
   * @var bool
   *
   * @ORM\Column(type="boolean")
   */
  private $executiveBoard;

  /**
   * @ORM\OneToMany(targetEntity="ProjectParticipant", mappedBy="project")
   */
  private $participants;

  /**
   * @ORM\OneToMany(targetEntity="ProjectParticipantField", mappedBy="project", indexBy="id", fetch="EXTRA_LAZY")
   * @ORM\OrderBy({"displayOrder" = "DESC"})
   */
  private $participantFields;

  /**
   * @ORM\OneToMany(targetEntity="ProjectParticipantFieldDatum", mappedBy="project", fetch="EXTRA_LAZY")
   */
  private $participantFieldsData;

  /**
   * @ORM\OneToMany(targetEntity="SepaDebitMandate", mappedBy="project")
   */
  private $sepaDebitMandates;

  /**
   * @ORM\OneToMany(targetEntity="ProjectPayment", mappedBy="project")
   */
  private $payments;

  // phpcs:disable Squiz.Commenting.FunctionComment.Missing
  public function __construct()
  {
    $this->arrayCTOR();
    $this->participants = new ArrayCollection();
    $this->participantFields = new ArrayCollection();
    $this->participantFieldsData = new ArrayCollection();
    $this->sepaDebitMandates = new ArrayCollection();
    $this->payments = new ArrayCollection();
  }
  // phpcs:enable

  /**
   * Set id.
   *
   * @param int $id
   *
   * @return Project
   */
  public function setId(int $id):Project
  {
    $this->id = $id;

    return $this;
  }

  /**
   * Get id.
   *
   * @return int
   */
  public function getId()
  {
    return $this->id;
  }

  /**
   * Set year.
   *
   * @param null|int $year
   *
   * @return Project
   */
  public function setYear(?int $year):Project
  {
    $this->year = $year;

    return $this;
  }

  /**
   * Get year.
   *
   * @return int
   */
  public function getYear()
  {
    return $this->year;
  }

  /**
   * Set name.
   *
   * @param null|string $name
   *
   * @return Project
   */
  public function setName(?string $name):Project
  {
    $this->name = $name;

    return $this;
  }

  /**
   * Get name.
   *
   * @return string
   */
  public function getName()
  {
    return $this->name;
  }

  /**
   * Set type.
   *
   * @param EnumProjectTemporalType|string $type
   *
   * @return Project
   */
  public function setType($type):Project
  {
    $this->type = new Types\EnumProjectTemporalType($type);

    return $this;
  }

  /**
   * Get type.
   *
   * @return EnumProjectTemporalType
   */
  public function getType():Types\EnumProjectTemporalType
  {
    return $this->type;
  }

  /**
   * Set participants.
   *
   * @param Collection $participants
   *
   * @return Project
   */
  public function setParticipants(Collection $participants):Project
  {
    $this->participants = $participants;

    return $this;
  }

  /**
   * Get participants.
   *
   * @return Collection
   */
  public function getParticipants():Collection
  {
    return $this->participants;
  }

  /**
   * Set participantFields.
   *
   * @param Collection $participantFields
   *
   * @return Project
   */
  public function setParticipantFields(Collection $participantFields):Project
  {
    $this->participantFields = $participantFields;

    return $this;
  }

  /**
   * Get participantFields.
   *
   * @return Collection
   */
  public function getParticipantFields():Collection
  {
    return $this->participantFields;
  }

  /**
   * Set participantFieldsData.
   *
   * @param Collection $participantFieldsData
   *
   * @return Project
   */
  public function setParticipantFieldsData(Collection $participantFieldsData):Project
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

  /**
   * Set payments.
   *
   * @param ArrayCollection $payments
   *
   * @return Project
   */
  public function setPayments(Collection $payments):Project
  {
    $this->payments = $payments;

    return $this;
  }

  /**
   * Get payments.
   *
   * @return ArrayCollection
   */
  public function getPayments():Collection
  {
    return $this->payments;
  }

  /**
   * Set sepaDebitMandates.
   *
   * @param Collection $sepaDebitMandates
   *
   * @return Project
   */
  public function setSepaDebitMandates(Collection $sepaDebitMandates):Project
  {
    $this->sepaDebitMandates = $sepaDebitMandates;

    return $this;
  }

  /**
   * Get sepaDebitMandates.
   *
   * @return Collection
   */
  public function getSepaDebitMandates():Collection
  {
    return $this->sepaDebitMandates;
  }

  /**
   * Set clubMembers.
   *
   * @param bool $clubMembers
   *
   * @return Project
   */
  public function setClubMembers(bool $clubMembers):Project
  {
    $this->clubMembers = $clubMembers;

    return $this;
  }

  /**
   * Get clubMembers.
   *
   * @return bool
   */
  public function getClubMembers():bool
  {
    return $this->clubMembers;
  }

  /**
   * Set executiveBoard.
   *
   * @param bool $executiveBoard
   *
   * @return Project
   */
  public function setExecutiveBoard(bool $executiveBoard):Project
  {
    $this->executiveBoard = $executiveBoard;

    return $this;
  }

  /**
   * Get executiveBoard.
   *
   * @return bool
   */
  public function getExecutiveBoard():bool
  {
    return !empty($this->executiveBoard);
  }

  /**
   * Sets registrationDeadline.
   *
   * @param string|int|DateTimeInterface $registrationDeadline
   *
   * @return Project
   */
  public function setRegistrationDeadline(mixed $registrationDeadline):Project
  {
    $this->registrationDeadline = self::convertToDateTime($registrationDeadline);
    return $this;
  }

  /**
   * Returns registrationDeadline.
   *
   * @return DateTimeImmutable
   */
  public function getRegistrationDeadline():?DateTimeInterface
  {
    return $this->registrationDeadline;
  }
}
