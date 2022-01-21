<?php
/**
 * @copyright Copyright (c) 2022 Claus-Justus Heine <himself@claus-justus-heine.de>
 *
 * @author Claus-Justus Heine <himself@claus-justus-heine.de>
 *
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
 *
 */

namespace OCA\CAFeVDBMembers\Database\ORM;

use OCP\AppFramework\IAppContainer;
use OCP\IConfig;

use OCA\CAFEVDB\Wrapped\Doctrine\ORM\Tools\Setup;
use OCA\CAFEVDB\Wrapped\Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Decorator\EntityManagerDecorator;
use Doctrine\DBAL\Connection as DatabaseConnection;

/**
 * Use this as the actual EntityManager in order to be able to
 * construct it without a Factory and to define an extension point for
 * later.
 */
class EntityManager extends EntityManagerDecorator
{
  const ENTITY_PATHS = [
    __DIR__ . "/Entities",
  ];
  const PROXY_DIR = __DIR__ . "/Proxies";
  const DEV_MODE = true;

  /** @var EntityManagerInterface */
  private $entityManager;

  /** @var string */
  private $appName;

  /** @var IConfig */
  private $cloudConfig;

  /** @var IAppContainer */
  private $appContainer;

  public function __construct(
    $appname
    , IAppContainer $appContainer
    , IConfig $cloudConfig
  ) {
    $this->appName = $appName;
    $this->appContainer = $appContainer;
    $this->cloudConfig = $cloudConfig;
    parent::__construct($this->getEntityManager());
    $this->entityManager = $this->wrapped;
  }

  private function createSimpleConfiguration():array
  {
    $cache = null;
    $useSimpleAnnotationReader = false;
    $config = Setup::createAnnotationMetadataConfiguration(self::ENTITY_PATHS, self::DEV_MODE, self::PROXY_DIR, $cache, $useSimpleAnnotationReader);
    $config->setEntityListenerResolver(new class($this->appContainer) extends ORM\Mapping\DefaultEntityListenerResolver {

      private $appContainer;

      public function __construct(IAppContainer $appContainer)
      {
        $this->appContainer = $appContainer;
      }

      public function resolve($className)
      {
        try {
          return parent::resolve($className);
        } catch (\Throwable $t) {
          $this->register($object = $this->appContainer->get($className));
          return $object;
        }
      }
    });

    // mysql set names UTF-8 if required
    $eventManager->addEventSubscriber(new \OCA\CAFEVDB\Wrapped\Doctrine\DBAL\Event\Listeners\MysqlSessionInit());

    $eventManager->addEventListener([
      \OCA\CAFEVDB\Wrapped\Doctrine\ORM\Tools\ToolEvents::postGenerateSchema,
      ORM\Events::loadClassMetadata,
      ORM\Events::preUpdate,
      ORM\Events::postUpdate,
    ], $this);


    return [ $config, new \OCA\CAFEVDB\Wrapped\Doctrine\Common\EventManager(), ];
  }

  private function connectionParameters($params = null) {
    $connectionParams = [
      'dbname' => $this->cloudConfig->getAppValue($this->appName, 'database', $this->cloudConfig->getSystemValue('dbname') . '_' . $this->appName),
      'user' => $this->cloudConfig->getSystemValue('dbuser'),
      'password' => $this->cloudConfig->getSystemvalue('dbpassword'),
      'host' => $this->cloudConfig->getSystemValue('dbhost'),
    ];
    $driverParams = [
      'driver' => 'pdo_mysql',
      'wrapperClass' => DatabaseConnection::class,
    ];
    $charSetParams = [
      'collate' => 'utf8mb4_bin',
      'charset' => 'utf8mb4',
    ];
    !is_array($params) && ($params = []);
    $connectionParams = array_merge($connectionParams, $params, $driverParams, $charSetParams);
    return $connectionParams;
  }

  private function getEntityManager($params = [])
  {
    list($config, $eventManager) = $this->createSimpleConfiguration();

    if (self::DEV_MODE) {
      $config->setAutoGenerateProxyClasses(true);
    } else {
      $config->setAutoGenerateProxyClasses(false);
    }

    $namingStrategy = new UnderscoreNamingStrategy(CASE_LOWER);
    $config->setNamingStrategy($namingStrategy);

    $quoteStrategy = new ReservedWordQuoteStrategy();
    $config->setQuoteStrategy($quoteStrategy);

    \Doctrine\ORM\EntityManager::create($this->connectionParameters($params), $config, $eventManager);

    return $entityManager;
  }
}
