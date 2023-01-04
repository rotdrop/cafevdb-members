<?php
/**
 * Member's data base connector for CAFEVDB orchetra management app.
 *
 * @author Claus-Justus Heine <himself@claus-justus-heine.de>
 * @copyright Copyright (c) 2022 Claus-Justus Heine
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

use OCA\CAFeVDBMembers\AppInfo\Application;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\TemplateResponse;
use OCP\IRequest;
use OCP\Util;

/** AJAX endpoint for generating the main page of the app. */
class PageController extends Controller
{
  // phpcs:ignore Squiz.Commenting.FunctionComment.Missing
  public function __construct(
    string $appName,
    IRequest $request,
  ) {
    parent::__construct($appName, $request);
  }
  // phpcs:enable

  /**
   * Render default template
   *
   * @return TemplateResponse
   *
   * @NoAdminRequired
   * @NoCSRFRequired
   */
  public function index():TemplateResponse
  {
    Util::addScript($this->appName, 'cafevdbmembers-main');

    return new TemplateResponse($this->appName, 'main');
  }
}
