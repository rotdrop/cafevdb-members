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

use OCA\CAFeVDBMembers\Database\ORM as CAFEVDB;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * SepaDebitMandate
 *
 * @ORM\Table(name="PersonalizedSepaDebitMandatesView")
 * @ORM\Entity
 */
class SepaDebitMandate implements \ArrayAccess
{
  use CAFEVDB\Traits\ArrayTrait;
  use CAFEVDB\Traits\TimestampableEntity;
  use \OCA\CAFeVDBMembers\Traits\DateTimeTrait;
  use CAFEVDB\Traits\SoftDeleteableEntity;

  /**
   * @var Musician
   *
   * @ORM\ManyToOne(targetEntity="Musician", inversedBy="sepaDebitMandates", fetch="EXTRA_LAZY")
   * @ORM\Id
   * _AT_Gedmo\Timestampable(on={"update","change","create","delete"}, field="writtenMandate", timestampField="sepaDebitMandatesChanged")
   */
  private $musician;

  /**
   * @var int
   *
   * This is a POSITIVE per-musician sequence count. It currently is
   * incremented using
   * \OCA\CAFEVDB\Database\Doctrine\ORM\Traits\PerMusicianSequenceTrait
   *
   * @ORM\Column(type="integer")
   * @ORM\Id
   * @ORM\GeneratedValue(strategy="NONE")
   * _AT_ORM\GeneratedValue(strategy="CUSTOM")
   * _AT_ORM\CustomIdGenerator(class="OCA\CAFEVDB\Database\Doctrine\ORM\Mapping\PerMusicianSequenceGenerator")
   */
  private $sequence;

  /**
   * @var SepaBankAccount
   *
   * Debit-mandates can expire, so many debit-mandates may refer the
   * same bank-account.
   *
   * @ORM\ManyToOne(targetEntity="SepaBankAccount", inversedBy="sepaDebitMandates")
   * @ORM\JoinColumns(
   *   @ORM\JoinColumn(name="musician_id", referencedColumnName="musician_id", nullable=false),
   *   @ORM\JoinColumn(name="bank_account_sequence", referencedColumnName="sequence", nullable=false)
   * )
   */
  private $sepaBankAccount;

  /**
   * All debit-mandates are tied to a specific project. The convention
   * is that debit-mandates tied to the member's project are permanent
   * and can be used for all other projects as well. We do not make
   * this field an id as the sequence-id is a running index per
   * musician and joins are more difficult to define.
   *
   * The ProjectPayment entity, e.g., has to reference either a
   * mandate for its own project or a mandate from the member's
   * project.
   *
   * @ORM\ManyToOne(targetEntity="Project", inversedBy="sepaDebitMandates", fetch="EXTRA_LAZY")
   * @ORM\JoinColumns(
   *   @ORM\JoinColumn(name="project_id", referencedColumnName="id", nullable=false)
   * )
   */
  private $project;

  /**
   * @var string
   *
   * @ORM\Column(type="string", length=35, options={"collation"="ascii_general_ci"})
   */
  private $mandateReference;

  /**
   * @var bool
   *
   * @ORM\Column(type="boolean", nullable=false)
   */
  private $nonRecurring;

  /**
   * @var \DateTimeImmutable
   *
   * @ORM\Column(type="date_immutable", nullable=true)
   */
  private $mandateDate;

  /**
   * @var int
   *
   * Pre-notification dead-line in calendar days. Normally 14, may be
   * shorter, e.g. 7 calendar days but at least 5 business days.
   *
   * @ORM\Column(type="integer", options={"default"="14"})
   */
  private $preNotificationCalendarDays = 14;

  /**
   * @var int
   *
   * Pre-notification dead-line in TARGET2 days. Normally unset.
   *
   * @ORM\Column(type="integer", nullable=true)
   */
  private $preNotificationBusinessDays = null;

  /**
   * @var \DateTimeImmutable|null
   *
   * @ORM\Column(type="date_immutable", nullable=true)
   */
  private $lastUsedDate;

  // /**
  //  * @var EncryptedFile
  //  *
  //  * _AT_ORM\OneToOne(targetEntity="EncryptedFile", cascade={"persist"}, orphanRemoval=true)
  //  */
  // private $writtenMandate;

  /**
   * @var ProjectPayment
   *
   * Linke to the payments table.
   *
   * _AT_ORM\OneToMany(targetEntity="CompositePayment",
   *                   mappedBy="sepaDebitMandate",
   *                   fetch="EXTRA_LAZY")
   */
  private $payments;

  // phpcs:disable Squiz.Commenting.FunctionComment.Missing
  public function __construct()
  {
    $this->arrayCTOR();
    $this->payments = new ArrayCollection();
  }
  // phpcs:enable

  /**
   * Set sequence
   *
   * @param null|int $sequence
   *
   * @return SepaDebitMandate
   *
   * @todo Detangle mandate reference generation from setting
   * sequences here. Perhaps a slug-handler ...
   */
  public function setSequence(?int $sequence):SepaDebitMandate
  {
    $this->sequence = $sequence;
    $this->adjustMandateReference();

    return $this;
  }

  /**
   * Get sequence.
   *
   * @return null|int
   */
  public function getSequence():?int
  {
    return $this->sequence;
  }

  /**
   * Set musician or musician-id
   *
   * @param int|Musician $musician
   *
   * @return SepaDebitMandate
   */
  public function setMusician($musician):SepaDebitMandate
  {
    $this->musician = $musician;

    return $this;
  }

  /**
   * Get musician.
   *
   * @return int|Musician
   */
  public function getMusician()
  {
    return $this->musician;
  }

  /**
   * Set sepaBankAccount.
   *
   * @param SepaBankAccount $sepaBankAccount
   *
   * @return SepaDebitMandate
   */
  public function setSepaBankAccount(SepaBankAccount $sepaBankAccount):SepaDebitMandate
  {
    $this->sepaBankAccount = $sepaBankAccount;

    return $this;
  }

  /**
   * Get sepaBankAccount.
   *
   * @return SepaBankAccount
   */
  public function getSepaBankAccount():SepaBankAccount
  {
    return $this->sepaBankAccount;
  }

  /**
   * Set mandateReference.
   *
   * @param string $mandateReference
   *
   * @return SepaDebitMandate
   */
  public function setMandateReference(string $mandateReference)
  {
    $this->mandateReference = $mandateReference;

    return $this;
  }

  /**
   * Get mandateReference.
   *
   * @return null|string
   */
  public function getMandateReference():?string
  {
    return $this->mandateReference;
  }

  /**
   * Set project or project-id
   *
   * @param int|Project $project
   *
   * @return SepaDebitMandate
   */
  public function setProject($project):SepaDebitMandate
  {
    $this->project = $project;

    return $this;
  }

  /**
   * Get project.
   *
   * @return Project|int
   */
  public function getProject()
  {
    return $this->project;
  }

  /**
   * Set mandateDate.
   *
   * @param string|\DateTimeInterface $mandateDate
   *
   * @return SepaDebitMandate
   */
  public function setMandateDate($mandateDate):SepaDebitMandate
  {
    $this->mandateDate = self::convertToDateTime($mandateDate);
    return $this;
  }

  /**
   * Get mandateDate.
   *
   * @return \DateTimeInterface
   */
  public function getMandateDate():\DateTimeInterface
  {
    return $this->mandateDate;
  }

  /**
   * Set preNotificationCalendarDays.
   *
   * @param int $preNotificationCalendarDays
   *
   * @return SepaDebitMandate
   */
  public function setPreNotificationCalendarDays(int $preNotificationCalendarDays):SepaDebitMandate
  {
    $this->preNotificationCalendarDays = $preNotificationCalendarDays;
    return $this;
  }

  /**
   * Get preNotificationCalendarDays.
   *
   * @return int
   */
  public function getPreNotificationCalendarDays():int
  {
    return $this->preNotificationCalendarDays;
  }

  /**
   * Set preNotificationBusinessDays.
   *
   * @param int|null $preNotificationBusinessDays
   *
   * @return SepaDebitMandate
   */
  public function setPreNotificationBusinessDays(?int $preNotificationBusinessDays):SepaDebitMandate
  {
    $this->preNotificationBusinessDays = $preNotificationBusinessDays;

    return $this;
  }

  /**
   * Get preNotificationBusinessDays.
   *
   * @return int|null
   */
  public function getPreNotificationBusinessDays():?int
  {
    return $this->preNotificationBusinessDays;
  }

  /**
   * Set lastUsedDate.
   *
   * @param string|\DateTimeInterface $lastUsedDate
   *
   * @return SepaDebitMandate
   */
  public function setLastUsedDate($lastUsedDate):SepaDebitMandate
  {
    $this->lastUsedDate = self::convertToDateTime($lastUsedDate);
    return $this;
  }

  /**
   * Get lastUsedDate.
   *
   * @return \DateTimeInterface
   */
  public function getLastUsedDate():?\DateTimeInterface
  {
    return $this->lastUsedDate;
  }

  /**
   * Set nonRecurring.
   *
   * @param bool $nonRecurring
   *
   * @return SepaDebitMandate
   */
  public function setNonRecurring(bool $nonRecurring):SepaDebitMandate
  {
    $this->nonRecurring = $nonRecurring;

    return $this;
  }

  /**
   * Get nonRecurring.
   *
   * @return bool
   */
  public function getNonRecurring():bool
  {
    return $this->nonRecurring;
  }

  /**
   * Set payments.
   *
   * @param Collection $payments
   *
   * @return SepaDebitMandate
   */
  public function setPayments(Collection $payments):SepaDebitMandate
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

  // /**
  //  * Set writtenMandate.
  //  *
  //  * @param null|EncryptedFile $writtenMandate
  //  *
  //  * @return SepaDebitMandate
  //  */
  // public function setWrittenMandate(?EncryptedFile $writtenMandate):SepaDebitMandate
  // {
  //   $this->writtenMandate = $writtenMandate;

  //   return $this;
  // }

  // /**
  //  * Get writtenMandate.
  //  *
  //  * @return null|EncryptedFile
  //  */
  // public function getWrittenMandate():?EncryptedFile
  // {
  //   return $this->writtenMandate;
  // }

  // /**
  //  * Return the number of payments attached to this entity.
  //  *
  //  * @return int
  //  */
  // public function usage():int
  // {
  //   return $this->payments->count();
  // }
}
