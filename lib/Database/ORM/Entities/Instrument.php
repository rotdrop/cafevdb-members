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
 * @ORM\Table(name="PersonalizedInstrumentsView")
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 * @Gedmo\TranslationEntity(class="TableFieldTranslation")
 */
class Instrument implements \ArrayAccess
{
  use CAFEVDB\Traits\ArrayTrait;
  use CAFEVDB\Traits\TranslatableTrait;
  use CAFEVDB\Traits\SoftDeleteableEntity;
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
   * @ORM\Column(type="string", length=128, nullable=false)
   */
  private string $name;

  /**
   * @var int
   *
   * @ORM\Column(type="smallint", nullable=false)
   */
  private int $sortOrder;

  /**
   * @ORM\ManyToMany(targetEntity="InstrumentFamily", inversedBy="instruments")
   * @ORM\JoinTable(
   *   name="PersonalizedInstrumentInstrumentFamilyView",
   *   joinColumns={@ORM\JoinColumn(referencedColumnName="id")},
   *   inverseJoinColumns={@ORM\JoinColumn(referencedColumnName="id")}
   * )
   */
  private $families;

  /**
   * @ORM\OneToMany(targetEntity="MusicianInstrument", mappedBy="instrument")
   */
  private $musicianInstruments;

  public function __construct() {
    $this->arrayCTOR();
    $this->families = new ArrayCollection();
    $this->musicianInstruments = new ArrayCollection();
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
   * Set name.
   *
   * @param string $name
   *
   * @return Instrument
   */
  public function setName(string $name):Instrument
  {
    $this->name = $name;

    return $this;
  }

  /**
   * Get name.
   *
   * @return string
   */
  public function getName():string
  {
    return $this->name;
  }

  /**
   * Set familie.
   *
   * @param Collection $families
   *
   * @return Instrument
   */
  public function setFamilies(Collection $families):Instrument
  {
    $this->families = $families;

    return $this;
  }

  /**
   * Get families.
   *
   * @return Collection
   */
  public function getFamilies():Collection
  {
    return $this->families;
  }

  /**
   * Set sortOrder.
   *
   * @param int $sortOrder
   *
   * @return Instrument
   */
  public function setSortOrder($sortOrder):Instrument
  {
    $this->sortOrder = $sortOrder;

    return $this;
  }

  /**
   * Get sortOrder.
   *
   * @return int
   */
  public function getSortOrder():int
  {
    return $this->sortOrder;
  }

  /**
   * Set musicianInstruments.
   *
   * @param bool $musicianInstruments
   *
   * @return Instrument
   */
  public function setMusicianInstruments($musicianInstruments):Instrument
  {
    $this->musicianInstruments = $musicianInstruments;

    return $this;
  }

  /**
   * Get musicianInstruments.
   *
   * @return Collection
   */
  public function getMusicianInstruments():Collection
  {
    return $this->musicianInstruments;
  }

  /**
   * Get the usage count, i.e. the number of entity instances which  use this instrument.
   */
  public function usage():int
  {
    return $this->musicianInstruments->count()
      /* + $this->projectInstruments->count()
         + $this->projectInstrumentationNumbers->count() */;
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
