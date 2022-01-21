<?php

namespace OCA\CAFeVDBMembers\Controller;

use OCA\CAFeVDBMembers\AppInfo\Application;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\TemplateResponse;
use OCP\IRequest;
use OCP\Util;

class PageController extends Controller
{
  public function __construct(
    string $appName
    , IRequest $request
  ) {
    parent::__construct($appName, $request);
  }

  /**
   * @NoAdminRequired
   * @NoCSRFRequired
   *
   * Render default template
   */
  public function index() {
    Util::addScript($this->appName, 'cafevdbmembers-main');

    return new TemplateResponse($this->appName, 'main');
  }
}
