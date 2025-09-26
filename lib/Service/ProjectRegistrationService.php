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

use OCP\IL10N;
use Psr\Log\LoggerInterface;
use OCP\Files\SimpleFS\ISimpleRoot;

use OCA\CAFeVDBMembers\Toolkit\Traits as ToolkitTraits;
use OCA\CAFeVDBMembers\Toolkit\Service\AppStorageDisclosure;

/**
 * Service class for managing project registrations, generating shares,
 * notifications and so on.
 */
class ProjectRegistrationService
{
  use ToolkitTraits\LoggerTrait;

  // phpcs:disable Squiz.Commenting.FunctionComment.Missing
  public function __construct(
    protected AppStorageDisclosure $appStorage,
    protected LoggerInterface $logger,
  ) {
  }
  // phpcs:enable Squiz.Commenting.FunctionComment.Missing

  /**
   * Data submission, this is more-or-less the main entry point.
   *
   * @param array $data The user submitted registration data.
   *
   * @return void
   */
  public function handleSubmission(array $data): void
  {
    $this->logInfo('Submission Data ' . print_r($data, true));

  }
}
