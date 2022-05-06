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
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;

use OCA\CAFeVDBMembers\Database\ORM as CAFEVDB;
use OCA\CAFeVDBMembers\Database\DBAL\Types;

/**
 * Musician
 *
 * @ORM\Table(name="PersonalizedMusiciansView")
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 */
class Musician implements \ArrayAccess, \JsonSerializable
{
  use CAFEVDB\Traits\ArrayTrait;
  use CAFEVDB\Traits\TimestampableEntity;
  use CAFEVDB\Traits\UuidTrait;
  // use CAFEVDB\Traits\GetByUuidTrait; used for participant fields.
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
   * @var string
   *
   * @ORM\Column(type="string", length=128, nullable=false)
   */
  private $surName;

  /**
   * @var string
   *
   * @ORM\Column(type="string", length=128, nullable=false)
   */
  private $firstName;

  /**
   * @var string
   *
   * @ORM\Column(type="string", length=128, nullable=true)
   */
  private $nickName;

  /**
   * @var string
   *
   * Display name, replaces default "$surName, $firstName"
   *
   * @ORM\Column(type="string", length=256, nullable=true)
   */
  private $displayName;

  /**
   * @var string
   *
   * This should look like a suitable user id, e.g.
   *
   * "official":  firstName.surName, nickName.surName
   * "personal":  firstName or firstname-FIRSTLETER_OF_SURNAME, e.g kathap, kathid
   *
   * We use the semi-official nickName.surName, e.g. katha.puff.
   *
   * @ORM\Column(type="string", length=256, unique=true, nullable=true)
   */
  private $userIdSlug;

  /**
   * @var string
   *
   * Meant for per-user authentication which might be used for future
   * extensions.
   *
   * @ORM\Column(type="string", length=256, unique=false, nullable=true)
   */
  private $userPassphrase;

  /**
   * @var string
   *
   * @ORM\Column(type="string", length=128, nullable=true)
   */
  private $city;

  /**
   * @var string
   *
   * @ORM\Column(type="string", length=128, nullable=true)
   */
  private $street;

  /**
   * @var string
   *
   * @ORM\Column(type="string", length=2, nullable=true)
   */
  private $country;

  /**
   * @var int|null
   *
   * @ORM\Column(type="string", length=32, nullable=true)
   */
  private $postalCode;

  /**
   * @var string
   *
   * @ORM\Column(type="string", length=2, nullable=true)
   */
  private $language;

  /**
   * @var string
   *
   * @ORM\Column(type="string", length=128, nullable=true)
   */
  private $mobilePhone;

  /**
   * @var string
   *
   * @ORM\Column(type="string", length=128, nullable=true)
   */
  private $fixedLinePhone;

  /**
   * @var \DateTime|null
   *
   * @ORM\Column(type="date_immutable", nullable=true)
   */
  private $birthday;

  /**
   * @var string
   *
   * @ORM\Column(type="string", length=256, nullable=false)
   */
  private $email;

  /**
   * @var Types\EnumMemberStatus|null
   *
   * @ORM\Column(type="EnumMemberStatus", nullable=false)
   */
  private $memberStatus;

  /**
   * @var string|null
   *
   * @ORM\Column(type="string", length=1024, nullable=true)
   */
  private $remarks;

  /**
   * @var bool|null
   *
   * Set to true if for whatever reason the user remains undeleted in the
   * musician-database but its cloud-account needs to be deactivated, for
   * instance to prevent abuse after a password breach or things like that.
   *
   * This only affects the cloud-account of DB-musicians. It can be set by
   * admins and group-admins through the cloud admin UI.
   *
   * @ORM\Column(type="boolean", nullable=true)
   */
  private $cloudAccountDeactivated;

  /**
   * @var bool|null
   *
   * Set to true if the cloud user-account should not be generated at
   * all. This differs from $cloudAccountDeactivated in that with
   * "...Disabled" the musician is not even exported as user account, while
   * the "...Deactivated" flag can be changed by the cloud administrator.
   *
   * Not that deleted users are also not exported to the cloud.
   *
   * @ORM\Column(type="boolean", nullable=true)
   */
  private $cloudAccountDisabled;

  /**
   * @ORM\OneToMany(targetEntity="MusicianInstrument", mappedBy="musician", fetch="EXTRA_LAZY")
   */
  private $instruments;

  /**
   * @ORM\OneToMany(targetEntity="SepaBankAccount", mappedBy="musician", fetch="EXTRA_LAZY")
   */
  private $sepaBankAccounts;

  /**
   * @ORM\OneToMany(targetEntity="SepaDebitMandate", mappedBy="musician", fetch="EXTRA_LAZY")
   */
  private $sepaDebitMandates;

  public function __construct() {
    $this->__wakeup();
    $this->memberStatus = Types\EnumMemberStatus::REGULAR();
    $this->instruments = new ArrayCollection;
    $this->sepaBankAccounts = new ArrayCollection;
    $this->sepaDebitMandates = new ArrayCollection;
  }

  public function __wakeup()
  {
    $this->arrayCTOR();
    $this->keys[] = 'publicName';
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
   * Set surName.
   *
   * @param string $surName
   *
   * @return Musician
   */
  public function setSurName($surName):Musician
  {
    $this->surName = $surName;

    return $this;
  }

  /**
   * Get surName.
   *
   * @return string
   */
  public function getSurName()
  {
    return $this->surName;
  }

  /**
   * Set firstName.
   *
   * @param string $firstName
   *
   * @return Musician
   */
  public function setFirstName($firstName):Musician
  {
    $this->firstName = $firstName;

    return $this;
  }

  /**
   * Get firstName.
   *
   * @return string
   */
  public function getFirstName()
  {
    return $this->firstName;
  }

  /**
   * Set city.
   *
   * @param string $city
   *
   * @return Musician
   */
  public function setCity($city):Musician
  {
    $this->city = $city;

    return $this;
  }

  /**
   * Get city.
   *
   * @return string
   */
  public function getCity()
  {
    return $this->city;
  }

  /**
   * Set street.
   *
   * @param string $street
   *
   * @return Musician
   */
  public function setStreet($street):Musician
  {
    $this->street = $street;

    return $this;
  }

  /**
   * Get street.
   *
   * @return string
   */
  public function getStreet()
  {
    return $this->street;
  }

  /**
   * Set postalCode.
   *
   * @param int|null $postalCode
   *
   * @return Musician
   */
  public function setPostalCode($postalCode = null):Musician
  {
    $this->postalCode = $postalCode;

    return $this;
  }

  /**
   * Get postalCode.
   *
   * @return int|null
   */
  public function getPostalCode()
  {
    return $this->postalCode;
  }

  /**
   * Set country.
   *
   * @param string $country
   *
   * @return Musician
   */
  public function setCountry($country):Musician
  {
    $this->country = $country;

    return $this;
  }

  /**
   * Get country.
   *
   * @return string
   */
  public function getCountry()
  {
    return $this->country;
  }

  /**
   * Set language.
   *
   * @param string $language
   *
   * @return Musician
   */
  public function setLanguage($language):Musician
  {
    $this->language = $language;

    return $this;
  }

  /**
   * Get language.
   *
   * @return string
   */
  public function getLanguage()
  {
    return $this->language;
  }

  /**
   * Set mobilePhone.
   *
   * @param string $mobilePhone
   *
   * @return Musician
   */
  public function setMobilePhone($mobilePhone):Musician
  {
    $this->mobilePhone = $mobilePhone;

    return $this;
  }

  /**
   * Get mobilePhone.
   *
   * @return string
   */
  public function getMobilePhone()
  {
    return $this->mobilePhone;
  }

  /**
   * Set fixedLinePhone.
   *
   * @param string $fixedLinePhone
   *
   * @return Musician
   */
  public function setFixedLinePhone($fixedLinePhone):Musician
  {
    $this->fixedLinePhone = $fixedLinePhone;

    return $this;
  }

  /**
   * Get fixedLinePhone.
   *
   * @return string
   */
  public function getFixedLinePhone()
  {
    return $this->fixedLinePhone;
  }

  /**
   * Set birthday.
   *
   * @param string|int|\DateTimeInterface $birthday
   *
   * @return Musician
   */
  public function setBirthday($birthday):Musician
  {
    $this->birthday = self::convertToDateTime($birthday);

    return $this;
  }

  /**
   * Get birthday.
   *
   * @return \DateTimeInterface|null
   */
  public function getBirthday():?\DateTimeInterface
  {
    return $this->birthday;
  }

  /**
   * Set email.
   *
   * @param string $email
   *
   * @return Musician
   */
  public function setEmail($email):Musician
  {
    $this->email = $email;

    return $this;
  }

  /**
   * Get email.
   *
   * @return string
   */
  public function getEmail()
  {
    return $this->email;
  }

  /**
   * Set memberStatus.
   *
   * @param string|EnumMemberStatus $memberStatus
   *
   * @return Musician
   */
  public function setMemberStatus($memberStatus):Musician
  {
    $this->memberStatus = new Types\EnumMemberStatus($memberStatus);

    return $this;
  }

  /**
   * Get memberStatus.
   *
   * @return EnumMemberStatus
   */
  public function getMemberStatus():Types\EnumMemberStatus
  {
    return $this->memberStatus;
  }

  /**
   * Set remarks.
   *
   * @param string|null $remarks
   *
   * @return Musician
   */
  public function setRemarks(?string $remarks = null):Musician
  {
    $this->remarks = $remarks;

    return $this;
  }

  /**
   * Get remarks.
   *
   * @return string|null
   */
  public function getRemarks():?string
  {
    return $this->remarks;
  }

  /**
   * Set cloudAccountDisabled.
   *
   * @param null|bool $cloudAccountDisabled
   *
   * @return Musician
   */
  public function setCloudAccountDisabled(?bool $cloudAccountDisabled):Musician
  {
    $this->CloudAccountDisabled = $cloudAccountDisabled;

    return $this;
  }

  /**
   * Get cloudAccountDisabled.
   *
   * @return null|bool
   */
  public function getCloudAccountDisabled():?bool
  {
    return $this->cloudAccountDisabled;
  }

  /**
   * Set cloudAccountDeactivated.
   *
   * @param null|bool $cloudAccountDeactivated
   *
   * @return Musician
   */
  public function setCloudAccountDeactivated(?bool $cloudAccountDeactivated):Musician
  {
    $this->CloudAccountDeactivated = $cloudAccountDeactivated;

    return $this;
  }

  /**
   * Get cloudAccountDeactivated.
   *
   * @return null|bool
   */
  public function getCloudAccountDeactivated():?bool
  {
    return $this->cloudAccountDeactivated;
  }

  /**
   * Set instruments.
   *
   * @param Collection $instruments
   *
   * @return Musician
   */
  public function setInstruments(Collection $instruments):Musician
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
   * Set displayName.
   *
   * @param string|null $displayName
   *
   * @return Musician
   */
  public function setDisplayName(?string $displayName):Musician
  {
    $this->displayName = $displayName;

    return $this;
  }

  /**
   * Get displayName.
   *
   * @return string
   */
  public function getDisplayName():?string
  {
    return $this->displayName;
  }

  /**
   * Set nickName.
   *
   * @param string|null $nickName
   *
   * @return Musician
   */
  public function setNickName(?string $nickName):Musician
  {
    $this->nickName = $nickName;

    return $this;
  }

  /**
   * Get nickName.
   *
   * @return string
   */
  public function getNickName():?string
  {
    return $this->nickName;
  }


  /**
   * Get the cooked display-name, taking nick-name into account and
   * just using $displayName if set.
   */
  public function getPublicName($firstNameFirst = false)
  {
    $firstName = empty($this->nickName) ? $this->firstName : $this->nickName;
    if ($firstNameFirst) {
      return $firstName . ' ' . $this->surName;
    }
    if (!empty($this->displayName)) {
      return $this->displayName;
    }
    return $this->surName.', '.$firstName;
  }

  /**
   * Set userPassphrase.
   *
   * @param string|null $userPassphrase
   *
   * @return Musician
   */
  public function setUserPassphrase(?string $userPassphrase):Musician
  {
    $this->userPassphrase = $userPassphrase;

    return $this;
  }

  /**
   * Get userPassphrase.
   *
   * @return string
   */
  public function getUserPassphrase():?string
  {
    return $this->userPassphrase;
  }

  /**
   * Set userIdSlug.
   *
   * @param string|null $userIdSlug
   *
   * @return Musician
   */
  public function setUserIdSlug(?string $userIdSlug):Musician
  {
    $this->userIdSlug = $userIdSlug;

    return $this;
  }

  /**
   * Get userIdSlug.
   *
   * @return string
   */
  public function getUserIdSlug():?string
  {
    return $this->userIdSlug;
  }

  /**
   * Set sepaBankAccounts.
   *
   * @param Collection $sepaBankAccounts
   *
   * @return Musician
   */
  public function setSepaBankAccounts(Collection $sepaBankAccounts):Musician
  {
    $this->sepaBankAccounts = $sepaBankAccounts;

    return $this;
  }

  /**
   * Get sepaBankAccounts.
   *
   * @return Collection
   */
  public function getSepaBankAccounts():Collection
  {
    return $this->sepaBankAccounts;
  }

  /**
   * Set sepaDebitMandates.
   *
   * @param Collection $sepaDebitMandates
   *
   * @return Musician
   */
  public function setSepaDebitMandates(Collection $sepaDebitMandates):Musician
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

  public function jsonSerialize():array
  {
    return $this->toArray();
    return array_merge($this->toArray(), [
      'publicName' => $this->getPublicName(true),
    ]);
  }

  /**
   * @ORM\PostLoad
   *
   * __wakeup() is not called when loading entities. Here we add a "virtual"
   * array key for the \ArrayAccess implementation.
   */
  public function postLoad()
  {
    $this->__wakeup();
  }
}
