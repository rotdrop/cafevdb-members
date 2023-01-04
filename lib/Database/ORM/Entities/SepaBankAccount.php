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

use OCA\CAFeVDBMembers\Database\ORM as CAFEVDB;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use MediaMonks\Doctrine\Mapping as MediaMonks;

/**
 * SepaBankAccount.
 *
 * @ORM\Table(name="PersonalizedSepaBankAccountsView")
 * @ORM\Entity
 */
class SepaBankAccount implements \ArrayAccess
{
  use CAFEVDB\Traits\ArrayTrait;
  use CAFEVDB\Traits\TimestampableEntity;
  use \OCA\CAFeVDBMembers\Traits\DateTimeTrait;
  use CAFEVDB\Traits\SoftDeleteableEntity;

  /**
   * @ORM\ManyToOne(targetEntity="Musician", inversedBy="sepaBankAccounts", fetch="EXTRA_LAZY")
   * @ORM\Id
   */
  private $musician;

  /**
   * @var int
   *
   * This is a POSITIVE per-musician sequence count. It currently is
   * incremented using
   * \OCA\CAFEVDB\Database\Doctrine\ORM\Traits\PerMusicianSequenceTrait
   *
   * @ORM\Column(type="integer")
   * @ORM\Id
   * @ORM\GeneratedValue(strategy="NONE")
   * _AT_ORM\GeneratedValue(strategy="CUSTOM")
   * _AT_ORM\CustomIdGenerator(class="OCA\CAFEVDB\Database\Doctrine\ORM\Mapping\PerMusicianSequenceGenerator")
   */
  private $sequence;

  /**
   * @var string
   *
   * Encryption with 16 bytes iv, 64 bytes HMAC (sha512) and perhaps more data for a multi-user seal.
   *
   * For now calculate wtih 256 bytes for the encrypted data itself + another
   * 256 bytes for each multi-user seal. Using 2k of data should be plenty
   * given that we probably only need two users: the management board with a
   * shared encryption key and the respective orchestra member with its own key.
   *
   * @ORM\Column(type="string", length=2048, nullable=false, options={"collation"="ascii_bin"})
   * @MediaMonks\Transformable(name="encrypt", context="encryptionContext[]")
   */
  private $iban;

  /**
   * @var string
   *
   * @ORM\Column(type="string", length=2048, nullable=false, options={"collation"="ascii_bin"})
   * @MediaMonks\Transformable(name="encrypt", context="encryptionContext[]")
   */
  private $bic;

  /**
   * @var string
   *
   * @ORM\Column(type="string", length=2048, nullable=false, options={"collation"="ascii_bin"})
   * @MediaMonks\Transformable(name="encrypt", context="encryptionContext[]")
   */
  private $blz;

  /**
   * @var string
   *
   * @ORM\Column(type="string", length=2048, nullable=false, options={"collation"="ascii_bin"})
   * @MediaMonks\Transformable(name="encrypt", context="encryptionContext[]")
   */
  private $bankAccountOwner;

  /**
   * @var array
   *
   * In memory encryption context to support multi user encryption.
   */
  private $encryptionContext = [];

  /**
   * @var Collection
   *
   * Link to the attached debit mandates. Can be more than one at a
   * given time, even more than one active.
   *
   * @ORM\OneToMany(targetEntity="SepaDebitMandate",
   *                mappedBy="sepaBankAccount",
   *                fetch="EXTRA_LAZY")
   */
  private $sepaDebitMandates;

  // phpcs:disable Squiz.Commenting.FunctionComment.Missing
  public function __construct()
  {
    $this->arrayCTOR();
    $this->sepaDebitMandates = new ArrayCollection();
    // $this->payments = new ArrayCollection();
  }
  // phpcs:enable

  /**
   * Set iban.
   *
   * @param null|string $iban
   *
   * @return SepaBankAccount
   */
  public function setIban(?string $iban):SepaBankAccount
  {
    $this->iban = $iban;

    return $this;
  }

  /**
   * Get iban.
   *
   * @return string
   */
  public function getIban()
  {
    return $this->iban;
  }

  /**
   * Set BIC.
   *
   * @param null|string $bic
   *
   * @return SepaBankAccount
   */
  public function setBic(?string $bic):SepaBankAccount
  {
    $this->bic = $bic;

    return $this;
  }

  /**
   * Get bic.
   *
   * @return string
   */
  public function getBic()
  {
    return $this->bic;
  }

  /**
   * Set blz.
   *
   * @param null|string $blz
   *
   * @return SepaBankAccount
   */
  public function setBlz(?string $blz):SepaBankAccount
  {
    $this->blz = $blz;

    return $this;
  }

  /**
   * Get blz.
   *
   * @return string
   */
  public function getBlz()
  {
    return $this->blz;
  }

  /**
   * Set bankAccountOwner.
   *
   * @param null|string $bankAccountOwner
   *
   * @return SepaBankAccount
   */
  public function setBankAccountOwner(?string $bankAccountOwner):SepaBankAccount
  {
    $this->bankAccountOwner = $bankAccountOwner;

    return $this;
  }

  /**
   * Get bankAccountOwner.
   *
   * @return string
   */
  public function getBankAccountOwner():string
  {
    return $this->bankAccountOwner;
  }

  /**
   * Set musician.
   *
   * @param Musician|null $musician
   *
   * @return SepaBankAccount
   */
  public function setMusician($musician = null):SepaBankAccount
  {
    $this->musician = $musician;

    return $this;
  }

  /**
   * Get musician.
   *
   * @return Musician|null
   */
  public function getMusician():?Musician
  {
    return $this->musician;
  }

  /**
   * Set sequence.
   *
   * @param int $sequence
   *
   * @return SepaBankAccount
   */
  public function setSequence(?int $sequence = null):SepaBankAccount
  {
    $this->sequence = $sequence;

    return $this;
  }

  /**
   * Get sequence.
   *
   * @return int|null
   */
  public function getSequence():?int
  {
    return $this->sequence;
  }

  // /**
  //  * Set payments.
  //  *
  //  * @param int $payments
  //  *
  //  * @return SepaBankAccount
  //  */
  // public function setPayments(Collection $payments):SepaBankAccount
  // {
  //   $this->payments = $payments;

  //   return $this;
  // }

  // /**
  //  * Get payments.
  //  *
  //  * @return Collection
  //  */
  // public function getPayments():Collection
  // {
  //   return $this->payments;
  // }

  /**
   * Set sepaDebitMandates.
   *
   * @param Collection $sepaDebitMandates
   *
   * @return SepaBankAccount
   */
  public function setSepaDebitMandates(Collection $sepaDebitMandates):SepaBankAccount
  {
    $this->sepaDebitMandates = $sepaDebitMandates;

    return $this;
  }

  /**
   * Get sepaDebitMandates.
   *
   * @return Collection
   */
  public function getSepaDebitMandates():Collection
  {
    return $this->sepaDebitMandates;
  }

  /**
   * Set encryptionContext.
   *
   * @param array $encryptionContext
   *
   * @return SepaBankAccount
   */
  public function setEncryptionContext(array $encryptionContext):SepaBankAccount
  {
    $this->encryptionContext = $encryptionContext;

    return $this;
  }

  /**
   * Get encryptionContext.
   *
   * @return array
   */
  public function getEncryptionContext():array
  {
    return $this->encryptionContext;
  }
}
