<?php
/**
 * Member's data base connector for CAFEVDB orchetra management app.
 *
 * @author Claus-Justus Heine <himself@claus-justus-heine.de>
 * @copyright Copyright (c) 2023-2025 Claus-Justus Heine
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

use OCP\AppFramework\Http\Template\PublicTemplateResponse;
use OCP\AppFramework\Http\Attribute;
use OCP\IRequest;
use OCP\ISession;
use OCP\IURLGenerator;
use OCP\Share\IShare;

/**
 * Playground, test out the authenticated public share controller in order to
 * evaluate its usefulness.
 */
class PublicPageController extends AuthPublicShareController
{
  protected IShare $share;

  // phpcs:ignore Squiz.Commenting.FunctionComment.Missing
  public function __construct(
    string $appName,
    IRequest $request,
    ISession $session,
    IURLGenerator $urlGenerator,
  ) {
    parent::__construct($appName, $request, $session, $urlGenerator);
  }
  // phpcs:enable

  /** {@inheritdoc} */
  #[Attribute\PublicPage]
  #[Attribute\NoCsRFRequired]
  public function showShare(): PublicTemplateResponse
  {
    return new PublicTemplateResponse($this->appName, 'public-auth-test', []);
  }
}
