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
use OCP\AppFramework\Http\DataResponse;
use OCP\IRequest;
use OCP\IL10N;

/** Attempt a catch-all controller to improve error messages. */
class CatchAllController extends Controller
{
  use \OCA\CAFeVDBMembers\Traits\ResponseTrait;

  /** @var IL10N */
  private $l;

  // phpcs:ignore Squiz.Commenting.FunctionComment.Missing
  public function __construct(
    string $appName,
    IRequest $request,
    IL10N $l10n,
  ) {
    parent::__construct($this->appName, $request);
    $this->l = $l10n;
  }
  // phpcs:enable

  /**
   * @param mixed $a
   *
   * @param mixed $b
   *
   * @param mixed $c
   *
   * @param mixed $d
   *
   * @param mixed $e
   *
   * @param mixed $f
   *
   * @param mixed $g
   *
   * @return DataResponse
   *
   * @NoAdminRequired
   * @NoCSRFRequired
   */
  public function post(
    mixed $a,
    mixed $b,
    mixed $c,
    mixed $d,
    mixed $e,
    mixed $f,
    mixed $g,
  ):DataResponse {
    $parts = [ $a, $b, $c, $d, $e, $f, $g ];
    $request = implode('/', array_filter($parts));
    if (!empty($request)) {
      return self::grumble(
        $this->l->t('Post to end-point "%s" not implemented.', $request));
    } else {
      return self::grumble(
        $this->l->t('Post to base-url of app "%s" not allowed.', $this->appName()));
    }
  }

  /**
   * @param mixed $a
   *
   * @param mixed $b
   *
   * @param mixed $c
   *
   * @param mixed $d
   *
   * @param mixed $e
   *
   * @param mixed $f
   *
   * @param mixed $g
   *
   * @return DataResponse
   *
   * @NoAdminRequired
   * @NoCSRFRequired
   */
  public function get(
    mixed $a,
    mixed $b,
    mixed $c,
    mixed $d,
    mixed $e,
    mixed $f,
    mixed $g,
  ):DataResponse {
    $parts = [ $a, $b, $c, $d, $e, $f, $g ];
    $request = implode('/', array_filter($parts));
    return self::grumble(
      $this->l->t('Get from end-point "%s" not implemented.', $request));
  }
}
