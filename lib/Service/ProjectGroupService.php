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
use OCP\IGroupManager;
use OCP\IGroup;

use OCA\CAFEVDB\Events\ProjectUpdatedEvent;
use OCA\CAFEVDB\Events\BeforeProjectDeletedEvent;
use OCA\CAFEVDB\Events\ProjectCreatedEvent;

class ProjectGroupService
{
  use \OCA\CAFeVDBMembers\Traits\LoggerTrait;

  const GROUP_ID_PREFIX = 'cafevdb:';

  /** @var IL10N */
  private $l;

  /** @var IGroupManager */
  private $groupManager;

  /** @var GroupFoldersService */
  private $groupFoldersService;

  /** @var string */
  private $memberRootFolder;

  /** @var string */
  private $appManagementGroup;

  public function __construct(
    string $appManagementGroup
    , string $memberRootFolder
    , LoggerInterface $logger
    , IL10N $l10n
    , IGroupManager $groupManager
    , GroupFoldersService $groupFoldersService
  ) {
    $this->appManagementGroup = $appManagementGroup;
    $this->memberRootFolder = $memberRootFolder;
    $this->logger = $logger;
    $this->l = $l10n;
    $this->groupManager = $groupManager;
    $this->groupFoldersService = $groupFoldersService;
  }

  /** Make sure all folders for all projects exist */
  public function synchronizeFolderStructure()
  {
    /** @var IGroup $group */
    foreach ($this->groupManager->search('') as $group) {
      if (!str_starts_with($group->getGID(), self::GROUP_ID_PREFIX)) {
        continue;
      }
      $this->logDebug('Should handle ' . $group->getGID() . ' / ' . $group->getDisplayName());
      // $mountPoint = $this->getProjectFolderMountPoint($group->getDisplayName(), $yearMount);
      $this->ensureProjectFolder($group);
    }
  }

  public function getProjectFolderMountPoint(string $projectName, ?string &$yearMount = null):string
  {
    $projectYear = substr($projectName, -4);
    if (preg_match('/^\d{4}$/', $projectYear) !== 1) {
      $yearMount = $this->memberRootFolder;
    } else {
      $yearMount = $this->memberRootFolder . '/' . $projectYear;
    }
    $leafMountPoint = $yearMount . '/' . $projectName;
    return $leafMountPoint;
  }

  public function getProjectGroupId(int $projectId):string
  {
    return self::GROUP_ID_PREFIX . $projectId;
  }

  /**
   * Make sure a shared folder exists for the given group.
   */
  public function ensureProjectFolder(IGroup $group)
  {
    $groupId = $group->getGID();
    $groupName = $group->getDisplayName();
    $leafMountPoint = $this->getProjectFolderMountPoint($groupName, $yearMount);

    // add read-access to the root-folder
    $this->groupFoldersService->addGroupToFolder(
      $this->memberRootFolder,
      $groupId,
      GroupFoldersService::PERMISSION_READ,
      canManage: false);

    if ($yearMount != $this->memberRootFolder) {
      // add read-access to the year-folder
      $yearFolder = $this->groupFoldersService->getFolder($yearMount);
      $yearPermissions = GroupFoldersService::PERMISSION_READ;
      if (empty($yearFolder)) {
        $this->groupFoldersService->createFolder(
          $yearMount, [
            $groupId => $yearPermissions,
            $this->appManagementGroup => GroupFoldersService::DEFAULT_PERMISSIONS,
          ], [
            $this->appManagementGroup => GroupFoldersService::MANAGER_TYPE_GROUP
          ]);
      } else {
        // add read-access to the year-folder if not already
        $this->logDebug('ADD ' . $groupId);
        $this->groupFoldersService->addGroupToFolder(
          $yearMount,
          $groupId,
          GroupFoldersService::PERMISSION_READ,
          canManage: false);
        $this->logDebug('ADD ' . $this->appManagementGroup);
        $this->groupFoldersService->addGroupToFolder(
          $yearMount,
          $this->appManagementGroup,
          GroupFoldersService::DEFAULT_PERMISSIONS,
          canManage: true);
      }
    }

    $leafPermissions = GroupFoldersService::PERMISSION_DELETE|GroupFoldersService::PERMISSION_WRITE;
    $leafFolder = $this->groupFoldersService->getFolder($leafMountPoint);
    if (empty($leafFolder)) {
      $this->groupFoldersService->createFolder(
        $leafMountPoint, [
          $groupId => $leafPermissions,
          $this->appManagementGroup => GroupFoldersService::DEFAULT_PERMISSIONS,
        ], [
          $this->appManagementGroup => GroupFoldersService::MANAGER_TYPE_GROUP
        ]);
    } else {
      // add write-access to the leaf-folder, this lazily just performs the necessary steps.
      $this->groupFoldersService->addGroupToFolder(
        $leafMountPoint,
        $groupId,
        GroupFoldersService::PERMISSION_DELETE|GroupFoldersService::PERMISSION_WRITE,
        canManage: false);
      // add write-access to the leaf-folder, this lazily just performs the necessary steps.
      $this->groupFoldersService->addGroupToFolder(
        $leafMountPoint,
        $this->appManagementGroup,
        GroupFoldersService::DEFAULT_PERMISSIONS,
        canManage: true);
    }

    // finally remove left-over entries
    foreach ($this->groupFoldersService->searchFolders($groupId, GroupFoldersService::SEARCH_TOPIC_GROUP) as $folderInfo) {
      $mountPoint =  $folderInfo['mount_point'];
      if ($mountPoint == $this->memberRootFolder) {
        // skip root-folder
        continue;
      }
      if (!str_starts_with($mountPoint, $this->memberRootFolder)) {
        continue;
      }
      if ($mountPoint != $leafMountPoint && $mountPoint != $yearMount) {
        $this->groupFoldersService->removeGroupFromFolder($mountPoint, $groupId);
      }
    }
  }

  /**
   * Remove all "orphan" folders, i.e. year-folders without projects.
   */
  public function removeOrphanFolders()
  {
    $allFolders = $this->groupFoldersService->searchFolders('|^' . $this->memberRootFolder . '.*$|');
    $cleanList = [];
    $parents = [];
    foreach ($allFolders as $index => $folderInfo) {
      if (empty($folderInfo['groups'])) {
        $cleanList[] = $folderInfo;
        unset($allFolders[$index]);
        continue;
      }
      $parents[dirname($folderInfo['mount_point'])] = true;
    }
    foreach ($allFolders as $index => $folderInfo) {
      $mountPoint = $folderInfo['mount_point'];
      $groupYear = substr($mountPoint, -4);
      if (preg_match('/^\d{4}$/', $groupYear) !== 1) {
        continue;
      }
      if (!isset($parents[$mountPoint])) {
        $cleanList[] = $folderInfo;
      }
    }
    foreach ($cleanList as $folderInfo) {
      this->groupFoldersService->deleteFolders($folderInfo['mount_point']);
    }
  }

  public function handleProjectRenamed(ProjectUpdatedEvent $event)
  {
    $groupId = $this->getProjectGroupId($event->getProjectId());
    $oldData = $event->getOldData();
    $newData = $event->getNewData();
    if ($oldData['name'] != $newData['name']) {
      $oldMountPoint = $this->getProjectFolderMountPoint($oldData['name']);
      $newMountPoint = $this->getProjectFolderMountPoint($newData['name']);
      $folderInfo = $this->groupFoldersService->getFolder($oldMountPoint);
      if (!empty($folderInfo)) {
        $this->groupFoldersService->changeMountPoint($oldMountPoint, $newMountPoint);
      }
      $group = $this->groupManager->get($groupId);
      if (!empty($group)) {
        $this->ensureProjectFolder($group);
      } else {
        $this->logError('Cloud-group "' . $groupId . '" for project "' . $newData['name'] . ' does not exist.');
      }
    }
  }

  public function handleProjectDeletedEvent(BeforeProjectDeletedEvent $event)
  {
    // $groupId = $this->getProjectGroupId($event->getProjectId());
    $mountPoint = $this->getProjectFolderMountPoint($event->getProjectName());
    $this->groupFoldersService->deleteFolders($mountPoint);
  }

  public function handleProjectCreatedEvent(ProjectCreatedEvent $event)
  {
    $groupId = $this->getProjectGroupId($event->getProjectId());
    $projectName = $event->getProjectName();
    $group = $this->groupManager->get($groupId);
    if (!empty($group)) {
      $this->ensureProjectFolder($group);
    } else {
      $this->logError('Cloud-group "' . $groupId . '" for project "' . $newData['name'] . ' does not exist.');
    }
  }
}
