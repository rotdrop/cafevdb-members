<?php
/**
 * @copyright Copyright (c) 2022 Claus-Justus Heine <himself@claus-justus-heine.de>
 * @author Claus-Justus Heine <himself@claus-justus-heine.de>
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

namespace OCA\CAFeVDBMembers\AppInfo;

use OCP\AppFramework\App;
use OCP\AppFramework\Bootstrap\IBootstrap;
use OCP\AppFramework\Bootstrap\IRegistrationContext;
use OCP\AppFramework\Bootstrap\IBootContext;
use OCP\AppFramework\Services\IInitialState;

use Psr\Container\ContainerInterface;

use OCA\CAFeVDBMembers\Listener\Registration as ListenerRegistration;

class Application extends App implements IBootstrap
{
  const CAFEVDB_APP = 'cafevdb';

  const DEFAULT_LOCALE_KEY = 'DefaultLocale';
  const DEFAULT_LOCALE = 'en_US';

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
    $context->injectFn(function(IInitialState $initialState) {
      $initialState->provideInitialState('testValue', '*** INITIAL STATE OF TEST VALUE ***');
    });

    // The following would restrict the settings to the sub-admins of the
    // orechstra group only. Another approach would be to put the sub-admins
    // into a separate group and use the ordinary admin-delegation to handle
    // the restriction.
    //
    // $context->injectFn(function(
    //   string $appManagementGroup
    //   , \OCP\IUserSession $userSession
    //   , \OCP\IGroupManager $groupManager
    //   , \OCP\Group\ISubAdmin $groupSubAdmin
    //   , \OCP\Settings\IManager $settingsManager
    // ) {
    //   /** @var \OCP\IUser $user */
    //   $user = $userSession->getUser();

    //   if (empty($user)
    //       || empty($appManagementGroup)
    //       || !$groupManager->isInGroup($user->getUID(), $appManagementGroup)
    //       || !$groupSubAdmin->isSubAdminOfGroup($user, $groupManager->get($appManagementGroup))
    //   ) {
    //     return;
    //   }
    //   $settingsManager->registerSection('admin', \OCA\CAFeVDBMembers\Settings\AdminSection::class);
    //   $settingsManager->registerSetting('admin', \OCA\CAFeVDBMembers\Settings\Admin::class);
    // });
  }

  // Called earlier than boot, so anything initialized in the
  // "boot()" method must not be used here.
  public function register(IRegistrationContext $context): void
  {
    if ((@include_once __DIR__ . '/../../vendor/autoload.php') === false) {
      throw new \Exception('Cannot include autoload. Did you run install dependencies using composer?');
    }
    $context->registerService('appManagementGroup', function($c) {
      /** @var \OCP\IConfig $config */
      $config = $c->get(\OCP\IConfig::class);
      return $config->getAppValue(self::CAFEVDB_APP, 'usergroup');
    });
    $context->registerService('memberRootFolder', function($c) {
      /** @var \OCP\IConfig $config */
      $config = $c->get(\OCP\IConfig::class);
      return $config->getAppValue($this->appName, 'memberRootFolder');
    });

    $context->registerService(ucfirst(self::DEFAULT_LOCALE_KEY), function(ContainerInterface $container) {
      return self::DEFAULT_LOCALE;
    });
    $context->registerServiceAlias(lcfirst(self::DEFAULT_LOCALE), ucfirst(self::DEFAULT_LOCALE));

    // Register listeners
    ListenerRegistration::register($context);
  }
}
