<?php
/**
 * Member's data base connector for CAFEVDB orchetra management app.
 *
 * @author Claus-Justus Heine <himself@claus-justus-heine.de>
 * @copyright Copyright (c) 2022, 2023, 2024 Claus-Justus Heine>
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

use RuntimeException;

use Psr\Log\LoggerInterface;
use OCP\IL10N;
use OCP\Constants;
use OCP\Files\IRootFolder;

use OCA\GroupFolders\Folder\FolderManager;
use OCA\GroupFolders\Mount\MountProvider;

use OCA\CAFeVDBMembers\Toolkit\Service\GroupFoldersService as ToolkitService;

/**
 * Mis-use the internal routes of the groupfolders app in order to
 * automatically generate the requried shared folder structure for the
 * orchestra members, in particular shared storage for photos.
 */
class GroupFoldersService extends ToolkitService
{
  // phpcs:disable Squiz.Commenting.FunctionComment.Missing
  public function __construct(
    LoggerInterface $logger,
    IL10N $l10n,
    IRootFolder $rootFolder,
    FolderManager $folderManager,
    MountProvider $mountProvider,
  ) {
    parent::__construct(
      logger: $logger,
      rootFolder: $rootFolder,
      folderManager: $folderManager,
      mountProvider: $mountProvider,
      l: $l10n,
    );
  }
  // phpcs:enable Squiz.Commenting.FunctionComment.Missing
}
