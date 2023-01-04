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
use Gedmo\Mapping\Annotation as Gedmo;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;

use OCA\CAFeVDBMembers\Database\ORM as CAFEVDB;
use OCA\CAFeVDBMembers\Utils\Uuid;
use OCA\CAFeVDBMembers\Database\DBAL\Types;

/**
 * ProjectParticipantFields
 *
 * @ORM\Table(name="PersonalizedProjectParticipantFieldsView")
 * @ORM\Entity
 * @Gedmo\TranslationEntity(class="TableFieldTranslation")
 */
class ProjectParticipantField implements \ArrayAccess
{
  use CAFEVDB\Traits\ArrayTrait;
  use \OCA\CAFeVDBMembers\Traits\DateTimeTrait;
  use CAFEVDB\Traits\SoftDeleteableEntity;
  use CAFEVDB\Traits\GetByUuidTrait;
  use CAFEVDB\Traits\TranslatableTrait;

  /**
   * @var int
   *
   * @ORM\Column(type="integer")
   * @ORM\Id
   * @ORM\GeneratedValue
   */
  private $id;

  /**
   * @var Project
   *
   * @ORM\ManyToOne(targetEntity="Project", inversedBy="participantFields", fetch="EXTRA_LAZY")
   * @ORM\JoinColumn(nullable=false)
   */
  private $project;

  /**
   * @var string
   *
   * @Gedmo\Translatable(untranslated="untranslatedName")
   * @ORM\Column(type="string", length=128, nullable=false)
   */
  private $name;

  /**
   * @var string
   *
   * Untranslated variant of self:$name, filled automatically by
   * Gedmo\Translatable
   */
  private $untranslatedName;

  /**
   * @var Types\EnumParticipantFieldMultiplicity
   *
   * @ORM\Column(type="EnumParticipantFieldMultiplicity", nullable=false)
   */
  private $multiplicity;

  /**
   * @var EnumParticipantFieldDataType
   *
   * @ORM\Column(type="EnumParticipantFieldDataType", nullable=false, options={"default"="text"})
   */
  private $dataType = 'text';

  /**
   * @var Collection
   *
   * @ORM\OneToMany(targetEntity="ProjectParticipantFieldDataOption", mappedBy="field", indexBy="key")
   * @ORM\OrderBy({"label" = "ASC", "key" = "ASC"})
   */
  private $dataOptions;

  /**
   * @var \DateTimeImmutable
   *
   * @ORM\Column(type="date_immutable", nullable=true, options={"comment"="Due-date for financial fields."})
   */
  private $dueDate = null;

  /**
   * @var \DateTimeImmutable
   *
   * @ORM\Column(type="date_immutable", nullable=true, options={"comment"="Due-date of deposit for financial fields."})
   */
  private $depositDueDate = null;

  /**
   * @var null|ProjectParticipantFieldDataOption
   *
   * @ORM\OneToOne(targetEntity="ProjectParticipantFieldDataOption")
   * @ORM\JoinColumns(
   *   @ORM\JoinColumn(name="id", referencedColumnName="field_id"),
   *   @ORM\JoinColumn(name="default_value", referencedColumnName="key", nullable=true)
   * )
   */
  private $defaultValue = null;

  /**
   * @var string
   *
   * @Gedmo\Translatable
   * @ORM\Column(type="string", length=4096, nullable=true)
   */
  private $tooltip = null;

  /**
   * @var string
   *
   * @Gedmo\Translatable(untranslated="untranslatedTab")
   * @ORM\Column(type="string", length=256, nullable=true, options={"comment"="Tab to display the field in. If empty, then the project tab is used."})
   */
  private $tab = null;

  /**
   * @var string
   *
   * Untranslated variant of self:$tab, filled automatically by
   * Gedmo\Translatable
   */
  private $untranslatedTab;

  /**
   * @var int|null
   *
   * @ORM\Column(type="integer", nullable=true)
   */
  private $displayOrder = null;

  /**
   * @var bool|null
   *
   * @ORM\Column(type="boolean", nullable=true, options={"default"="0"})
   */
  private $encrypted = false;

  /**
   * @var string|null
   *
   * @ORM\Column(type="string", length=1024, nullable=true, options={"comment"="If non-empty restrict the visbility to this comma separated list of user-groups."})
   */
  private $readers = null;

  /**
   * @var string|null
   *
   * @ORM\Column(type="string", length=1024, nullable=true, options={"comment"="Empty or comma separated list of groups allowed to change the field."})
   */
  private $writers = null;

  /**
   * @ORM\OneToMany(targetEntity="ProjectParticipantFieldDatum", mappedBy="field", fetch="EXTRA_LAZY")
   */
  private $fieldData;

  // phpcs:disable Squiz.Commenting.FunctionComment.Missing
  public function __construct()
  {
    $this->__wakeup();
    $this->id = null;
    $this->project = null;
    $this->defaultValue = null;
    $this->fieldData = new ArrayCollection();
    $this->dataOptions = new ArrayCollection();
  }
  // phpcs:enable

  /** {@inheritdoc} */
  public function __clone()
  {
    if (!$this->id) {
      return;
    }
    $oldDataOptions = $this->dataOptions;
    $oldDefaultValue = $this->defaultValue;
    $this->__construct();
    foreach ($oldDataOptions as $oldDataOption) {
      $dataOption = clone $oldDataOption;
      $dataOption->setField($this);
      $this->dataOptions->add($dataOption);
      if ($oldDataOption == $oldDefaultValue) {
        $this->defaultValue = $dataOption;
      }
    }
  }

  /** {@inheritdoc} */
  public function __wakeup()
  {
    $this->arrayCTOR();
  }

  /**
   * Get id.
   *
   * @return null|int
   */
  public function getId():?int
  {
    return $this->id;
  }

  /**
   * Set id.
   *
   * @param int $id
   *
   * @return ProjectParticipantField
   */
  public function setId(int $id):ProjectParticipantField
  {
    $this->id = $id;

    return $this;
  }

  /**
   * Set project.
   *
   * @param null|Project $project
   *
   * @return ProjectParticipantField
   */
  public function setProject(?Project $project):ProjectParticipantField
  {
    $this->project = $project;

    return $this;
  }

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
   * Set dataOption.
   *
   * @param Collection $dataOptions
   *
   * @return ProjectParticipantField
   */
  public function setDataOptions(Collection $dataOptions):ProjectParticipantField
  {
    $this->dataOptions = $dataOptions;

    return $this;
  }

  /**
   * Get dataOption.
   *
   * @return Collection
   */
  public function getDataOptions():Collection
  {
    return $this->dataOptions;
  }

  /**
   * Get the options without UUID zero
   *
   * @param bool $includeDeleted Whether or not to include soft-deleted options.
   *
   * @return Collection
   */
  public function getSelectableOptions(bool $includeDeleted = false):Collection
  {
    // this unfortunately just does not work.
    // return $this->dataOptions->matching(DBUtil::criteriaWhere([ '!key' => Uuid::NIL, 'deleted' => null, ]));
    return $this->dataOptions->filter(function($option) use ($includeDeleted) {
      /** @var ProjectParticipantFieldDataOption $option */
      return ($includeDeleted || empty($option->getDeleted()))
        && $option->getKey() != Uuid::nil();
    });
  }

  /**
   * Get the special option holding management data if present.
   *
   * @return null|ProjectParticipantFieldDataOption
   */
  public function getManagementOption():?ProjectParticipantFieldDataOption
  {
    return $this->getDataOption(Uuid::NIL);
  }

  /**
   * Get one specific option
   *
   * @param mixed $key Everything which can be converted to an UUID by
   * Uuid::asUuid().
   *
   * @return null|ProjectParticipantFieldDataOption
   */
  public function getDataOption(mixed $key):?ProjectParticipantFieldDataOption
  {
    return $this->getByUuid($this->dataOptions, $key, 'key');
  }

  /**
   * Set fieldData.
   *
   * @param Collection $fieldData
   *
   * @return ProjectParticipantField
   */
  public function setFieldData(Collection $fieldData):ProjectParticipantField
  {
    $this->fieldData = $fieldData;

    return $this;
  }

  /**
   * Get fieldData.
   *
   * @return Collection
   */
  public function getFieldData():Collection
  {
    return $this->fieldData;
  }

  /**
   * Return the number of data items associated with this field.
   *
   * @return int
   */
  public function usage():int
  {
    return $this->dataOptions->count();
  }

  /**
   * Set displayOrder.
   *
   * @param int|null $displayOrder
   *
   * @return ProjectParticipantField
   */
  public function setDisplayOrder($displayOrder):ProjectParticipantField
  {
    $this->displayOrder = $displayOrder;

    return $this;
  }

  /**
   * Get displayOrder.
   *
   * @return int|null
   */
  public function getDisplayOrder()
  {
    return $this->displayOrder;
  }

  /**
   * Set name.
   *
   * @param null|string $name
   *
   * @return ProjectParticipantField
   */
  public function setName(?string $name):ProjectParticipantField
  {
    $this->name = $name;
    if ($this->getLocale() == ConfigService::DEFAULT_LOCALE) {
      $this->untranslatedName = $this->name;
    }
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
   * Set untranslatedName.
   *
   * @param null|string $untranslatedName
   *
   * @return ProjectParticipantField
   */
  public function setUntranslatedName(?string $untranslatedName):ProjectParticipantField
  {
    throw new Exceptions\DatabaseReadonlyException('The property "untranslatedName" cannot be set, it is read-only.');
    return $this;
  }

  /**
   * Get untranslatedName.
   *
   * @return string
   */
  public function getUntranslatedName()
  {
    return $this->untranslatedName;
  }

  /**
   * Set multiplicity.
   *
   * @param EnumParticipantFieldMultiplicity|string $multiplicity
   *
   * @return ProjectParticipantField
   */
  public function setMultiplicity($multiplicity):ProjectParticipantField
  {
    $this->multiplicity = new Types\EnumParticipantFieldMultiplicity($multiplicity);

    return $this;
  }

  /**
   * Get multiplicity.
   *
   * @return EnumParticipantFieldMultiplicity
   */
  public function getMultiplicity():Types\EnumParticipantFieldMultiplicity
  {
    return $this->multiplicity;
  }

  /**
   * Set dataType.
   *
   * @param EnumParticipantFieldDataType|string $dataType
   *
   * @return ProjectParticipantField
   */
  public function setDataType($dataType):ProjectParticipantField
  {
    $this->dataType = new Types\EnumParticipantFieldDataType($dataType);
    return $this;
  }

  /**
   * Get dataType.
   *
   * @return EnumParticipantFieldDataType
   */
  public function getDataType():Types\EnumParticipantFieldDataType
  {
    return $this->dataType;
  }

  /**
   * Set dueDate.
   *
   * @param string|null|\DateTimeInterface $dueDate
   *
   * @return ProjectParticipantField
   */
  public function setDueDate($dueDate):ProjectParticipantField
  {
    $this->dueDate = self::convertToDateTime($dueDate);
    return $this;
  }

  /**
   * Get dueDate.
   *
   * @return \DateTimeImmutable|null
   */
  public function getDueDate():?\DateTimeImmutable
  {
    return $this->dueDate;
  }

  /**
   * Set depositDueDate.
   *
   * @param string|null|\DateTimeInterface $depositDueDate
   *
   * @return ProjectParticipantField
   */
  public function setDepositDueDate($depositDueDate):ProjectParticipantField
  {
    $this->depositDueDate = self::convertToDateTime($depositDueDate);
    return $this;
  }

  /**
   * Get depositDueDate.
   *
   * @return \DateTimeImmutable|null
   */
  public function getDepositDueDate():?\DateTimeImmutable
  {
    return $this->depositDueDate;
  }

  /**
   * Set defaultValue.
   *
   * @param null|ProjectParticipantFieldDataOption $defaultValue
   *
   * @return ProjectParticipantField
   */
  public function setDefaultValue($defaultValue):ProjectParticipantField
  {
    $this->defaultValue = $defaultValue;

    return $this;
  }

  /**
   * Get defaultValue.
   *
   * @return null|ProjectParticipantFieldDataOption
   */
  public function getDefaultValue():?ProjectParticipantFieldDataOption
  {
    return $this->defaultValue;
  }

  /**
   * Set tooltip.
   *
   * @param null|string $tooltip
   *
   * @return ProjectParticipantField
   */
  public function setTooltip(?string $tooltip):ProjectParticipantField
  {
    $this->tooltip = $tooltip;

    return $this;
  }

  /**
   * Get tooltip.
   *
   * @return string
   */
  public function getTooltip()
  {
    return $this->tooltip;
  }

  /**
   * Set tab.
   *
   * @param null|string $tab
   *
   * @return ProjectParticipantField
   */
  public function setTab(?string $tab):ProjectParticipantField
  {
    $this->tab = $tab;

    return $this;
  }

  /**
   * Get tab.
   *
   * @return string
   */
  public function getTab()
  {
    return $this->tab;
  }

  /**
   * Set untranslatedTab.
   *
   * @param null|string $untranslatedTab
   *
   * @return ProjectParticipantField
   */
  public function setUntranslatedTab(?string $untranslatedTab):ProjectParticipantField
  {
    throw new Exceptions\DatabaseReadonlyException('The property "untranslatedTab" cannot be set, it is read-only.');
    return $this;
  }

  /**
   * Get untranslatedTab.
   *
   * @return string
   */
  public function getUntranslatedTab()
  {
    return $this->untranslatedTab;
  }

  /**
   * Set encrypted.
   *
   * @param bool|null $encrypted
   *
   * @return ProjectParticipantField
   */
  public function setEncrypted($encrypted):ProjectParticipantField
  {
    $this->encrypted = $encrypted;

    return $this;
  }

  /**
   * Get encrypted.
   *
   * @return bool|null
   */
  public function getEncrypted()
  {
    return $this->encrypted;
  }

  /**
   * Set readers.
   *
   * @param string|null $readers
   *
   * @return ProjectParticipantField
   */
  public function setReaders($readers):ProjectParticipantField
  {
    $this->readers = $readers;

    return $this;
  }

  /**
   * Get readers.
   *
   * @return string|null
   */
  public function getReaders()
  {
    return $this->readers;
  }

  /**
   * Set writers.
   *
   * @param string|null $writers
   *
   * @return ProjectParticipantField
   */
  public function setWriters($writers):ProjectParticipantField
  {
    $this->writers = $writers;

    return $this;
  }

  /**
   * Get writers.
   *
   * @return string|null
   */
  public function getWriters()
  {
    return $this->writers;
  }
}
