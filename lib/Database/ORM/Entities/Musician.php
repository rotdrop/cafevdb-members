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
 */
class Musician implements \ArrayAccess, \JsonSerializable
{
  use CAFEVDB\Traits\ArrayTrait;
  use CAFEVDB\Traits\TimestampableEntity;
  use CAFEVDB\Traits\UuidTrait;
  use CAFEVDB\Traits\GetByUuidTrait;
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
   * @ORM\OneToMany(targetEntity="ProjectParticipant", mappedBy="musician", indexBy="project_id", fetch="EXTRA_LAZY")
   */
  private $projectParticipation;

  /**
   * @ORM\OneToMany(targetEntity="ProjectInstrument", mappedBy="musician", fetch="EXTRA_LAZY")
   */
  private $projectInstruments;

  /**
   * @ORM\OneToMany(targetEntity="ProjectParticipantFieldDatum", mappedBy="musician", indexBy="option_key", fetch="EXTRA_LAZY")
   */
  private $projectParticipantFieldsData;

  /**
   * @ORM\OneToMany(targetEntity="SepaBankAccount", mappedBy="musician", fetch="EXTRA_LAZY")
   */
  private $sepaBankAccounts;

  /**
   * @ORM\OneToMany(targetEntity="SepaDebitMandate", mappedBy="musician", fetch="EXTRA_LAZY")
   */
  private $sepaDebitMandates;

  /**
   * @ORM\OneToMany(targetEntity="CompositePayment", mappedBy="musician", fetch="EXTRA_LAZY")
   */
  private $payments;

  /**
   * @ORM\OneToMany(targetEntity="InstrumentInsurance", mappedBy="musician", fetch="EXTRA_LAZY")
   */
  private $instrumentInsurances;

  /**
   * @var Collection
   *
   * @ORM\ManyToMany(targetEntity="EncryptedFile", inversedBy="owners", indexBy="id", fetch="EXTRA_LAZY")
   * @ORM\JoinTable(name="EncryptedFileOwners")
   *
   * The list of files owned by this musician. This is in particular important for
   * encrypted files where the list of owners determines the encryption keys
   * which are used to seal the data.
   */
  private $encryptedFiles;

  public function __construct() {
    $this->__wakeup();
    $this->memberStatus = Types\EnumMemberStatus::REGULAR();
    $this->instruments = new ArrayCollection;
    $this->projectInstruments = new ArrayCollection();
    $this->projectParticipation = new ArrayCollection();
    $this->projectParticipantFieldsData = new ArrayCollection();
    $this->sepaBankAccounts = new ArrayCollection;
    $this->sepaDebitMandates = new ArrayCollection;
    $this->payments = new ArrayCollection();
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
   * Set encryptedFiles.
   *
   * @param Collection $encryptedFiles
   *
   * @return Musician
   */
  public function setEncryptedFiles(Collection $encryptedFiles):Musician
  {
    $this->encryptedFiles = $encryptedFiles;

    return $this;
  }

  /**
   * Get encryptedFiles.
   *
   * @return Collection
   */
  public function getEncryptedFiles():Collection
  {
    return $this->encryptedFiles;
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
   * Set projectInstruments.
   *
   * @param Collection $projectInstruments
   *
   * @return Musician
   */
  public function setProjectInstruments(Collection $projectInstruments):Musician
  {
    $this->projectInstruments = $projectInstruments;

    return $this;
  }

  /**
   * Get projectInstruments.
   *
   * @return Collection
   */
  public function getProjectInstruments():Collection
  {
    return $this->projectInstruments;
  }

  /**
   * Set projectParticipation.
   *
   * @param Collection $projectParticipation
   *
   * @return Musician
   */
  public function setProjectParticipation(Collection $projectParticipation):Musician
  {
    $this->projectParticipation = $projectParticipation;

    return $this;
  }

  /**
   * Get projectParticipation.
   *
   * @return Collection
   */
  public function getProjectParticipation():Collection
  {
    return $this->projectParticipation;
  }

  /**
   * Check whether the given project is contained in projectParticipation.
   *
   * @param int|Project $projectOrId
   *
   * @return bool
   */
  public function isMemberOf($projectOrId):bool
  {
    return !empty($this->getProjectParticipantOf($projectOrId));
  }

  /**
   * Return the project-participant entity for the given project or null
   *
   * @param int|Project $projectOrId
   *
   * @return null|ProjectParticipant
   */
  public function getProjectParticipantOf($projectOrId):?ProjectParticipant
  {
    $projectId = ($projectOrId instanceof Project) ? $projectOrId->getId() : $projectOrId;
    $participant = $this->projectParticipation->get($projectId);
    if (!empty($participant)) {
      return $participant;
    }
    // $matching = $this->projectParticipation->matching(DBUtil::criteriaWhere([
    //   'project' => $projectId,
    // ]));
    //
    // The infamous
    //
    // Cannot match on
    // OCA\CAFEVDB\Database\Doctrine\ORM\Entities\ProjectParticipant::project
    // with a non-object value. Matching objects by id is not
    // compatible with matching on an in-memory collection, which
    // compares objects by reference.
    //
    // Oh no.

    $matching = $this->projectParticipation->filter(function($participant) use ($projectId) {
      return $participant->getProject()->getId() == $projectId;
    });
    if ($matching->count() == 1) {
      return $matching->first();
    }
    return null;
  }

  /**
   * Set payments.
   *
   * @param Collection $payments
   *
   * @return Musician
   */
  public function setPayments($payments):Musician
  {
    $this->payments = $payments;

    return $this;
  }

  /**
   * Get payments.
   *
   * @return Collection
   */
  public function getPayments():Collection
  {
    return $this->payments;
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
   * Set projectParticipantFieldsData.
   *
   * @param Collection $projectParticipantFieldsData
   *
   * @return Musician
   */
  public function setProjectParticipantFieldsData($projectParticipantFieldsData):Musician
  {
    $this->projectParticipantFieldsData = $projectParticipantFieldsData;

    return $this;
  }

  /**
   * Get projectParticipantFieldsData.
   *
   * @return Collection
   */
  public function getProjectParticipantFieldsData():Collection
  {
    return $this->projectParticipantFieldsData;
  }

  /**
   * Get one specific participant-field datum indexed by its key
   *
   * @param mixed $key Everything which can be converted to an UUID by
   * Uuid::asUuid().
   *
   * @return null|ProjectParticipantFieldDatum
   */
  public function getProjectParticipantFieldsDatum($key):?ProjectParticipantFieldDatum
  {
    return $this->getByUuid($this->projectParticipantFieldsData, $key, 'optionKey');
  }

  /**
   * Set instrumentInsurances.
   *
   * @param Collection $instrumentInsurances
   *
   * @return Musician
   */
  public function setInstrumentInsurances($instrumentInsurances):Musician
  {
    $this->instrumentInsurances = $instrumentInsurances;

    return $this;
  }

  /**
   * Get instrumentInsurances.
   *
   * @return Collection
   */
  public function getInstrumentInsurances():Collection
  {
    return $this->instrumentInsurances;
  }
}
