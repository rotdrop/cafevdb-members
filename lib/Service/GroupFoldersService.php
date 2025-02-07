<?php
/**
 * Member's data base connector for CAFEVDB orchetra management app.
 *
 * @copyright Copyright (c) 2022 Claus-Justus Heine <himself@claus-justus-heine.de>
 * @author Claus-Justus Heine <himself@claus-justus-heine.de>
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

use Psr\Log\LoggerInterface;
use OCP\IL10N;
use OCP\Constants;

/**
 * Mis-use the internal routes of the groupfolders app in order to
 * automatically generate the requried shared folder structure for the
 * orchestra members, in particular shared storage for photos.
 */
class GroupFoldersService
{
  use \OCA\CAFeVDBMembers\Traits\LoggerTrait;

  const GROUP_FOLDERS_APP = 'groupfolders';

  const SEARCH_TOPIC_MOUNT = 'mount';
  const SEARCH_TOPIC_GROUP = 'group';

  const PERMISSION_WRITE = Constants::PERMISSION_READ|Constants::PERMISSION_UPDATE|Constants::PERMISSION_CREATE;
  const PERMISSION_READ = Constants::PERMISSION_READ;
  const PERMISSION_DELETE = Constants::PERMISSION_READ|Constants::PERMISSION_DELETE;
  const PERMISSION_SHARE = Constants::PERMISSION_READ|Constants::PERMISSION_SHARE;
  const PERMISSION_ALL = Constants::PERMISSION_ALL;
  const DEFAULT_PERMISSIONS = self::PERMISSION_ALL;

  const MANAGER_TYPE_GROUP = 'group';
  const MANAGER_TYPE_USER = 'user';

  /** @var IL10N */
  private $l;

  /** @var RequestService */
  private $requestService;

  /**
   * @var array
   *
   * All shared folders.
   * ```
   * [
   *   'id' => ID,
   *   'mount_point' => MOUNT_POINT,
   *   'groups' => [
   *     GROUP_ID => PERMISSIONS,
   *   ],
   *   'quota' => -3,
   *   'size' => SIZE,
   *   'acl' => true/false,
   *   'manage' => [
   *      [ 'type' => 'group'/'user', 'id' => MANAGER_ID, 'displayname' => DISPLAY_NAME ]
   *   ]
   * ]
   * ```
   */
  private $sharedFolders = null;

  public function __construct(
    LoggerInterface $logger
    , IL10N $l10n
    , RequestService $requestService
  ) {
    $this->logger = $logger;
    $this->l = $l10n;
    $this->requestService = $requestService;
  }

  /**
   * Fetch and cache all shared folders from the groupfolders app
   */
  private function fetchFolders()
  {
    $folders = $this->requestService->getFromRoute(
      implode('.', [ self::GROUP_FOLDERS_APP, 'Folder', 'getFolders']));
    $this->sharedFolders = [];
    foreach ($folders as $id => $folderInfo) {
      $this->sharedFolders[$folderInfo['mount_point']] = $folderInfo;
    }
    $this->logDebug('FOLDERS ' . print_r($this->sharedFolders, true));
  }

  private function ensureFolders(bool $reload = false)
  {
    if ($reload || $this->sharedFolders === null) {
      $this->fetchFolders();
    }
  }

  /**
   * Obtain the shared folder data for the given mount-point.
   *
   * @param string $mountPoint
   *
   * @return null|array Return the data requested from the groupfolders app or
   * null if the folder is not found.
   */
  public function getFolder(string $mountPoint, bool $reload = false):?array
  {
    $this->ensureFolders($reload);
    $this->logDebug('SHARED FOLDERS: ' . print_r($this->sharedFolders, true));
    return $this->sharedFolders[$mountPoint]??null;
  }

  /**
   * Return all shared folders matching the given regexp.
   *
   * @param string $regexp
   *
   * @param string $topic
   */
  public function searchFolders(string $regexp, string $topic = self::SEARCH_TOPIC_MOUNT)
  {
    if ($this->sharedFolders === null) {
      $this->fetchFolders();
    }
    if ($regexp[0] != $regexp[-1]) {
      $regexp = '|^' . $regexp . '$|';
    }
    $this->logDebug('REGEXP ' . $regexp . ' / ' . $topic);
    switch ($topic) {
      case self::SEARCH_TOPIC_MOUNT:
        return array_filter($this->sharedFolders, function($folderInfo) use ($regexp) {
          return preg_match($regexp, $folderInfo['mount_point']);
        });
      case self::SEARCH_TOPIC_GROUP:
        return array_filter($this->sharedFolders, function($folderInfo) use ($regexp) {
          foreach (array_keys($folderInfo['groups']) as $group) {
            if (preg_match($regexp, $group)) {
              return true;
            }
            return false;
          }
        });
      default:
        throw new \RuntimeException($this->l->t('Unknown search topic "%1$s"', $topic));
    }
  }

  public function deleteFolders(string $mountRegexp)
  {
    $route = implode('.', [ self::GROUP_FOLDERS_APP, 'Folder', 'removeFolder']);
    $method = RequestService::DELETE;
    foreach ($this->searchFolders($mountRegexp) as $folderInfo) {
      $this->requestService->callInternalRoute($route, $method, [ 'id' => $folderInfo['id'], ]);
      unset($this->sharedFolders[$folderInfo['mount_point']]);
    }
  }

  public function getFolderById(int $id)
  {
    $folderInfo = $this->requestService->getFromRoute(
      implode('.', [ self::GROUP_FOLDERS_APP, 'Folder', 'getFolder']),
      routeParams: [ 'id' => $id ]
    );
    if (isset($folderInfo['id']) && isset($folderInfo['mount_point'])) {
      foreach ($this->sharedFolders as $mountPoint => $cachedInfo) {
        if ($cachedInfo['id'] == $folderInfo['id']) {
          unset($this->sharedFolders[$mountPoint]);
        }
      }
      $this->sharedFolders[$folderInfo['mount_point']] = $folderInfo;
    }
    $this->logDebug('BY ID INFO ' . print_r($folderInfo, true));
    return $folderInfo;
  }

  /**
   * Create a shared folder and set appropriate permissions
   *
   * @param string $mountPoint Note the a nested mount-point will not create
   * the parent folders.
   *
   * @param array $groups Array $groupId => $groupPermissions
   * ```
   * [ GROUP1_ID => PERMS1, GROUP2_ID => PERMS2 ]
   * ```
   *
   * @param array $manager ID => TYPE
   * ```
   * [ USER_ID => 'user', GROUP_ID => 'group' ]
   */
  public function createFolder(string $mountPoint, array $groups, array $manager = [])
  {
    $folderInfo = $this->getFolder($mountPoint);
    if (!empty($folderInfo)) {
      throw new \RuntimeException($this->l->t('Shared folder for mount-point "%1$s" already exists, cannot create it.', [ $mountPoint ]));
    }
    // CREATE:
    // POST BASE_URL/groupfolders/folders
    // DATA mountpoint: WHATEVER
    $route = implode('.', [ self::GROUP_FOLDERS_APP, 'Folder', 'addFolder']);
    $result = $this->requestService->postToRoute($route, requestData: [ 'mountpoint' => $mountPoint ]);
    $folderInfo = $this->getFolderById($result['id']);

    foreach ($groups as $groupId => $permissions) {
      $this->addGroupToFolder($mountPoint, $groupId, $permissions);
    }

    foreach ($manager as $managerId => $managerType) {
      $this->addManagerToFolder($mountPoint, $managerId, $managerType);
    }
  }

  /**
   * Add a single group to the given shared folder. This is a no-op if the
   * group including matching permissions is already there.
   */
  public function addGroupToFolder(string $mountPoint, string $groupId, int $permissions = self::DEFAULT_PERMISSIONS, bool $canManage = false)
  {
    // POST BASEURL/groupfolders/folders/4/groups
    // DATA group: GROUP_ID
    $folderInfo = $this->getFolder($mountPoint);
    if (empty($folderInfo)) {
      throw new \RuntimeException($this->l->t('Shared folder for mount-point "%1$s" does not exist, cannot add group "%2$s".', [ $mountPoint, $groupId ]));
    }
    if (!isset($folderInfo['groups'][$groupId])) {
      $route = implode('.', [ self::GROUP_FOLDERS_APP, 'Folder', 'addGroup']);
      $this->requestService->postToRoute($route, [ 'id' => $folderInfo['id'] ], [ 'group' => $groupId ]);
      $folderInfo['groups'][$groupId] = self::DEFAULT_PERMISSIONS;
    }
    if ($folderInfo['groups'][$groupId]??0 != $permissions) {
      $this->setGroupPermissions($mountPoint, $groupId, $permissions);
    }
    $this->changeFolderManager($mountPoint, $groupId, self::MANAGER_TYPE_GROUP, $canManage);
  }

  /**
   * Remove a group from the given shared folder.
   */
  public function removeGroupFromFolder(string $mountPoint, string $groupId)
  {
    // REMOVE GROUP
    // DELETE BASE_URL/groupfolders/folders/4/groups/GROUP_ID
    $folderInfo = $this->getFolder($mountPoint);
    if (empty($folderInfo)) {
      throw new \RuntimeException($this->l->t('Shared folder for mount-point "%1$s" does not exist, cannot remove group "%2$s".', [ $mountPoint, $groupId ]));
    }
    $route = implode('.', [ self::GROUP_FOLDERS_APP, 'Folder', 'removeGroup']);
    $method = RequestService::DELETE;
    $this->requestService->callInternalRoute($route, $method, [
      'id' => $folderInfo['id'],
      'group' => $groupId,
    ]);
    unset($folderInfo[$mountPoint]['groups'][$groupId]);
  }

  private function changeFolderManager(string $mountPoint, string $managerId, string $type, bool $canManage)
  {
    // POST BASEURL/groupfolders/folders/8/acl
    // [ acl => 0 / 1 ]
    //
    // POST BASEURL/groupfolders/folders/4/manageACL
    // [ mappingType => MANAGER_TYPE, mappingId => MANAGER_ID, manageAcl=> 1 ]
    //
    $folderInfo = $this->getFolder($mountPoint);
    if (empty($folderInfo)) {
      throw new \RuntimeException($this->l->t('Shared folder for mount-point "%1$s" does not exist, cannot modify manager "%2$s".', [ $mountPoint, $managerId ]));
    }

    $this->logDebug('FOLDER INFO ' . print_r($folderInfo, true));

    // first check if anything needs to be done
    $aclEnabled = !!($folderInfo['acl']??false);
    $isManager = 0 < count(array_filter($folderInfo['manage']??[], function($manager) use ($managerId, $type) {
      return $manager['type'] == $type && $manager['id'] == $managerId;
    }));

    if ($isManager == $canManage && ($canManage || $isManager == $canManage)) {
      return;
    }

    $folderId = $folderInfo['id'];
    if (!$aclEnabled) {
      $route = implode('.', [ self::GROUP_FOLDERS_APP, 'Folder', 'setACL']);
      $this->requestService->postToRoute($route, [ 'id' => $folderId ], [ 'acl' => 1 ]);
    }

    if ($isManager != $canManage) {
      $route = implode('.', [ self::GROUP_FOLDERS_APP, 'Folder', 'setManageACL']);
      $this->requestService->postToRoute($route, [ 'id' => $folderId ], [
        'mappingType' => $type,
        'mappingId' => $managerId,
        'manageAcl' => (int)$canManage,
      ]);
    }
    if (!$aclEnabled || $isManager != $canManage) {
      $this->getFolderById($folderId);
    }
  }

  public function addManagerToFolder(string $mountPoint, string $managerId, string $managerType)
  {
    $this->changeFolderManager($mountPoint, $managerId, $managerType, canManage: true);
  }

  public function removeManagerFromFolder(string $mountPoint, string $managerId, string $managerType)
  {
    $this->changeFolderManager($mountPoint, $managerId, $managerType, canManage: false);
  }

  public function setGroupPermissions(string $mountPoint, string $groupId, int $permissions)
  {
    // SET PERMISSIONS
    // POST BASE_URL/groupfolders/folders/4/groups/GROUP_ID
    // DATA permissions: PERM-BITFIELD
    $folderInfo = $this->getFolder($mountPoint);
    if (empty($folderInfo)) {
      throw new \RuntimeException($this->l->t('Shared folder for mount-point "%1$s" does not exist, cannot set permissions for group "%2$s".', [ $mountPoint, $groupId ]));
    }
    if (($folderInfo['groups'][$groupId]??0) != $permissions) {
      $route = implode('.', [ self::GROUP_FOLDERS_APP, 'Folder', 'setPermissions']);
      $this->requestService->postToRoute($route, [
        'id' => $folderInfo['id'],
        'group' => $groupId,
      ], [
        'permissions' => $permissions,
      ]);
      $this->sharedFolders[$mountPoint]['groups'][$groupId] = $permissions;
    }
  }

  /**
   * Change the given group-shared folder -- given by its old mount-point --
   * to a new mount-point.
   *
   * @param string $mountPoint The old mount-point which determines the
   * group-shared folder to act upon.
   *
   * @param string $targetMountPoint The destination mount-point. If the
   * parent-directories of the destination mount-point do not exist then the
   * shared folder will be hidden.
   *
   * @param bool $moveChildren If true and the old mount-point appears as prefix
   * in other group-shared folders then change their respective mount-points
   * as well.
   */
  public function changeMountPoint(string $mountPoint, string $targetMountPoint, bool $moveChildren = true)
  {
    if ($mountPoint == $targetMountPoint) {
      return;
    }
    $folderInfo = $this->getFolder($mountPoint);
    if (empty($folderInfo)) {
      throw new \RuntimeException($this->l->t('Shared folder for mount-point "%1$s" does not exist, cannot change its mount-point to "%2$s".', [ $mountPoint,  ]));
    }
    // POST BASE_URL/groupfolders/folders/FOLDER_ID/mountpoint
    // DATA [ mountpoint => NEW_MOUNT_POINT ]

    $route = implode('.', [ self::GROUP_FOLDERS_APP, 'Folder', 'renameFolder']);
    $this->requestService->postToRoute($route, [
      'id' => $folderInfo['id'],
    ], [
      'mountpoint' => $targetMountPoint,
    ]);
    $this->sharedFolders[$targetMountPoint] = $this->sharedFolders[$mountPoint];
    unset($this->sharedFolders[$mountPoint]);
    $this->sharedFolders[$targetMountPoint]['mount_point'] = $targetMountPoint;

    if ($moveChildren)  {
      $children = $this->searchFolders('|^' . $mountPoint . '/.*$|');
      foreach ($children as $childInfo) {
        $oldChildMount = $childInfo['mount_point'];
        $newChildMount = $targetMountPoint . substr($oldChildMount, strlen($mountPoint));
        try {
          $this->changeMountPoint($oldChildMount, $newChildMount, moveChildren: false);
        } catch (\Throwable $t) {
          $this->logException($t, 'Failed to move child mount from ' . $oldChildMount . ' to ' . $newChildMount);
        }
      }
    }
  }
}
