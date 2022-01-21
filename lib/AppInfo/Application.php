<?php

namespace OCA\CAFeVDBMembers\AppInfo;

use OCP\AppFramework\App;
use OCP\AppFramework\Bootstrap\IBootstrap;
use OCP\AppFramework\Bootstrap\IRegistrationContext;
use OCP\AppFramework\Bootstrap\IBootContext;

class Application extends App implements IBootstrap
{
  /** @var string */
  protected $appName;

  public function __construct()
  {
    $infoXml = new \SimpleXMLElement(file_get_contents(__DIR__ . '/../../appinfo/info.xml'));
    $this->appName = (string)$infoXml->id;
    parent::__construct($this->appName);
  }

  // Called later than "register".
  public function boot(IBootContext $context): void
  {
  }

  // Called earlier than boot, so anything initialized in the
  // "boot()" method must not be used here.
  public function register(IRegistrationContext $context): void
  {
    if ((@include_once __DIR__ . '/../../vendor/autoload.php') === false) {
      throw new \Exception('Cannot include autoload. Did you run install dependencies using composer?');
    }
  }
}
