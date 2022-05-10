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
 * MusicianInstruments
 *
 * This is almost only a pivot table (i.e. a join table between
 * instruments and musicians) but for the "ranking" column which codes
 * a loose ranking like "primary instrument", i.e. the preference of
 * instruments of the given musician.
 *
 * @ORM\Table(name="PersonalizedMusicianInstrumentsView")
 * @ORM\Entity
 */
class MusicianInstrument implements \ArrayAccess
{
  use CAFEVDB\Traits\ArrayTrait;
  use CAFEVDB\Traits\TimestampableEntity;
  use CAFEVDB\Traits\SoftDeleteableEntity;
  use CAFEVDB\Traits\UnusedTrait;

  /**
   * @var Musician
   * @ORM\ManyToOne(targetEntity="Musician", inversedBy="instruments")
   * @ORM\Id
   */
  private $musician;

  /**
   * @var Instrument
   * @ORM\ManyToOne(targetEntity="Instrument", inversedBy="musicianInstruments")
   * @ORM\Id
   */
  private $instrument;

  /**
   * @var int
   * @ORM\Column(type="integer", nullable=false)
   */
  private $ranking = 1;

  public function __construct() {
    $this->__wakeup();
  }

  /**
   * Set musician.
   *
   * @param int $musician
   *
   * @return MusicianInstrument
   */
  public function setMusician($musician):MusicianInstrument
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
   * Set instrument.
   *
   * @param Instrument $instrument
   *
   * @return MusicianInstrument
   */
  public function setInstrument($instrument):MusicianInstrument
  {
    $this->instrument = $instrument;

    return $this;
  }

  /**
   * Get instrument.
   *
   * @return Instrument
   */
  public function getInstrument():Instrument
  {
    return $this->instrument;
  }

  /**
   * Set ranking.
   *
   * @param int $ranking
   *
   * @return MusicianInstrument
   */
  public function setRanking(int $ranking):MusicianInstrument
  {
    $this->ranking = $ranking;

    return $this;
  }

  /**
   * Get ranking.
   *
   * @return int
   */
  public function getRanking():int
  {
    return $this->ranking;
  }

  /**
   * Return the number of project instrumentation slots the associated
   * musician is registered with.
   */
  public function usage()
  {
    return 0; /* $this->projectInstruments->count(); */
  }
}
