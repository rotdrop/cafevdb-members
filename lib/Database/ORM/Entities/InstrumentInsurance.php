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
  use \OCA\CAFeVDBMembers\Traits\DateTimeTrait;

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

  public function __construct() {
    $this->arrayCTOR();
  }

  /**
   * Set id.
   *
   * @param int $id
   *
   * @return InstrumentInsurance
   */
  public function setId($id):InstrumentInsurance
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
   * Set instrumentHolder.
   *
   * @param int $instrumentHolder
   *
   * @return InstrumentInsurance
   */
  public function setInstrumentHolder($instrumentHolder):InstrumentInsurance
  {
    $this->instrumentHolder = $instrumentHolder;

    return $this;
  }

  /**
   * Get instrumentHolder.
   *
   * @return Musician
   */
  public function getInstrumentHolder():Musician
  {
    return $this->instrumentHolder;
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
   * @param string $object
   *
   * @return InstrumentInsurance
   */
  public function setObject($object):InstrumentInsurance
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
  public function setAccessory($accessory):InstrumentInsurance
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
    return $this->accessory;
  }

  /**
   * Set manufacturer.
   *
   * @param string $manufacturer
   *
   * @return InstrumentInsurance
   */
  public function setManufacturer($manufacturer):InstrumentInsurance
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
   * @param string $yearOfConstruction
   *
   * @return InstrumentInsurance
   */
  public function setYearOfConstruction($yearOfConstruction):InstrumentInsurance
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
  public function setInsuranceAmount($insuranceAmount):InstrumentInsurance
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
   * @param int $musician
   *
   * @return InstrumentInsurance
   */
  public function setMusician($musician):InstrumentInsurance
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
   * @param string|\DateTimeInterface $submitDate
   *
   * @return InstrumentInsurance
   */
  public function setStartOfInsurance($startOfInsurance):InstrumentInsurance
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
   * @param int $insuranceRate
   *
   * @return InstrumentInsurance
   */
  public function setInsuranceRate(InsuranceRate $insuranceRate):InstrumentInsurance
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
   * @param int $isDebitor
   *
   * @return InstrumentInsurance
   */
  public function setIsDebitor(IsDebitor $isDebitor):InstrumentInsurance
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
   * @param int $isHolder
   *
   * @return InstrumentInsurance
   */
  public function setIsHolder(IsHolder $isHolder):InstrumentInsurance
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
}
