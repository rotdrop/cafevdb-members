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

namespace OCA\CAFeVDBMembers\Service;

use InvalidArgumentException;
use RuntimeException;

use OCP\IRequest;
use OCP\IURLGenerator;
use OCP\ISession;
use Psr\Log\LoggerInterface;
use OCP\IL10N;

use OCA\CAFeVDBMembers\Toolkit\Service\RequestService as ToolkitService;

/** Post to local routes on the same server. */
class RequestService extends ToolkitService
{
  // phpcs:disable Squiz.Commenting.FunctionComment.Missing
  public function __construct(
    IRequest $request,
    IURLGenerator $urlGenerator,
    ISession $session,
    LoggerInterface $logger,
    IL10N $l10n,
    bool $closeSession = true,
  ) {
    parent::__construct($request, $urlGenerator, $session, $logger, $l10n, $closeSession);
  }
  // phpcs:enable Squiz.Commenting.FunctionComment.Missing
}
