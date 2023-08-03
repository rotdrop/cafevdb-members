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
use OCA\CAFeVDBMembers\Database\DBAL\Types;

/**
 * Image -- Meta data for images stored in the data-base.
 *
 * The actual image data is stored in FileData for performance reasons.
 *
 * @ORM\Entity
 */
class Image extends File
{
  /**
   * @var FileData
   *
   * As ORM still does not support lazy one-to-one associations from the
   * inverse side we just use one-directional from both sides here. This
   * works, as the join column is just the key of both sides. So we have no
   * "mappedBy" and "inversedBy".
   *
   * Also: it is not possible to override the targetEntity from a bass-class
   * annotation, so the OneToOne annotation must got to the
   * leave-class. Further: in "single table inheritance" only leave-classes
   * can be loaded lazily. So we need this artificial ImageFileData class
   * which is just there to provide a lazy-loadable leaf-class.
   *
   * @ORM\OneToOne(targetEntity="ImageFileData", fetch="EXTRA_LAZY")
   * @ORM\JoinColumns(
   *   @ORM\JoinColumn(name="id", referencedColumnName="file_id", nullable=false),
   * )
   */
  protected $fileData;

  /**
   * @var int
   *
   * @ORM\Column(type="integer", nullable=false, options={"default"=-1})
   */
  private $width;

  /**
   * @var int
   *
   * @ORM\Column(type="integer", nullable=false, options={"default"=-1})
   */
  private $height;

  /**
   * Get $width.
   *
   * @return int
   */
  public function getWidth():int
  {
    return $this->width;
  }

  /**
   * Get $height.
   *
   * @return int
   */
  public function getHeight():int
  {
    return $this->height;
  }
}
