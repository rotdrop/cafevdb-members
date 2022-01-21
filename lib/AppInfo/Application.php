<?php

namespace OCA\CAFeVDBMembers\AppInfo;

use OCP\AppFramework\App;

class Application extends App {
  public const APP_ID = 'cafevdbmembers';

  public function __construct() {
    parent::__construct(self::APP_ID);
  }
}
