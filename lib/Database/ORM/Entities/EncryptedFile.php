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
 * @ORM\Entity
 */
class EncryptedFile extends File
{
  /**
   * @var Collection
   *
   * As ORM still does not support lazy one-to-one associations from the
   * inverse side we just use one-directional from both sides here. This
   * works, as the join column is just the key of both sides. So we have no
   * "mappedBy" and "inversedBy".
   *
   * Not that it is not possible to override the targetEntity annotation from
   * the base-class, so it must go here to the leaf-class.
   *
   * @ORM\OneToOne(targetEntity="EncryptedFileData", fetch="EXTRA_LAZY")
   * @ORM\JoinColumns(
   *   @ORM\JoinColumn(name="id", referencedColumnName="file_id", nullable=false),
   * )
   */
  protected $fileData;

  /**
   * @var Collection
   *
   * @ORM\ManyToMany(targetEntity="Musician", mappedBy="encryptedFiles", indexBy="id", fetch="EXTRA_LAZY")
   *
   * The list of owners which in addition to the members of the management
   * group may have access to this file. This is in particular important for
   * encrypted files where the list of owners determines the encryption keys
   * which are used to seal the data.
   */
  private $owners;

  // phpcs:ignore Squiz.Commenting.FunctionComment.Missing
  public function __construct($fileName = null, $data = null, $mimeType = null, ?Musician $owner = null)
  {
    parent::__construct($fileName, null, $mimeType);
    $this->owners = new ArrayCollection;
    $data = $data ?? '';
    $fileData = new EncryptedFileData;
    $fileData->setData($data);
    $this->setFileData($fileData)
      ->setSize(strlen($data));
    if (!empty($owner)) {
      $this->addOwner($owner);
    }
  }
  // phpcs:enable

  /**
   * Set Owners.
   *
   * @param Collection $owners
   *
   * @return EncryptedFile
   */
  public function setOwners(Collection $owners):EncryptedFile
  {
    $this->owners = $owners;

    return $this;
  }

  /**
   * Get Owners.
   *
   * @return Collection
   */
  public function getOwners():Collection
  {
    return $this->owners;
  }

  /**
   * Add the given musician to the list of owners.
   *
   * @param Musician $musician
   *
   * @return EncryptedFile
   */
  public function addOwner(Musician $musician):EncryptedFile
  {
    $musicianId = $musician->getId();
    if (!$this->owners->containsKey($musicianId)) {
      $this->owners->set($musicianId, $musician);
    }
    return $this;
  }

  /**
   * Remove the given musician from the list of owners
   *
   * @param Musician $musician
   *
   * @return EncryptedFile
   */
  public function removeOwner(Musician $musician):EncryptedFile
  {
    $this->owners->remove($musician->getId());

    return $this;
  }
}
