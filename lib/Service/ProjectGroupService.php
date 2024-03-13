<?php
/**
 * Member's data base connector for CAFEVDB orchetra management app.
 *
 * @author Claus-Justus Heine <himself@claus-justus-heine.de>
 * @copyright Copyright (c) 2022-2024 Claus-Justus Heine
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

use InvalidArgumentException;
use RuntimeException;

use Psr\Log\LoggerInterface;
use OCP\IL10N;
use OCP\IGroupManager;
use OCP\IGroup;

use OCA\CAFeVDBMembers\Database\DBAL\Types\EnumProjectTemporalType as ProjectType;
use OCA\CAFEVDB\Events\PostProjectUpdatedEvent;
use OCA\CAFEVDB\Events\BeforeProjectDeletedEvent;
use OCA\CAFEVDB\Events\ProjectCreatedEvent;
use OCA\CAFEVDB\Service\CloudUserConnectorService;

use OCA\CAFeVDBMembers\Constants;

/** Manage the shared project-group folders. */
class ProjectGroupService
{
  use \OCA\CAFeVDBMembers\Toolkit\Traits\LoggerTrait;

  const GROUP_ID_PREFIX = Constants::CAFEVDB_APP_ID . CloudUserConnectorService::GROUP_ID_SEPARATOR;

  const MANAGEMENT_PERMISSIONS = GroupFoldersService::PERMISSION_ALL;
  const GROUP_LEAF_PERMISSIONS = GroupFoldersService::PERMISSION_DELETE|GroupFoldersService::PERMISSION_WRITE;
  const GROUP_PARENT_PERMISSIONS = GroupFoldersService::PERMISSION_READ;

  // phpcs:disable Squiz.Commenting.FunctionComment.Missing
  public function __construct(
    private string $appManagementGroup,
    private string $memberRootFolder,
    protected LoggerInterface $logger,
    protected IL10N $l,
    private IGroupManager $groupManager,
    private GroupFoldersService $groupFoldersService,
  ) {
  }
  // phpcs:enable

  /**
   * Make sure all folders for all projects exist.
   *
   * @param string $gid
   *
   * @return void
   */
  public function synchronizeFolderStructure(string $gid = null):void
  {
    if (empty($gid)) {
      throw new InvalidArgumentException($this->l->t('Syncing all groups in one run is no longer supported.'));
      $groups = $this->getProjectGroups();
    } else {
      if (!$this->isProjectGroup($gid)) {
        throw new InvalidArgumentException(
          $this->l->t('Group %1$s does not start with the correct prefix "%2$s".', [
            $gid, self::GROUP_ID_PREFIX,
          ]));
      }
      $groups = [ $this->groupManager->get($gid) ];
    }

    /** @var IGroup $group */
    foreach ($groups as $group) {
      $this->logDebug('Should handle ' . $group->getGID() . ' / ' . $group->getDisplayName());
      $this->ensureProjectFolder($group);
    }

    // remove empty "year" folders
    $this->removeOrphanFolders();
  }

  /**
   * @param string $gid
   *
   * @return bool
   */
  private function isProjectGroup(string $gid):bool
  {
    return str_starts_with($gid, self::GROUP_ID_PREFIX);
  }

  /**
   * Returns the list of the cafevdb created user groups.
   *
   * @return array
   */
  public function getProjectGroups():array
  {
    $groups = [];
    /** @var IGroup $group */
    foreach ($this->groupManager->search('') as $group) {
      if ($this->isProjectGroup($group->getGID())) {
        $groups[] = $group;
      }
    }
    return $groups;
  }

  /**
   * @param string $projectName
   *
   * @param array $parentMounts
   *
   * @return string
   */
  public function getProjectFolderMountPoint(string $projectName, array &$parentMounts = null):string
  {
    $projectYear = substr($projectName, -4);
    if (preg_match('/^\d{4}$/', $projectYear) !== 1) {
      $parentMounts = [ $this->memberRootFolder ];
    } else {
      $parentMounts = [ $this->memberRootFolder, $this->l->t('projects'), $projectYear ];
    }
    $leafMountPoint = implode('/', $parentMounts) . '/' . $projectName;
    $previousMounts = [];
    foreach ($parentMounts as &$parentMount) {
      $previousMounts[] = $parentMount;
      $parentMount = implode('/', $previousMounts);
    }
    return $leafMountPoint;
  }

  /**
   * @param int $projectId
   *
   * @return string
   */
  public function getProjectGroupId(int $projectId):string
  {
    return self::GROUP_ID_PREFIX . $projectId;
  }

  /**
   * Make sure a shared folder exists for the given group.
   *
   * @param IGroup $group
   *
   * @param null|string $forcedFolderName A forced folder-name. This
   * is primarily used to make sure that the new group display name is
   * used while renaming projects. If null the display name of the
   * group is used.
   *
   * @return void
   */
  public function ensureProjectFolder(IGroup $group, ?string $forcedFolderName = null):void
  {
    $groupId = $group->getGID();
    $groupName = $forcedFolderName ?? $group->getDisplayName();
    $leafMountPoint = $this->getProjectFolderMountPoint($groupName, $parentMounts);

    // ensure all parent mounts exist and are readable by the respective project-group
    foreach ($parentMounts as $parentMount) {
      $parentFolder = $this->groupFoldersService->getFolder($parentMount);
      if (empty($parentFolder)) {
        $this->groupFoldersService->createFolder(
          $parentMount, [
            $groupId => self::GROUP_PARENT_PERMISSIONS,
            $this->appManagementGroup => self::MANAGEMENT_PERMISSIONS,
          ], [
            $this->appManagementGroup => GroupFoldersService::MANAGER_TYPE_GROUP
          ]);
      } else {
        // add read-access to the parent-folder if not already
        $this->logDebug('ADD ' . $groupId);
        $this->groupFoldersService->addGroupToFolder(
          $parentMount,
          $groupId,
          self::GROUP_PARENT_PERMISSIONS,
          canManage: false);
        $this->logDebug('ADD ' . $this->appManagementGroup);
        $this->groupFoldersService->addGroupToFolder(
          $parentMount,
          $this->appManagementGroup,
          self::MANAGEMENT_PERMISSIONS,
          canManage: true);
      }
    }

    // finally ensure the leaf-folder is there and is writable by the project group
    $leafFolder = $this->groupFoldersService->getFolder($leafMountPoint);
    if (empty($leafFolder)) {
      $groupFolders = $this->searchWritableGroupFolders($group);
      if (count($groupFolders) == 1) {
        // just rename it
        $groupFolder = array_shift($groupFolders);
        $this->groupFoldersService->changeMountPoint($groupFolder['mount_point'], $leafMountPoint, moveChildren: true);
      } else {
        $this->groupFoldersService->createFolder(
          $leafMountPoint, [
            $groupId => self::GROUP_LEAF_PERMISSIONS,
            $this->appManagementGroup => self::MANAGEMENT_PERMISSIONS,
          ], [
            $this->appManagementGroup => GroupFoldersService::MANAGER_TYPE_GROUP
          ]);
      }
    }
    $leafFolder = $this->groupFoldersService->getFolder($leafMountPoint);
    if (empty($leafFolder)) {
      throw new RuntimeException($this->l->t('Unable to make sure the the group-shared folder "%1$s" for group "%2$s" exists.', [ $leafMountPoint, $group->getDisplayName() ]));
    }
    if ($leafFolder['groups'][$groupId]['permissions'] != self::GROUP_LEAF_PERMISSIONS) {
      // add write-access to the leaf-folder, this lazily just performs the necessary steps.
      $this->groupFoldersService->addGroupToFolder(
        $leafMountPoint,
        $groupId,
        self::GROUP_LEAF_PERMISSIONS,
        canManage: false);
    }
    if ($leafFolder['groups'][$this->appManagementGroup]['permissions'] != self::MANAGEMENT_PERMISSIONS) {
      // add write-access to the leaf-folder, this lazily just performs the necessary steps.
      $this->groupFoldersService->addGroupToFolder(
        $leafMountPoint,
        $this->appManagementGroup,
        self::MANAGEMENT_PERMISSIONS,
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
      if ($mountPoint !== $leafMountPoint && array_search($mountPoint, $parentMounts) === false) {
        $this->groupFoldersService->removeGroupFromFolder($mountPoint, $groupId);
        // maybe the contents should be moved to the current mount
      }
    }
  }

  /**
   * Search all folders for which the given group has write-access. Ideally,
   * this is just one. If so, this folder can simply be renamed when changing
   * the internal folder structure.
   *
   * @param IGroup $group
   *
   * @return array
   */
  private function searchWritableGroupFolders(IGroup $group):array
  {
    $groupId = $group->getGID();
    $groupFolders = [];
    foreach ($this->groupFoldersService->searchFolders($groupId, GroupFoldersService::SEARCH_TOPIC_GROUP) as $folderInfo) {
      if (($folderInfo['groups'][$groupId]['permissions'] & self::GROUP_LEAF_PERMISSIONS) != GroupFoldersService::PERMISSION_READ) {
        $groupFolders[] = $folderInfo;

      }
    }
    return $groupFolders;
  }

  /**
   * Remove all "orphan" folders, i.e. year-folders without projects.
   *
   * @return void
   */
  public function removeOrphanFolders():void
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
      $groupYear = substr($mountPoint, -5);
      if (preg_match('|^/\d{4}$|', $groupYear) !== 1) {
        continue;
      }
      if (!isset($parents[$mountPoint])) {
        $cleanList[] = $folderInfo;
      }
    }
    foreach ($cleanList as $folderInfo) {
      $this->groupFoldersService->deleteFolders($folderInfo['mount_point']);
    }
  }

  /**
   * @param PostProjectUpdatedEvent $event
   *
   * @return void
   */
  public function handleProjectRenamed(PostProjectUpdatedEvent $event):void
  {
    $groupId = $this->getProjectGroupId($event->getProjectId());
    $oldData = $event->getOldData();
    $newData = $event->getNewData();
    if ($oldData['type'] == ProjectType::TEMPLATE && $newData['type'] == ProjectType::TEMPLATE) {
      return;
    } elseif ($oldData['type'] == ProjectType::TEMPLATE) {
      $this->handleProjectCreatedEvent(new ProjectCreatedEvent(
        $newData['id'], $newData['name'], $newData['year'], $newData['type']
      ));
      return;
    } elseif ($newData['type'] == ProjectType::TEMPLATE) {
      $this->handleProjectDeletedEvent(new BeforeProjectDeletedEvent(
        $newData['id'], $newData['name'], $newData['year'], $newData['type']
      ));
      return;
    }

    if ($oldData['name'] != $newData['name']) {
      $oldMountPoint = $this->getProjectFolderMountPoint($oldData['name']);
      $newMountPoint = $this->getProjectFolderMountPoint($newData['name']);
      $folderInfo = $this->groupFoldersService->getFolder($oldMountPoint);
      if (!empty($folderInfo)) {
        $this->groupFoldersService->changeMountPoint($oldMountPoint, $newMountPoint);
      } else {
        $this->logInfo('No folder info for old mount-point ' . $oldMountPoint);
      }
      $group = $this->groupManager->get($groupId);
      if (!empty($group)) {
        if ($group->getDisplayName() != $newData['name']) {
          $this->logWarn('GROUP NAME IS STILL ' . $group->getDisplayName() . ' vs ' . $newData['name']);
        }
        $this->ensureProjectFolder($group, forcedFolderName: $newData['name']);
      } else {
        $this->logError('Cloud-group "' . $groupId . '" for project "' . $newData['name'] . ' does not exist.');
      }
    }
  }

  /**
   * @param BeforeProjectDeletedEvent $event
   *
   * @return void
   */
  public function handleProjectDeletedEvent(BeforeProjectDeletedEvent $event)
  {
    // $groupId = $this->getProjectGroupId($event->getProjectId());
    $mountPoint = $this->getProjectFolderMountPoint($event->getProjectName());
    $this->groupFoldersService->deleteFolders($mountPoint);
  }

  /**
   * @param ProjectCreatedEvent $event
   *
   * @return void
   */
  public function handleProjectCreatedEvent(ProjectCreatedEvent $event)
  {
    $groupId = $this->getProjectGroupId($event->getProjectId());
    $projectName = $event->getProjectName();
    $group = $this->groupManager->get($groupId);
    if (!empty($group)) {
      $this->ensureProjectFolder($group);
    } else {
      $this->logError('Cloud-group "' . $groupId . '" for project "' . $projectName . ' does not exist.');
    }
  }
}
