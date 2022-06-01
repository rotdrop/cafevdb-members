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

namespace OCA\CAFeVDBMembers\Database\ORM\Listeners;

use Psr\Log\LoggerInterface;
use OCP\IUserSession;
use OCP\Authentication\LoginCredentials\IStore as ICredentialsStore;
use OCP\Authentication\LoginCredentials\ICredentials;
use MediaMonks\Doctrine\Transformable;
use OCA\CAFEVDB\Crypto;

class Encryption implements Transformable\Transformer\TransformerInterface
{
  use \OCA\CAFeVDBMembers\Traits\LoggerTrait;

  /** @var IUserSession */
  private $userSession;

  /** @var Crypto\AsymmetricKeyService */
  private $keyService;

  /** @var Crypto\SealCryptor */
  private $sealCryptor;

  public function __construct(
    IUserSession $userSession
    , Crypto\AsymmetricKeyService $keyService
    , Crypto\SealCryptor $sealCryptor
    , LoggerInterface $logger
  ) {
    $this->userSession = $userSession;
    $this->keyService = $keyService;
    $this->sealCryptor = clone $sealCryptor;
    $this->logger = $logger;
    $this->keyService->initEncryptionKeyPair();
  }

  public function isCachable(): bool
  {
    return true;
  }

  /**
   * Forward transform to data-base (encrypt).
   *
   * @param string $value Unencrypted data.
   *
   * @return string Encrypted data.
   */
  public function transform(?string $value, mixed &$context = null): mixed
  {
    $context = $this->manageEncryptionContext(null, $context);

    if (empty($context)) {
      return $value;
    }

    $sealCryptors = [];
    foreach ($context as $encryptionId) {
      $cryptor =  $this->getSealCryptor($encryptionId);
      if ($cryptor->canEncrypt()) {
        $sealCryptors[$encryptionId] = $cryptor;
      }
    }
    $this->sealCryptor->setSealCryptors($sealCryptors);

    return $this->sealCryptor->encrypt($value);
  }

  /**
   * Decrypt.
   *
   * @param string $value Encrypted data.
   *
   * @return string Decrypted data.
   */
  public function reverseTransform(?string $value, mixed &$context = null): mixed
  {
    if (!$this->sealCryptor->getSealService()->isSealedData($value)) {
      return $value;
    }

    $context = $this->manageEncryptionContext($value, $context);

    if (empty($context)) {
      return $value;
    }

    $sealCryptors = [];
    foreach ($context as $encryptionId) {
      $cryptor = $this->getSealCryptor($encryptionId);
      if ($cryptor->canDecrypt()) {
        $sealCryptors[$encryptionId] = $cryptor;
      }
    }
    $this->sealCryptor->setSealCryptors($sealCryptors);

    return $this->sealCryptor->decrypt($value);
  }

  private function getSealCryptor(string $encryptionId):Crypto\ICryptor
  {
    return $this->keyService->getCryptor($encryptionId);
  }

  private function manageEncryptionContext(?string $value, $context)
  {
    if (is_string($context)) {
      try {
        $context = json_decode($context);
      } catch (\Throwable $t) {
        $context = explode(',', $context);
      }
    }

    if (empty($context)) {
      $context = [];
    }

    if ($this->sealCryptor->getSealService()->isSealedData($value)) {
      $sealData = $this->sealCryptor->getSealService()->parseSeal($value);
      $context = array_merge($context, array_keys($sealData['keys']));
    }
    $user = $this->userSession->getUser();
    if (!empty($user))  {
      $userId = $user->getUID();
      if (array_search($userId, $context) === false) {
        $context[] = $userId;
      }
    }

    if (!is_array($context)) {
      throw new \RuntimeException('Encryption context must be an array of user- or @group-ids.');
    }
    return array_unique($context);
  }
};
