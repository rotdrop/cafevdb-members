<?php
/**
 * Member's data base connector for CAFEVDB orchetra management app.
 *
 * @author Claus-Justus Heine <himself@claus-justus-heine.de>
 * @copyright Copyright (c) 2022, 2023, 2026 Claus-Justus Heine
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

// phpcs:disable PSR1.Files.SideEffects

namespace OCA\CAFeVDBMembers\AppInfo;

use NumberFormatter;
use Exception;

use OCP\AppFramework\App;
use OCP\AppFramework\Bootstrap\IRegistrationContext;
use OCP\AppFramework\Bootstrap\IBootContext;
use OCP\AppFramework\Services\IInitialState;
use OCP\IConfig;

use Psr\Container\ContainerInterface;

use OCA\CAFEVDB;
use OCA\CAFeVDBMembers\Database\Registration as DatabaseRegistration;
use OCA\CAFeVDBMembers\Listener\Registration as ListenerRegistration;
use OCA\CAFeVDBMembers\Settings\ConfigConstants;
use OCA\CAFeVDBMembers\Toolkit\AppInfo\AbstractApplication;

include_once __DIR__ . '/../Toolkit/AppInfo/AbstractApplication.php';

/** Cloud application entry point. */
class Application extends AbstractApplication
{
  const DEFAULT_LOCALE_KEY = 'DefaultLocale';
  const DEFAULT_LOCALE = 'en_US';

  protected static string $orchestraAppName;

  /**
   * Reads off the app-name from the info.xml file.
   *
   * @return string
   */
  public static function getOrchestraAppName(): string
  {
    return self::$orchestraAppName ?? (self::$orchestraAppName = CAFEVDB\AppInfo\Application::getAppName());
  }

  /** {@inheritdoc} */
  public function boot(IBootContext $context):void
  {
    $context->injectFn(function(IInitialState $initialState, IConfig $config) {
      self::getOrchestraAppName();
      $orchestraLocale = $config->getAppValue(self::$orchestraAppName, CAFEVDB\Settings\ConfigConstants::ORCHESTRA_LOCALE_KEY, self::DEFAULT_LOCALE);
      $fmt = new NumberFormatter($orchestraLocale, NumberFormatter::CURRENCY);
      $currencySymbol = $fmt->getSymbol(NumberFormatter::CURRENCY_SYMBOL);
      $currencyCode = $fmt->getTextAttribute(NumberFormatter::CURRENCY_CODE);
      self::getOrchestraAppName();
      $initialState->provideInitialState('config', [
        'orchstraAppName' => self::$orchestraAppName,
        'orchestraName' => $config->getAppValue(self::$orchestraAppName, CAFEVDB\Settings\ConfigConstants::ORCHESTRA_NAME_KEY),
        'orchestraLocale' => $orchestraLocale,
        'currencySymbol' => $currencySymbol,
        'currencyCode' => $currencyCode,
      ]);
    });
  }

  /**
   * {@inheritdoc}
   *
   * Called earlier than boot, so anything initialized in the "boot()" method
   * must not be used here.
   */
  public function register(IRegistrationContext $context):void
  {
    parent::register($context);

    $context->registerService('orchestraAppName', fn($c) => self::getOrchestraAppName());
    $context->registerService('appManagementGroup', function($c) {
      self::getOrchestraAppName();
      /** @var \OCP\IConfig $config */
      $config = $c->get(\OCP\IConfig::class);
      return $config->getAppValue(self::$orchestraAppName, CAFEVDB\Settings\ConfigConstants::USER_GROUP_KEY);
    });
    $context->registerService(ConfigConstants::MEMBER_ROOT_FOLDER_KEY, function($c) {
      self::getAppName();
      /** @var \OCP\IConfig $config */
      $config = $c->get(\OCP\IConfig::class);
      return $config->getAppValue(self::$appName, ConfigConstants::MEMBER_ROOT_FOLDER_KEY);
    });

    $context->registerService(ucfirst(self::DEFAULT_LOCALE_KEY), function(ContainerInterface $container) {
      return self::DEFAULT_LOCALE;
    });
    $context->registerServiceAlias(lcfirst(self::DEFAULT_LOCALE), ucfirst(self::DEFAULT_LOCALE));

    DatabaseRegistration::register($context);
    ListenerRegistration::register($context);
  }
}
