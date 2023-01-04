<?php
/**
 * Member's data base connector for CAFEVDB orchetra management app.
 *
 * @author Claus-Justus Heine <himself@claus-justus-heine.de>
 * @copyright Copyright (c) 2022 Claus-Justus Heine
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
 * An entity which modesl a file-system file. While it is not always
 * advisable to store file-system data in a data-base, we do so
 * nevertheless for selected small files.
 *
 * @ORM\Table(name="PersonalizedFilesView")
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="type", type="EnumFileType")
 * @ORM\DiscriminatorMap({"generic"="File","encrypted"="EncryptedFile","image"="Image"})
 * @ORM\Entity
 */
class File implements \ArrayAccess
{
  use CAFEVDB\Traits\ArrayTrait;
  use CAFEVDB\Traits\TimestampableEntity;

  const PATH_SEPARATOR = '/';

  /**
   * @var int
   *
   * @ORM\Column(type="integer", nullable=false)
   * @ORM\Id
   * @ORM\GeneratedValue(strategy="IDENTITY")
   */
  private $id;

  /**
   * @var string|null
   *
   * @ORM\Column(type="string", length=512, nullable=true)
   */
  private $fileName;

  /**
   * @var string|null
   *
   * @ORM\Column(type="string", length=128, nullable=false)
   */
  private $mimeType;

  /**
   * @var int
   *
   * @ORM\Column(type="integer", nullable=false, options={"default"=-1})
   */
  private $size = -1;

  /**
   * @var FileData
   *
   * As ORM still does not support lazy one-to-one associations from the
   * inverse side we use a OneToMany - ManyToOne trick which inserts a lazy
   * association in between.
   *
   * @ORM\OneToMany(targetEntity="FileData", mappedBy="file", cascade={"all"}, orphanRemoval=true, fetch="EXTRA_LAZY")
   */
  protected $fileData;

  /**
   * @var string|null
   *
   * @ORM\Column(type="string", length=32, nullable=true, options={"fixed"=true})
   */
  protected $dataHash;

  /**
   * @var \DateTimeImmutable
   * @ORM\Column(type="datetime_immutable", nullable=true)
   */
  protected $updated;

  // phpcs:ignore Squiz.Commenting.FunctionComment.Missing
  public function __construct($fileName = null, $data = null, $mimeType = null)
  {
    $this->arrayCTOR();
    $this->setFileName($fileName);
    $this->setMimeType($mimeType);
    $data = $data ?? '';
    $fileData = new FileData;
    $fileData->setData($data);
    $this->setFileData($fileData)
      ->setSize(strlen($data));
  }
  // phpcs:enable

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
   * Set mimeType.
   *
   * @param string|null $mimeType
   *
   * @return File
   */
  public function setMimeType($mimeType = null):File
  {
    $this->mimeType = $mimeType;

    return $this;
  }

  /**
   * Get mimeType.
   *
   * @return string|null
   */
  public function getMimeType()
  {
    return $this->mimeType;
  }

  /**
   * Set fileName.
   *
   * @param string|null $fileName
   *
   * @return File
   */
  public function setFileName($fileName = null):File
  {
    $this->fileName = $fileName;

    return $this;
  }

  /**
   * Get fileName.
   *
   * @return string|null
   */
  public function getFileName()
  {
    return $this->fileName;
  }

  /**
   * Set only the dir-name.
   *
   * @param string $dirName
   *
   * @return File
   */
  public function setDirName(string $dirName):File
  {
    $this->fileName = trim($dirName, self::PATH_SEPARATOR) . self::PATH_SEPARATOR . ($this->getBaseName()??'');
    return $this;
  }

  /**
   * Get the dir-part of the file-name
   *
   * @return null|string
   */
  public function getDirName():?string
  {
    return is_string($this->fileName) ? dirname($this->fileName) : null;
  }

  /**
   * Set only the base-name.
   *
   * @param string $baseName
   *
   * @return File
   */
  public function setBaseName(string $baseName):File
  {
    $this->fileName = ($this->getDirName()??'') . self::PATH_SEPARATOR . trim($baseName, self::PATH_SEPARATOR);
    return $this;
  }

  /**
   * Get the dir-part of the file-name.
   *
   * @param null|string $extension
   *
   * @return null|string
   */
  public function getBaseName(?string $extension = null):?string
  {
    return is_string($this->fileName) ? basename($this->fileName, $extension) : null;
  }

  /**
   * Set dataHash.
   *
   * @param string|null $dataHash
   *
   * @return File
   */
  public function setDataHash($dataHash = null):File
  {
    $this->dataHash = $dataHash;

    return $this;
  }

  /**
   * Get dataHash.
   *
   * @return string|null
   */
  public function getDataHash()
  {
    return $this->dataHash;
  }

  /**
   * Set $size.
   *
   * @param int $size
   *
   * @return File
   */
  public function setSize(int $size = -1):File
  {
    $this->size = $size;

    return $this;
  }

  /**
   * Get $size.
   *
   * @return int
   */
  public function getSize():int
  {
    return $this->size;
  }

  /**
   * Set FileData.
   *
   * @param FileData $fileData
   *
   * @return File
   */
  public function setFileData(FileData $fileData):File
  {
    $this->fileData = $fileData;
    $fileData->setFile($this);

    return $this;
  }

  /**
   * Get FileData.
   *
   * @return FileData
   */
  public function getFileData():FileData
  {
    return $this->fileData;
  }
}
