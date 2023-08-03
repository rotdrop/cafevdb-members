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
use OCA\CAFeVDBMembers\Database\DBAL\Types\EnumDirEntryType as DirEntryType;

/**
 * Generic directory entry for a database-backed file.
 *
 * @ORM\Table(name="PersonalizedDatabaseStorageDirEntriesView")
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="type", type="EnumDirEntryType")
 * @ORM\DiscriminatorMap({"generic"="DatabaseStorageDirEntry", "file"="DatabaseStorageFile", "folder"="DatabaseStorageFolder"})
 * @ORM\Entity
 */
class DatabaseStorageDirEntry implements \ArrayAccess
{
  use CAFEVDB\Traits\ArrayTrait;
  use CAFEVDB\Traits\UpdatedAtEntity;
  use CAFEVDB\Traits\CreatedAtEntity;

  /** @var string */
  protected static $type = DirEntryType::GENERIC;

  /**
   * @var int
   *
   * @ORM\Column(type="integer", nullable=false)
   * @ORM\Id
   * @ORM\GeneratedValue(strategy="IDENTITY")
   */
  protected $id;

  /**
   * @var string
   *
   * @ORM\Column(type="string", length=256)
   */
  protected $name;

  /**
   * @var DatabaseStorageFolder
   *
   * @ORM\ManyToOne(targetEntity="DatabaseStorageFolder", inversedBy="directoryEntries")
   */
  protected $parent;

  /** {@inheritdoc} */
  public function __construct()
  {
  }

  /** @return null|int */
  public function getId():?int
  {
    return $this->id;
  }

  /** @return null|string */
  public function getName():?string
  {
    return $this->name;
  }

  /** @return null|DatabaseStorageDirEntry */
  public function getParent():?DatabaseStorageFolder
  {
    return $this->parent;
  }

  /**
   * Fetch the entire path up to the root node. This will result in database
   * queries if the parent elements are not already in memory.
   *
   * @return string Full path excluding leadin slash.
   */
  public function getPathName():string
  {
    $path = $this->name;
    $node = $this->parent;
    while (!empty($node)) {
      $path = $node->getName() . Constants::PATH_SEP . $path;
      $node = $node->getParent();
    }
    return $path;
  }

  /**
   * @return DatabaseStorageFolder The root directory.
   */
  public function getRoot():DatabaseStorageFolder
  {
    for ($root = $this, $parent = $root->getParent(); !empty($parent); $root = $parent, $parent = $root->getParent());
    return $root;
  }

  /** @return bool Whether this folder has a parent folder. */
  public function isRootFolder():bool
  {
    return $this->parent === null;
  }

  /** {@inheritdoc} */
  public function __toString():string
  {
    return static::$type . ':' . $this->name;
  }
}
