<?php
/**
 * Member's data base connector for CAFEVDB orchetra management app.
 *
 * @author Claus-Justus Heine <himself@claus-justus-heine.de>
 * @copyright Copyright (c) 2025 Claus-Justus Heine>
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

namespace OCA\CAFeVDBMembers\Service;

use UnexpectedValueException;

use OCP\Files\Folder;
use OCP\Files\NotFoundException as FileNotFoundException;
use OCP\IL10N;
use OCP\IUserSession;
use Psr\Log\LoggerInterface;

use OCA\CAFeVDBMembers\Exceptions;
use OCA\CAFeVDBMembers\Toolkit\Traits as ToolkitTraits;
use OCA\CAFeVDBMembers\Toolkit\Service\AppStorageDisclosure;

/**
 * Service class for managing project registrations, generating shares,
 * notifications and so on.
 */
class ProjectRegistrationService
{
  use ToolkitTraits\LoggerTrait;

  private const REGISTRATION_HASH_ALGORITHM = 'sha256';
  public const REGISTRATION_FOLDER = 'project-registration';
  public const PERSONAL_PROFILE_KEY = 'personalProfile';
  public const EMAIL_KEY = 'email';
  public const USER_ID_KEY = 'uid';

  // phpcs:disable Squiz.Commenting.FunctionComment.Missing
  public function __construct(
    protected AppStorageDisclosure $appStorage,
    protected IL10N $l,
    protected IUserSession $userSession,
    protected LoggerInterface $logger,
  ) {
  }
  // phpcs:enable Squiz.Commenting.FunctionComment.Missing

  /**
   * Data submission, this is more-or-less the main entry point.
   *
   * @param string $projectName
   *
   * @param array $data The user submitted registration data.
   *
   * @return void
   *
   * @throws Exceptions\RegistrationDataMissingException
   * @throws UnexpectedValueException
   *
   * @todo If we have old data then make sure that the user is authenticated
   * before overwriting the application data.
   */
  public function handleSubmission(string $projectName, array $data): void
  {
    $this->logInfo('Submission Data ' . print_r($data, true));
    $primaryEmail = $data[self::PERSONAL_PROFILE_KEY][self::EMAIL_KEY] ?? null;
    if ($primaryEmail === null) {
      throw new Exceptions\RegistrationDataMissingException(
        message: $this->l->t(
          'The field "%1$s" in the submitted registration data is missing. '
          . ' Unfortunately, we do without a valid email address as we need some means to communication with the persions applying of participation.',
          'email',
        ),
      );
    }
    // The email is the token that we use for identification
    $registrationHash = hash(self::REGISTRATION_HASH_ALGORITHM, $primaryEmail);
    /** @var Folder $folder */
    $folder = $this->appStorage->getFilesystemFolder(self::REGISTRATION_FOLDER . '/' . $projectName);
    $oldUid = null;
    try {
      $dataFile = $folder->get($registrationHash);
      $oldData = json_decode($dataFile->getContent(), JSON_OBJECT_AS_ARRAY);
      $oldUid = $oldData[self::PERSONAL_PROFILE_KEY][self::USER_ID_KEY] ?? null;
    } catch (FileNotFoundException) {
      $dataFile = $folder->newFile($registrationHash);
    }
    // Remember the UID and also keep any previously submitted UID. We treat
    // the email address as unique identifier here.
    if ($this->userSession->isLoggedIn()) {
      $uid = $this->userSession->getUser()->getUID();
      if ($oldUid !== null && $oldUid !== $uid) {
        throw new UnexpectedValueException(
          $this->l->t(
            'The UID "%1$s" stored in the previously submitted application differs from the uid "%2$s" of the current user.',
            [$oldUid, $uid],
          ),
        );
      }
      $data[self::PERSONAL_PROFILE_KEY][self::USER_ID_KEY] = $uid;
    }
    $dataFile->putContent(json_encode($data, JSON_PRETTY_PRINT));
  }
}
