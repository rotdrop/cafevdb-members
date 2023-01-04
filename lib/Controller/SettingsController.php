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

namespace OCA\CAFeVDBMembers\Controller;

use Psr\Log\LoggerInterface;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\DataResponse;
use OCP\IRequest;
use OCP\IConfig;
use OCP\IL10N;

use OCA\CAFeVDBMembers\Service\GroupFoldersService;
use OCA\CAFeVDBMembers\Service\ProjectGroupService;

/** AJAX end-points for admin and personal settings. */
class SettingsController extends Controller
{
  use \OCA\RotDrop\Toolkit\Traits\ResponseTrait;
  use \OCA\RotDrop\Toolkit\Traits\LoggerTrait;

  const MEMBER_ROOT_FOLDER_KEY = 'memberRootFolder';
  const FOLDER_GROUPS_KEY = 'memberFolderGroups';
  const SYNCHRONIZE_KEY = 'synchronize';
  const USER_VIEWS_DATABASE_KEY = 'cloudUserViewsDatabase';

  /** @var IConfig */
  private $config;

  /** @var IL10N */
  private $l;

  /** @var string */
  private $userId;

  /** @var GroupFoldersService */
  private $groupFoldersService;

  /** @var ProjectGroupService */
  private $projectGroupService;

  /** @var string */
  private $appManagementGroup;

  // phpcs:ignore Squiz.Commenting.FunctionComment.Missing
  public function __construct(
    string $appName,
    string $appManagementGroup,
    IRequest $request,
    ?string $userId,
    LoggerInterface $logger,
    IL10N $l10n,
    IConfig $config,
    GroupFoldersService $groupFoldersService,
    ProjectGroupService $projectGroupService,
  ) {
    parent::__construct($appName, $request);
    $this->appManagementGroup = $appManagementGroup;
    $this->logger = $logger;
    $this->l = $l10n;
    $this->config = $config;
    $this->userId = $userId;
    $this->groupFoldersService = $groupFoldersService;
    $this->projectGroupService = $projectGroupService;
  }
  // phpcs:enable

  /**
   * @param string $setting
   *
   * @param null|string $value
   *
   * @param bool $force
   *
   * @return DataResponse
   *
   * @AuthorizedAdminSetting(settings=OCA\CAFeVDBMembers\Settings\Admin)
   */
  public function setAdmin(string $setting, ?string $value, bool $force = false):DataResponse
  {
    $newValue = $value;
    $oldValue = $this->config->getAppValue($this->appName, $setting);
    switch ($setting) {
      case self::MEMBER_ROOT_FOLDER_KEY:
        $oldRootFolder = empty($oldValue)
          ? null
          : $this->groupFoldersService->getFolder($oldValue);
        $newRootFolder = empty($newValue)
          ? null
          : $this->groupFoldersService->getFolder($newValue);

        if (empty($newValue) && !empty($oldRootFolder)) {
          if (!$force) {
            return new DataResponse([
              'status' => 'unconfirmed',
              'feedback' => $this->l->t('Really delete the old shared root-folder "/%1$s/"?', $oldValue),
            ]);
          }
          $this->groupFoldersService->deleteFolders('|^' . $oldValue . '.*|');
        }

        if ($oldValue != $newValue && !empty($newRootFolder)) {
          if (!$force) {
            return new DataResponse([
              'status' => 'unconfirmed',
              'feedback' => $this->l->t('Destination "%1$s" already exists and is configured as shared folder, delete it?', $newValue),
            ]);
          }

          $this->groupFoldersService->deleteFolders('|^' . $newValue . '$|');

          $newRootFolder = null;
        }

        if (!empty($newValue)) {
          if (empty($oldRootFolder)) {
            // create a new one
            $this->groupFoldersService->createFolder(
              $newValue,
              [ $this->appManagementGroup => GroupFoldersService::PERMISSION_ALL ],
              [ $this->appManagementGroup => GroupFoldersService::MANAGER_TYPE_GROUP ],
            );
          } elseif ($oldValue != $newValue) {
            // rename and/or check permissions
            $this->groupFoldersService->changeMountPoint($oldValue, $newValue, moveChildren: true);
          }
        }
        break;
      case self::SYNCHRONIZE_KEY:
        try {
          $this->projectGroupService->synchronizeFolderStructure($value);
          return new DataResponse([
            'messages' => $this->l->t('Successfully synchronized the shared-folder structure.'),
          ]);
        } catch (\Throwable $t) {
          $this->logException($t);
          return self::grumble($this->l->t('Synchronizing the shared-folder structure failed: %s', $t->getMessage()));
        }
      case self::USER_VIEWS_DATABASE_KEY:
        break;
      default:
        return self::grumble($this->l->t('Unknown admin setting: "%1$s"', $setting));
    }
    $this->config->setAppValue($this->appName, $setting, $newValue);
    return new DataResponse([
      'oldValue' => $oldValue,
    ]);
  }

  /**
   * @param string $setting
   *
   * @return DataResponse
   *
   * @AuthorizedAdminSetting(settings=OCA\CAFeVDBMembers\Settings\Admin)
   */
  public function getAdmin(string $setting):DataResponse
  {
    switch ($setting) {
      case self::USER_VIEWS_DATABASE_KEY:
      case self::MEMBER_ROOT_FOLDER_KEY:
        return new DataResponse([
          'value' => $this->config->getAppValue($this->appName, $setting),
        ]);
      case self::FOLDER_GROUPS_KEY:
        $groups = [];
        /** @var \OCP\IGroup $group */
        foreach ($this->projectGroupService->getProjectGroups() as $group) {
          $groups[] = [
            'gid' => $group->getGID(),
            'displayName' => $group->getDisplayName(),
          ];
        }
        return new DataResponse([
          'value' => $groups,
        ]);
    }
    return self::grumble($this->l->t('Unknown admin setting: "%1$s"', $setting));
  }


  /**
   * Export some of the admin settings
   *
   * @param string $setting
   *
   * @return DataResponse
   *
   * @NoAdminRequired
   */
  public function getApp(string $setting):DataResponse
  {
    switch ($setting) {
      case self::MEMBER_ROOT_FOLDER_KEY:
      case self::FOLDER_GROUPS_KEY:
        return $this->getAdmin($setting);
      default:
        return self::grumble($this->l->t('Unknown app setting: "%1$s"', $setting));
    }
  }

  /**
   * @param string $setting
   *
   * @param mixed $value
   *
   * @return DataResponse
   *
   * @NoAdminRequired
   */
  public function setPersonal(string $setting, mixed $value):DataResponse
  {
    $oldValue = $this->config->getUserValue($this->userId, $this->appName, $setting);
    $this->config->setUserValue($this->userId, $this->appName, $setting, $value);
    return new DataResponse([
      'oldValue' => $oldValue,
    ]);
  }

  /**
   * @param string $setting
   *
   * @return DataResponse
   *
   * @NoAdminRequired
   */
  public function getPersonal(string $setting):DataResponse
  {
    return new DataResponse([
      'value' => $this->config->getUserValue($this->userId, $this->appName, $setting),
    ]);
  }
}
