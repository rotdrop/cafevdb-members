<?php
/**
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

namespace OCA\CAFeVDBMembers\Controller;

use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\DataResponse;
use OCP\IRequest;
use OCP\IConfig;

class SettingsController extends Controller
{
  /** @var IConfig */
  private $config;

  /** @var string */
  private $userId;

  public function __construct(
    string $appName
    , IRequest $request
    , IConfig $config
    , $userId
  ) {
    parent::__construct($appName, $request);
    $this->config = $config;
    $this->userId = $userId;
  }

  public function setAdmin(string $setting, $value)
  {
    $oldValue = $this->config->getAppValue($this->appName, $setting);
    $this->config->setAppValue($this->appName, $setting, $value);
    return new DataResponse([
      'oldValue' => $oldValue,
    ]);
  }

  public function getAdmin(string $setting)
  {
    return new DataResponse([
      'value' => $this->config->getAppValue($this->appName, $setting),
    ]);
  }

  /**
   * @NoAdminRequired
   */
  public function setPersonal(string $setting, $value)
  {
    $oldValue = $this->config->getUserValue($this->userId, $this->appName, $setting);
    $this->config->setUserValue($this->userId, $this->appName, $setting, $value);
    return new DataResponse([
      'oldValue' => $oldValue,
    ]);
  }

  /**
   * @NoAdminRequired
   */
  public function getPersonal(string $setting)
  {
    return new DataResponse([
      'value' => $this->config->getUserValue($this->userId, $this->appName, $setting),
    ]);
  }
}
