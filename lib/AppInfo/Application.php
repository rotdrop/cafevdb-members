<?php
/**
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

// phpcs:disable PSR1.Files.SideEffects

namespace OCA\CAFeVDBMembers\AppInfo;

use NumberFormatter;
use Exception;

use OCP\AppFramework\App;
use OCP\AppFramework\Bootstrap\IBootstrap;
use OCP\AppFramework\Bootstrap\IRegistrationContext;
use OCP\AppFramework\Bootstrap\IBootContext;
use OCP\AppFramework\Services\IInitialState;
use OCP\IConfig;

use Psr\Container\ContainerInterface;

use OCA\CAFeVDBMembers\Listener\Registration as ListenerRegistration;

include_once __DIR__ . '/../../vendor/autoload.php';

/** Cloud application entry point. */
class Application extends App implements IBootstrap
{
  use \OCA\CAFeVDBMembers\Toolkit\Traits\AppNameTrait;

  const CAFEVDB_APP = 'cafevdb';

  const DEFAULT_LOCALE_KEY = 'DefaultLocale';
  const DEFAULT_LOCALE = 'en_US';

  /** @var string */
  protected $appName;

  // phpcs:disable Squiz.Commenting.FunctionComment.Missing
  public function __construct()
  {
    $this->appName = $this->getAppInfoAppName(__DIR__);
    parent::__construct($this->appName);
  }
  // phpcs:enable Squiz.Commenting.FunctionComment.Missing

  /** {@inheritdoc} */
  public function boot(IBootContext $context):void
  {
    $context->injectFn(function(IInitialState $initialState, IConfig $config) {
      $orchestraLocale = $config->getAppValue(self::CAFEVDB_APP, 'orchestraLocale', self::DEFAULT_LOCALE);
      $fmt = new NumberFormatter($orchestraLocale, NumberFormatter::CURRENCY);
      $currencySymbol = $fmt->getSymbol(NumberFormatter::CURRENCY_SYMBOL);
      $currencyCode = $fmt->getTextAttribute(NumberFormatter::CURRENCY_CODE);
      $initialState->provideInitialState('config', [
        'orchestraName' => $config->getAppValue(self::CAFEVDB_APP, 'orchestra'),
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
