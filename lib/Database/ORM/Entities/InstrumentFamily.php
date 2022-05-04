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
use Gedmo\Mapping\Annotation as Gedmo;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

use OCA\CAFeVDBMembers\Database\ORM as CAFEVDB;
use OCA\CAFeVDBMembers\Database\DBAL\Types;

/**
 * Instruments
 *
 * @ORM\Table(name="PersonalizedInstrumentFamiliesView")
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 * @Gedmo\TranslationEntity(class="TableFieldTranslation")
 */
class InstrumentFamily implements \ArrayAccess
{
  use CAFEVDB\Traits\ArrayTrait;
  use CAFEVDB\Traits\TranslatableTrait;
  use CAFEVDB\Traits\UnusedTrait;

  /**
   * @var int
   *
   * @ORM\Column(type="integer", nullable=false)
   * @ORM\Id
   * @ORM\GeneratedValue(strategy="IDENTITY")
   */
  private ?int $id = null;

  /**
   * @var string
   *
   * @Gedmo\Translatable
   * @ORM\Column(type="string", length=255, nullable=false, unique=true)
   */
  private string $family;

  /**
   * @ORM\ManyToMany(targetEntity="Instrument", mappedBy="families")
   */
  private $instruments;

  public function __construct() {
    $this->arrayCTOR();
    $this->instruments = new ArrayCollection();
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
   * Set family.
   *
   * @param string $family
   *
   * @return InstrumentFamily
   */
  public function setFamily(string $family):InstrumentFamily
  {
    $this->family = $family;

    return $this;
  }

  /**
   * Get family.
   *
   * @return string
   */
  public function getFamily():string
  {
    return $this->family;
  }

  /**
   * Set instruments.
   *
   * @param bool $instruments
   *
   * @return InstrumentFamily
   */
  public function setInstruments($instruments):InstrumentFamily
  {
    $this->instruments = $instruments;

    return $this;
  }

  /**
   * Get instruments.
   *
   * @return Collection
   */
  public function getInstruments():Collection
  {
    return $this->instruments;
  }

  /**
   * Get the usage count, i.e. the number of instruments which belong
   * to this family.
   */
  public function usage():int
  {
    return $this->instruments->count();
  }

  /**
   * @ORM\PostLoad
   *
   * __wakeup() is not called when loading entities
   */
  public function postLoad()
  {
    $this->__wakeup();
  }
}
