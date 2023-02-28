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
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

use OCA\CAFeVDBMembers\Database\ORM as CAFEVDB;

/**
 * InstrumentInsurance
 *
 * @ORM\Table(name="PersonalizedInstrumentInsurancesView")
 * @ORM\Entity
 */
class InstrumentInsurance implements \ArrayAccess
{
  use CAFEVDB\Traits\ArrayTrait;
  use CAFEVDB\Traits\TimestampableEntity;
  use CAFEVDB\Traits\SoftDeleteableEntity;
  use \OCA\CAFeVDBMembers\Toolkit\Traits\DateTimeTrait;

  /**
   * @var int
   *
   * @ORM\Column(type="integer", nullable=false)
   * @ORM\Id
   * @ORM\GeneratedValue(strategy="IDENTITY")
   */
  private $id;

  /**
   * @var Musician
   *
   * @ORM\ManyToOne(targetEntity="Musician", inversedBy="instrumentInsurances", fetch="EXTRA_LAZY")
   * @ORM\JoinColumn(nullable=false)
   */
  private $musician;

  /**
   * @var bool
   *
   * @ORM\Column(type="boolean", nullable=false)
   */
  private $isDebitor;

  /**
   * @var bool
   *
   * @ORM\Column(type="boolean", nullable=false)
   */
  private $isHolder;

  /**
   * @var bool
   *
   * @ORM\Column(type="boolean", nullable=false)
   */
  private $isOwner;

  /**
   * @var InsuranceRate
   *
   * @ORM\ManyToOne(targetEntity="InsuranceRate", inversedBy="instrumentInsurances", fetch="EXTRA_LAZY")
   * @ORM\JoinColumns(
   *   @ORM\JoinColumn(name="broker_id", referencedColumnName="broker_id", nullable=false),
   *   @ORM\JoinColumn(name="geographical_scope", referencedColumnName="geographical_scope", nullable=false)
   * )
   */
  private $insuranceRate;

  /**
   * @var string
   *
   * @ORM\Column(type="string", length=128, nullable=false)
   */
  private $object;

  /**
   * @var array
   *
   * @ORM\Column(type="boolean", nullable=true, options={"default"=false})
   */
  private $accessory = false;

  /**
   * @var string
   *
   * @ORM\Column(type="string", length=128, nullable=false)
   */
  private $manufacturer;

  /**
   * @var string
   *
   * @ORM\Column(type="string", length=64, nullable=false)
   */
  private $yearOfConstruction;

  /**
   * @var int
   *
   * @ORM\Column(type="integer", nullable=false)
   */
  private $insuranceAmount;

  /**
   * @var \DateTime
   *
   * @ORM\Column(type="date_immutable", nullable=false)
   */
  private $startOfInsurance;

  // phpcs:ignore Squiz.Commenting.FunctionComment.Missing
  public function __construct()
  {
    $this->arrayCTOR();
  }
  // phpcs:enable

  /**
   * Set id.
   *
   * @param int $id
   *
   * @return InstrumentInsurance
   */
  public function setId(int $id):InstrumentInsurance
  {
    $this->id = $id;

    return $this;
  }

  /**
   * Get id.
   *
   * @return int
   */
  public function getId():int
  {
    return $this->id;
  }

  /**
   * Get broker.
   *
   * @return InsuranceBroker
   */
  public function getBroker():InsuranceBroker
  {
    return $this->insuranceRate->getBroker();
  }

  /**
   * Get geographicalScope.
   *
   * @return Types\EnumGeographicalScope
   */
  public function getGeographicalScope():Types\EnumGeographicalScope
  {
    return $this->insuranceRate->getGeographicalScope();
  }

  /**
   * Set object.
   *
   * @param null|string $object
   *
   * @return InstrumentInsurance
   */
  public function setObject(?string $object):InstrumentInsurance
  {
    $this->object = $object;

    return $this;
  }

  /**
   * Get object.
   *
   * @return string
   */
  public function getObject():string
  {
    return $this->object;
  }

  /**
   * Set accessory.
   *
   * @param bool $accessory
   *
   * @return InstrumentInsurance
   */
  public function setAccessory(bool $accessory):InstrumentInsurance
  {
    $this->accessory = $accessory;

    return $this;
  }

  /**
   * Get accessory.
   *
   * @return bool
   */
  public function getAccessory():bool
  {
    return !empty($this->accessory);
  }

  /**
   * Set manufacturer.
   *
   * @param null|string $manufacturer
   *
   * @return InstrumentInsurance
   */
  public function setManufacturer(?string $manufacturer):InstrumentInsurance
  {
    $this->manufacturer = $manufacturer;

    return $this;
  }

  /**
   * Get manufacturer.
   *
   * @return string
   */
  public function getManufacturer():string
  {
    return $this->manufacturer;
  }

  /**
   * Set yearOfConstruction.
   *
   * @param null|string $yearOfConstruction
   *
   * @return InstrumentInsurance
   */
  public function setYearOfConstruction(?string $yearOfConstruction):InstrumentInsurance
  {
    $this->yearOfConstruction = $yearOfConstruction;

    return $this;
  }

  /**
   * Get yearOfConstruction.
   *
   * @return string
   */
  public function getYearOfConstruction()
  {
    return $this->yearOfConstruction;
  }

  /**
   * Set insuranceAmount.
   *
   * @param int $insuranceAmount
   *
   * @return InstrumentInsurance
   */
  public function setInsuranceAmount(int $insuranceAmount):InstrumentInsurance
  {
    $this->insuranceAmount = $insuranceAmount;

    return $this;
  }

  /**
   * Get insuranceAmount.
   *
   * @return int
   */
  public function getInsuranceAmount():int
  {
    return $this->insuranceAmount;
  }

  /**
   * Set musician.
   *
   * @param null|Musician $musician
   *
   * @return InstrumentInsurance
   */
  public function setMusician(?Musician $musician):InstrumentInsurance
  {
    $this->musician = $musician;

    return $this;
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
   * Set startOfInsurance.
   *
   * @param mixed $startOfInsurance
   *
   * @return InstrumentInsurance
   */
  public function setStartOfInsurance(mixed $startOfInsurance):InstrumentInsurance
  {
    $this->startOfInsurance = self::convertToDateTime($startOfInsurance);

    return $this;
  }

  /**
   * Get startOfInsurance.
   *
   * @return \DateTimeInterface
   */
  public function getStartOfInsurance():?\DateTimeInterface
  {
    return $this->startOfInsurance;
  }

  /**
   * Set insuranceRate.
   *
   * @param null|InsuranceRate $insuranceRate
   *
   * @return InstrumentInsurance
   */
  public function setInsuranceRate(?InsuranceRate $insuranceRate):InstrumentInsurance
  {
    $this->insuranceRate = $insuranceRate;
    return $this;
  }

  /**
   * Get insuranceRate.
   *
   * @return InsuranceRate
   */
  public function getInsuranceRate():InsuranceRate
  {
    return $this->insuranceRate;
  }

  /**
   * Set isDebitor.
   *
   * @param bool $isDebitor
   *
   * @return InstrumentInsurance
   */
  public function setIsDebitor(bool $isDebitor):InstrumentInsurance
  {
    $this->isDebitor = $isDebitor;
    return $this;
  }

  /**
   * Get isDebitor.
   *
   * @return bool
   */
  public function getIsDebitor():bool
  {
    return $this->isDebitor;
  }

  /**
   * Set isHolder.
   *
   * @param bool $isHolder
   *
   * @return InstrumentInsurance
   */
  public function setIsHolder(bool $isHolder):InstrumentInsurance
  {
    $this->isHolder = $isHolder;
    return $this;
  }

  /**
   * Get isHolder.
   *
   * @return bool
   */
  public function getIsHolder():bool
  {
    return $this->isHolder;
  }

  /**
   * Set isOwner.
   *
   * @param bool $isOwner
   *
   * @return InstrumentInsurance
   */
  public function setIsOwner(bool $isOwner):InstrumentInsurance
  {
    $this->isOwner = $isOwner;
    return $this;
  }

  /**
   * Get isOwner.
   *
   * @return bool
   */
  public function getIsOwner():bool
  {
    return $this->isOwner;
  }
}
