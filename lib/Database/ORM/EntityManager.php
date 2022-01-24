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

use Psr\Log\LoggerInterface;

use OCP\AppFramework\IAppContainer;
use OCP\IConfig;

use Doctrine\ORM\Tools\Setup;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\DBAL\Event\ConnectionEventArgs;
use Doctrine\ORM\Decorator\EntityManagerDecorator;
use Doctrine\DBAL\Connection as DatabaseConnection;

/**
 * Use this as the actual EntityManager in order to be able to
 * construct it without a Factory and to define an extension point for
 * later.
 */
class EntityManager extends EntityManagerDecorator
{
  use \OCA\CAFeVDBMembers\Traits\LoggerTrait;

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

  /** @var string */
  private $userId;

  /** @var bool */
  private $typesBound = false;

  public function __construct(
    $appname
    , $userId
    , IAppContainer $appContainer
    , IConfig $cloudConfig
    , LoggerInterface $logger
  ) {
    $this->appName = $appName;
    $this->userId = $userId;
    $this->appContainer = $appContainer;
    $this->cloudConfig = $cloudConfig;
    $this->logger = $logger;
    parent::__construct($this->getEntityManager());
    $this->entityManager = $this->wrapped;
    if ($this->connected()) {
      $this->registerTypes();
    }
  }

  private function createConfiguration():array
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

    // mysql set names UTF-8 if required, should be obsolete
    // $eventManager->addEventSubscriber(new \OCA\CAFEVDB\Wrapped\Doctrine\DBAL\Event\Listeners\MysqlSessionInit());

    $eventManager->addEventListener([
      // \OCA\CAFEVDB\Wrapped\Doctrine\ORM\Tools\ToolEvents::postGenerateSchema,
      // ORM\Events::loadClassMetadata,
      // ORM\Events::preUpdate,
      // ORM\Events::postUpdate,
      Events::postConnect,
    ], $this);


    return [ $config, new \OCA\CAFEVDB\Wrapped\Doctrine\Common\EventManager(), ];
  }

  private function connectionParameters($params = []) {
    $connectionParams = [
      // 'dbname' => $this->cloudConfig->getAppValue(
      //   $this->appName,
      //   'database',
      //   $this->cloudConfig->getSystemValue('dbname') . '_' . $this->appName
      // ),
      'database' => 'nextcloud_cafevdb', // @todo make it configurable
      'user' => $this->cloudConfig->getSystemValue('dbuser'),
      'password' => $this->cloudConfig->getSystemvalue('dbpassword'),
      'host' => $this->cloudConfig->getSystemValue('dbhost'),
    ];
    $driverParams = [
      'driver' => 'pdo_mysql',
    ];
    $charSetParams = [
      'collate' => 'utf8mb4_unicode_520_ci',
      'charset' => 'utf8mb4',
    ];
    $connectionParams = array_merge($connectionParams, $params, $driverParams, $charSetParams);
    return $connectionParams;
  }

  /**
   * Check for a valid database connection.
   *
   * @return bool
   */
  public function connected():bool
  {
    $connection = $this->getConnection();
    if (empty($connection)) {
      return false;
    }
    $params = $connection->getParams();
    $impossible = false;
    foreach ([ 'host', 'user', 'password', 'dbname' ] as $key) {
      if (empty($params[$key])) {
        $impossible = true;
      }
    }
    if ($impossible) {
      $this->logError('Unable to access database, connection parameters are unset');
      return false;
    }
    try {
      if (!$connection->ping()) {
        if (!$connection->connect()) {
          $this->logError('db cannot connect');
          return false;
        }
      }
    } catch (\Throwable $t) {
      $this->logException($t);
      return false;
    }
    return true;
  }

  private function registerTypes()
  {
    if ($this->typesBound) {
      return;
    }
    $types = [
      Types\EnumMemberStatus::class => 'enum',
      Types\UuidType::class => 'binary',
    ];

    $connection = $this->entityManager->getConnection();
    try {
      $platform = $connection->getDatabasePlatform();
      foreach ($types as $phpType => $sqlType) {
        if ($sqlType == 'enum') {
          $typeName = substr(strrchr($phpType, '\\'), 1);
          Types\EnumType::registerEnumType($typeName, $phpType);

          // variant in lower case
          $blah = strtolower($typeName);
          Types\EnumType::registerEnumType($blah, $phpType);
          $platform->registerDoctrineTypeMapping($sqlType, $blah);

        } else {
          $instance = new $phpType;
          $typeName = $instance->getName();
          Type::addType($typeName, $phpType);
        }
        if (!empty($sqlType)) {
          $platform->registerDoctrineTypeMapping($sqlType, $typeName);
        }
      }

      // Override datetime stuff
      Type::overrideType('datetime', Carbon\Doctrine\DateTimeType::class);
      Type::overrideType('datetime_immutable', Carbon\Doctrine\DateTimeImmutableType::class);
      Type::overrideType('datetimetz', Carbon\Doctrine\DateTimeType::class);
      Type::overrideType('datetimetz_immutable', Carbon\Doctrine\DateTimeImmutableType::class);
      $this->typesBound = true;
    } catch (\Throwable $t) {
      $this->logException($t);
    }
  }

  private function getEntityManager($params = [])
  {
    list($config, $eventManager) = $this->createConfiguration();

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

  public function postConnect(ConnectionEventArgs $args)
  {
    if (!empty($this->userId)) {
      $args->getConnection()->executeStatement("SET @CLOUD_USER_ID = '" . $this->userId . "'");
    }
  }
}
