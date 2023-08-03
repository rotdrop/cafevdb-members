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
 * FileData
 *
 * Simple data table for image blobs.
 *
 * @ORM\Table(name="PersonalizedFileDataView")
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="type", type="EnumFileType")
 * @ORM\DiscriminatorMap({"generic"="FileData", "image"="ImageFileData", "encrypted"="EncryptedFileData"})
 * @ORM\Entity
 */
class FileData implements \ArrayAccess
{
  use CAFEVDB\Traits\ArrayTrait;

  /**
   * @var File
   *
   * As ORM still does not support lazy one-to-one associations from the
   * inverse side we just use one-directional from both sides here. This
   * works, as the join column is just the key of both sides. So we have no
   * "mappedBy" and "inversedBy".
   *
   * @ORM\Id
   * @ORM\OneToOne(targetEntity="File")
   */
  protected $file;

  /**
   * @var string
   *
   * @ORM\Column(type="string", length=32, nullable=false, options={"fixed"=true})
   */
  protected $dataHash;

  /**
   * @var string
   *
   * @ORM\Column(type="blob", nullable=false)
   */
  protected $data;

  // phpcs:ignore Squiz.Commenting.FunctionComment.Missing
  public function __construct()
  {
    $this->arrayCTOR();
  }
  // phpcs:enable

  /**
   * Get data.
   *
   * @param string $format
   *
   * @return string|null
   */
  public function getData(string $format = 'binary'):?string
  {
    if (is_resource($this->data)) {
      rewind($this->data);
      switch ($format) {
        case 'base64':
          return base64_encode(stream_get_contents($this->data));
        case 'resource':
          return $this->data;
        case 'binary':
          return stream_get_contents($this->data);
        default:
          return $this->data;
      }
    } else {
      switch ($format) {
        case 'base64':
          return base64_encode($this->data);
        case 'resource':
          $stream = fopen('php://memory', 'r+');
          fwrite($stream, $this->data);
          rewind($stream);
          return $stream;
        case 'binary':
          return $this->data;
        default:
          return $this->data;
      }
    }
  }

  /**
   * Get file.
   *
   * @return File
   */
  public function getFile():File
  {
    return $this->file;
  }
}
