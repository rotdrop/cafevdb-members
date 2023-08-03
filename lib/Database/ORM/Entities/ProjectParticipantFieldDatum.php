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

use RuntimeException;

use OCA\CAFeVDBMembers\Database\ORM as CAFEVDB;
use OCA\CAFeVDBMembers\Utils\Uuid;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * ProjectParticipantFieldsData
 *
 * @ORM\Table(name="PersonalizedProjectParticipantFieldsDataView")
 * @ORM\Entity
 */
class ProjectParticipantFieldDatum implements \ArrayAccess
{
  use CAFEVDB\Traits\ArrayTrait;
  use CAFEVDB\Traits\TimestampableEntity;
  use CAFEVDB\Traits\SoftDeleteableEntity;

  const PAYMENT_REFERENCE_SEPARATOR = ': ';

  /**
   * @var ProjectParticipantField
   *
   * @ORM\ManyToOne(targetEntity="ProjectParticipantField", inversedBy="fieldData", fetch="EXTRA_LAZY")
   * @ORM\Id
   */
  private $field;

  /**
   * @var Project
   *
   * @ORM\ManyToOne(targetEntity="Project", inversedBy="participantFieldsData", fetch="EXTRA_LAZY")
   * @ORM\Id
   */
  private $project;

  /**
   * @var Musician
   *
   * @ORM\ManyToOne(targetEntity="Musician", inversedBy="projectParticipantFieldsData", fetch="EXTRA_LAZY")
   * @ORM\Id
   */
  private $musician;

  /**
   * @var \Ramsey\Uuid\UuidInterface
   *
   * @ORM\Column(type="uuid_binary")
   * @ORM\Id
   */
  private $optionKey;

  /**
   * @var string
   *
   * @ORM\Column(type="text", length=16777215, nullable=true, options={"default"=null})
   */
  private $optionValue = null;

  /**
   * @var float
   * Optional value of a deposit for monetary options. This is unused if
   * the deposit is fixed by single- or multi-select options.
   *
   * @ORM\Column(type="float", nullable=true)
   */
  private $deposit;

  /**
   * @var ProjectParticipantFieldDataOption
   *
   * @ORM\ManyToOne(targetEntity="ProjectParticipantFieldDataOption", inversedBy="fieldData", fetch="EXTRA_LAZY")
   * @ORM\JoinColumns(
   *   @ORM\JoinColumn(name="field_id", referencedColumnName="field_id"),
   *   @ORM\JoinColumn(name="option_key", referencedColumnName="key")
   * )
   */
  private $dataOption;

  /**
   * @var ProjectParticipant
   *
   * @ORM\ManyToOne(targetEntity="ProjectParticipant", inversedBy="participantFieldsData", fetch="EXTRA_LAZY")
   * @ORM\JoinColumns(
   *   @ORM\JoinColumn(name="project_id", referencedColumnName="project_id"),
   *   @ORM\JoinColumn(name="musician_id", referencedColumnName="musician_id")
   * )
   */
  private $projectParticipant;

  /**
   * @var ProjectPayment
   *
   * @ORM\OneToMany(targetEntity="ProjectPayment", mappedBy="receivable")
   */
  private $payments;

  /**
   * @var DatabaseStorageFile
   *
   * Optional. ATM only used for particular auto-generated monetary fields.
   *
   * @ORM\OneToOne(targetEntity="DatabaseStorageFile", fetch="EXTRA_LAZY")
   */
  private $supportingDocument;

  // phpcs:disable Squiz.Commenting.FunctionComment.Missing
  public function __construct()
  {
    $this->arrayCTOR();
    $this->payments = new ArrayCollection();
  }
  // phpcs:enable

  /**
   * Get project.
   *
   * @return Project
   */
  public function getProject():Project
  {
    return $this->project;
  }

  /**
   * Get musician.
   *
   * @return Musician
   */
  public function getMusician():Musician
  {
    return $this->musician;
  }

  /**
   * Get projectParticipant.
   *
   * @return ProjectParticipant
   */
  public function getProjectParticipant():ProjectParticipant
  {
    return $this->projectParticipant;
  }

  /**
   * Get field.
   *
   * @return ProjectParticipantField
   */
  public function getField():ProjectParticipantField
  {
    return $this->field;
  }

  /**
   * Get dataOption.
   *
   * @return ProjectParticipantFieldDataOption
   */
  public function getDataOption():ProjectParticipantFieldDataOption
  {
    return $this->dataOption;
  }

  /**
   * Get optionValue.
   *
   * @return null|string
   */
  public function getOptionValue():?string
  {
    return $this->optionValue;
  }

  /**
   * Get optionKey.
   *
   * @return UuidInterface
   */
  public function getOptionKey()
  {
    return $this->optionKey;
  }

  /**
   * Get deposit.
   *
   * @return null|float
   */
  public function getDeposit():?float
  {
    return $this->deposit;
  }

  /**
   * Get supportingDocument.
   *
   * @return null|DatabaseStorageFile
   */
  public function getSupportingDocument():?DatabaseStorageFile
  {
    return $this->supportingDocument;
  }

  /**
   * Get payments.
   *
   * @return Payments
   */
  public function getPayments():?Collection
  {
    return $this->payments;
  }

  /**
   * The amount to pay for this service-fee option.
   *
   * Only meaningful if
   * ProjectParticipantFieldDatum::getField()::getDataType() equals
   * DataType::RECEIVABLES, DataType::LIABILITIES.
   *
   * For DataType::LIABILITIES the amount is negated.
   *
   * @return float
   */
  public function amountPayable():float
  {
    $value = 0.0;
    switch ($this->field->getMultiplicity()) {
      case Multiplicity::SINGLE():
      case Multiplicity::MULTIPLE():
      case Multiplicity::PARALLEL():
      case Multiplicity::GROUPSOFPEOPLE():
        $value = filter_var($this->dataOption->getData(), FILTER_VALIDATE_FLOAT);
        if ($value === false) {
          throw new RuntimeException('Stored value cannot be converted to float.');
        }
        break;
      case Multiplicity::GROUPOFPEOPLE():
        // value in management option of $field
        $managementOption = $this->field->getManagementOption();
        if (empty($managementOption)) {
          throw new RuntimeException('Unable to access management option for obtaining the field value.');
        }
        $value = filter_var($managementOption->getData(), FILTER_VALIDATE_FLOAT);
        if ($value === false) {
          throw new RuntimeException('Stored value cannot be converted to float.');
        }
        break;
      case Multiplicity::SIMPLE():
      case Multiplicity::RECURRING():
        if (!empty($this->optionValue)) {
          $value = filter_var($this->optionValue, FILTER_VALIDATE_FLOAT);
          if ($value === false) {
            throw new RuntimeException('Stored value cannot be converted to float: ' . (string)$this->optionValue);
          }
        }
        break;
      default:
        throw new RuntimeException('Unhandled multiplicity tag: '.(string)$this->field->getMultiplicity());
    }
    if ($this->field->getDataType() == DataType::LIABILITIES) {
      $value = -$value;
    }
    return $value;
  }

  /**
   * The height of the deposit to pay, if any.
   *
   * Only meaningful if
   * ProjectParticipantFieldDatum::getField()::getDataType() equals
   * DataType::RECEIVABLES, DataType::LIABILITIES.
   *
   * For DataType::LIABILITIES the amount is negated.
   *
   * @return null|float
   */
  public function depositAmount():?float
  {
    $value = null;
    switch ($this->field->getMultiplicity()) {
      case Multiplicity::SINGLE():
      case Multiplicity::MULTIPLE():
      case Multiplicity::PARALLEL():
      case Multiplicity::GROUPSOFPEOPLE():
        $value = $this->dataOption->getDeposit();
        break;
      case Multiplicity::GROUPOFPEOPLE():
        // value in management option of $field
        $managementOption = $this->field->getManagementOption();
        if (empty($managementOption)) {
          throw new RuntimeException('Unable to access management option for obtaining the field value.');
        }
        $value = $managementOption->getDeposit();
        break;
      case Multiplicity::SIMPLE():
        $value = $this->getDeposit();
        break;
      case Multiplicity::RECURRING():
        break;
      default:
        throw new RuntimeException('Unhandled multiplicity tag: '.(string)$this->field->getMultiplicity());
    }
    if ($value !== null && $this->field->getDataType() == DataType::LIABILITIES) {
      $value = -$value;
    }
    return $value;
  }

  /**
   * The amount already paid as stored in the ProjectPayment entities.
   *
   * Only meaningful if
   * ProjectParticipantFieldDatum::getField()::getDataType() is
   * 'service-fee'.
   *
   * @return float
   */
  public function amountPaid():float
  {
    // sum up the values of all related payments
    $amount = 0.0;
    /** @var ProjectPayment $payment */
    foreach ($this->payments as $payment) {
      $amount += $payment->getAmount();
    }
    return $amount;
  }

  /**
   * Suggestion for a reference field for debit notes or money
   * transfers. Constructed from the labels of the associated
   * ProjectParticipantField and ProjectParticipantFieldDataOption
   * entities.
   *
   * @return string
   */
  public function paymentReference():string
  {
    // construct something nice from the various label fields:
    // - name of ProjectParticipantField
    // - label of ProjectParticipantFieldDataOption
    $fieldName = $this->field->getName();
    $optionLabel = $this->dataOption->getLabel();
    if (empty($fieldName)) {
      return $optionLabel;
    }
    if (empty($optionLabel) || $optionLabel === 'null') {
      return $fieldName;
    }
    return $fieldName.self::PAYMENT_REFERENCE_SEPARATOR.$optionLabel;
  }

  /**
   * Return the number of linked ProjectPayment entities.
   *
   * @return int
   */
  public function usage():int
  {
    return $this->payments->count();
  }

  /**
   * Return the effective option value, either by fetching it from the
   * option or from the own value field. This will not retrieve
   * referenced objects like files or collections of people.
   *
   * @return string
   */
  public function getEffectiveValue()
  {
    switch ($this->field->getMultiplicity()) {
      case Multiplicity::SIMPLE():
      case Multiplicity::RECURRING():
        return $this->optionValue;
        break;
      case Multiplicity::GROUPOFPEOPLE():
      case Multiplicity::GROUPSOFPEOPLE():
      case Multiplicity::MULTIPLE():
      case Multiplicity::SINGLE():
        return $this->dataOption->getData();
        break;
      case Multiplicity::PARALLEL():
        if ($this->field->getDataType() == DataType::CLOUD_FILE
            || $this->field->getDataType() == DataType::DB_FILE) {
          return $this->optionValue;
        } else {
          return $this->dataOption->getData();
        }
        break;
    }
    // perhaps this should throw ...
    return null;
  }

  /**
   * Return the effective deposit value depending on the
   * field-multiplicity.
   *
   * @return null|float
   */
  public function getEffectiveDeposit():?float
  {
    if ($this->field->getDataType() != DataType::SERVICE_FEE) {
      return null;
    }
    switch ($this->field->getMultiplicity()) {
      case Multiplicity::RECURRING():
        return null; // regardless of data-base storage
      case Multiplicity::SIMPLE():
        return $this->deposit;
      case Multiplicity::GROUPOFPEOPLE():
      case Multiplicity::GROUPSOFPEOPLE():
      case Multiplicity::MULTIPLE():
      case Multiplicity::SINGLE():
      case Multiplicity::PARALLEL():
        return $this->dataOption->getDeposit();
      default:
        return null;
    }
  }
}
