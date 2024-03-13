<?php
/**
 * Member's data base connector for CAFEVDB orchetra management app.
 *
 * @author Claus-Justus Heine <himself@claus-justus-heine.de>
 * @copyright Copyright (c) 2022-2024 Claus-Justus Heine>
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

namespace OCA\CAFeVDBMembers\Command;

use OCP\IL10N;
use OCP\IGroup;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Helper\ProgressBar;

use OCA\CAFeVDBMembers\Service\ProjectGroupService;

/** Synchronize the group-folders structure with the existing orchestra projects. */
class EnsureGroupFolders extends Command
{
   /** {@inheritdoc} */
  public function __construct(
    private string $appName,
    protected IL10N $l,
    private ProjectGroupService $projectGroupsService,
  ) {
    parent::__construct();
  }

  /** {@inheritdoc} */
  protected function configure()
  {
    $this
      ->setName($this->appName . ':groupfolders:ensure')
      ->setDescription($this->l->t('Ensure the group-folders structure is in sync with the orchestra projects'))
      ;
  }

   /** {@inheritdoc} */
  protected function execute(InputInterface $input, OutputInterface $output): int
  {
    $projectGroups = $this->projectGroupsService->getProjectGroups();

    $section = $output->section();
    $progress = new ProgressBar($section);
    $progress->start(count($projectGroups));

    /** @var IGroup $group */
    foreach ($projectGroups as $group) {
      $output->writeln($this->l->t('Synchronizing folder structure for group "%1$s" (%2$s).', [
        $group->getDisplayName(), $group->getGID()
      ]), OutputInterface::VERBOSITY_VERBOSE);
      $this->projectGroupsService->synchronizeFolderStructure($group->getGID());
      $progress->advance();
    }
    $progress->finish();

    return 0;
  }
}
