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
use OCP\IL10N;

use Doctrine\ORM\Tools\Setup;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\DBAL\Event\ConnectionEventArgs;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use Doctrine\ORM\Decorator\EntityManagerDecorator;
use Doctrine\ORM\Mapping\UnderscoreNamingStrategy;
use Doctrine\DBAL\Connection as DatabaseConnection;
use Doctrine\DBAL\Types\Type;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Doctrine\Common\Cache\ArrayCache;
use Doctrine\Common\Cache\Psr6\CacheAdapter;
use Doctrine\Common\Cache\Psr6\DoctrineProvider;
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\PsrCachedReader;

use MyCLabs\Enum\Enum as EnumType;
use MediaMonks\Doctrine\Transformable;

use OCA\CAFeVDBMembers\Database\ORM\Mapping\ReservedWordQuoteStrategy;
use OCA\CAFeVDBMembers\Database\DBAL\Types;
use OCA\CAFeVDBMembers\Database\DBAL\Logging\CloudLogger;
use OCA\CAFeVDBMembers\Service\AuthenticationService;

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

  const TRANSFORM_ENCRYPT = 'encrypt';

  const ROW_ACCESS_TOKEN_KEY = 'rowAccessToken';

  /** @var EntityManagerInterface */
  private $entityManager;

  /** @var string */
  private $appName;

  /** @var AuthenticationService */
  private $authenticationService;

  /** @var IConfig */
  private $cloudConfig;

  /** @var IAppContainer */
  private $appContainer;

  /** @var IL10N */
  private $l;

  /** @var CloudLogger */
  private $sqlLogger;

  /** @var string */
  private $userId;

  /** @var bool */
  private $typesBound = false;

  public function __construct(
    $appName
    , $userId
    , AuthenticationService $authenticationService
    , IAppContainer $appContainer
    , IConfig $cloudConfig
    , IL10N $l10n
    , LoggerInterface $logger
    , CloudLogger $sqlLogger
  ) {
    $this->appName = $appName;
    $this->userId = $userId;
    $this->authenticationService = $authenticationService;
    $this->appContainer = $appContainer;
    $this->cloudConfig = $cloudConfig;
    $this->l = $l10n;
    $this->logger = $logger;
    $this->sqlLogger = $sqlLogger;
    try {
      parent::__construct($this->getEntityManager());
    } catch (\Throwable $t) {
      $this->logException($t);
      throw $t;
    }
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
    $config->setEntityListenerResolver(new class($this->appContainer) extends \Doctrine\ORM\Mapping\DefaultEntityListenerResolver {

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

    $eventManager = new \Doctrine\Common\EventManager();

    $eventManager->addEventListener([
      // \OCA\CAFEVDB\Wrapped\Doctrine\ORM\Tools\ToolEvents::postGenerateSchema,
      // ORM\Events::loadClassMetadata,
      // ORM\Events::preUpdate,
      // ORM\Events::postUpdate,
      \Doctrine\DBAL\Events::postConnect,
      \Doctrine\ORM\Events::postLoad,
    ], $this);

    return [ $config, $eventManager, ];
  }

  private function createGedmoConfiguration($config, $eventManager):array
  {
    // standard annotation reader
    $annotationReader = new AnnotationReader;
    $cache = new ArrayAdapter();
    $cachedAnnotationReader = new PsrCachedReader($annotationReader, $cache);

    // create a driver chain for metadata reading
    $driverChain = new \Doctrine\Persistence\Mapping\Driver\MappingDriverChain();

    // load superclass metadata mapping only, into driver chain
    // also registers Gedmo annotations.NOTE: you can personalize it
    \Gedmo\DoctrineExtensions::registerAbstractMappingIntoDriverChainORM(
      $driverChain, // our metadata driver chain, to hook into
      $cachedAnnotationReader // our cached annotation reader
    );
    //<<< Further annotations can go here
    \MediaMonks\Doctrine\DoctrineExtensions::registerAnnotations();
    // CJH\Setup::registerAnnotations();
    //>>>

    // now we want to register our application entities,
    // for that we need another metadata driver used for Entity namespace
    $annotationDriver = new \Doctrine\ORM\Mapping\Driver\AnnotationDriver(
      $cachedAnnotationReader, // our cached annotation reader
      self::ENTITY_PATHS, // paths to look in
    );

    // NOTE: driver for application Entity can be different, Yaml, Xml or whatever
    // register annotation driver for our application Entity namespace
    $driverChain->addDriver($annotationDriver, 'OCA\CAFeVDBMembers\Database\ORM\Entities');

    // general ORM configuration
    //$config = new \OCA\CAFEVDB\Wrapped\Doctrine\ORM\Configuration;
    $config->setProxyDir(self::PROXY_DIR);
    $config->setProxyNamespace('OCA\CAFeVDBMembers\Database\ORM\Proxies');
    $config->setAutoGenerateProxyClasses(self::DEV_MODE); // this can be based on production config.

    // register metadata driver
    $config->setMetadataDriverImpl($driverChain);

    // use our already initialized cache driver
    $config->setMetadataCache($cache);
    $config->setQueryCacheImpl(DoctrineProvider::wrap($cache));

    // gedmo extension listeners

    // soft deletable
    $softDeletableListener = new \Gedmo\SoftDeleteable\SoftDeleteableListener();
    $softDeletableListener->setAnnotationReader($cachedAnnotationReader);
    $eventManager->addEventSubscriber($softDeletableListener);
    $config->addFilter('soft-deleteable', \Gedmo\SoftDeleteable\Filter\SoftDeleteableFilter::class);

    // encryption
    $transformerPool = new Transformable\Transformer\TransformerPool();
    $transformerPool[self::TRANSFORM_ENCRYPT] = $this->appContainer->get(
      Listeners\Encryption::class
    );
    $this->transformerPool = $transformerPool;
    $transformableListener = new Transformable\TransformableSubscriber($transformerPool);
    $transformableListener->setAnnotationReader($cachedAnnotationReader);
    $eventManager->addEventSubscriber($transformableListener);

    // translatable
    $translatableListener = $this->appContainer->get(Listeners\GedmoTranslatableListener::class);
    // current translation locale should be set from session or hook later into the listener
    // most important, before entity manager is flushed
    $localeCode = $this->l->getLocaleCode();
    if (strpos($localeCode, '_') === false) {
      $localeCode = $localeCode . '_' . strtoupper($localeCode);
    }
    $translatableListener->setTranslatableLocale($localeCode);
    $translatableListener->setDefaultLocale($this->appContainer->get('DefaultLocale'));
    $translatableListener->setTranslationFallback(true);
    $translatableListener->setPersistDefaultLocaleTranslation(true);
    $translatableListener->setAnnotationReader($cachedAnnotationReader);
    $eventManager->addEventSubscriber($translatableListener);

    $config->setDefaultQueryHint(
      \Doctrine\ORM\Query::HINT_CUSTOM_OUTPUT_WALKER,
      \Gedmo\Translatable\Query\TreeWalker\TranslationWalker::class
    );
    $config->setDefaultQueryHint(
      \Gedmo\Translatable\TranslatableListener::HINT_TRANSLATABLE_LOCALE,
      $localeCode
    );
    $config->setDefaultQueryHint(
      \Gedmo\Translatable\TranslatableListener::HINT_FALLBACK,
      1 // fallback to default values in case if record is not translated
    );

    return [ $config, $eventManager, $annotationReader ];
  }

  private function connectionParameters($params = [])
  {
    $connectionParams = [
      'dbname' => $this->cloudConfig->getAppValue($this->appName, 'cloudUserViewsDatabase'),
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
      if (!$connection->isConnected()) {
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
      Types\EnumFileType::class => 'enum',
      Types\EnumGeographicalScope::class => 'enum',
      Types\EnumMemberStatus::class => 'enum',
      Types\EnumParticipantFieldDataType::class => 'enum',
      Types\EnumParticipantFieldMultiplicity::class => 'enum',
      Types\EnumProjectTemporalType::class => 'enum',
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
      Type::overrideType('date', \Carbon\Doctrine\CarbonType::class);
      Type::overrideType('date_immutable', \Carbon\Doctrine\CarbonImmutableType::class);
      Type::overrideType('datetime', \Carbon\Doctrine\DateTimeType::class);
      Type::overrideType('datetime_immutable', \Carbon\Doctrine\DateTimeImmutableType::class);
      Type::overrideType('datetimetz', \Carbon\Doctrine\DateTimeType::class);
      Type::overrideType('datetimetz_immutable', \Carbon\Doctrine\DateTimeImmutableType::class);
      $this->typesBound = true;
    } catch (\Throwable $t) {
      $this->logException($t);
    }
  }

  private function getEntityManager($params = [])
  {
    list($config, $eventManager) = $this->createConfiguration();
    list($config, $eventManager, ) = $this->createGedmoConfiguration($config, $eventManager);

    if (self::DEV_MODE) {
      $config->setAutoGenerateProxyClasses(true);
    } else {
      $config->setAutoGenerateProxyClasses(false);
    }

    $namingStrategy = new UnderscoreNamingStrategy(CASE_LOWER);
    $config->setNamingStrategy($namingStrategy);

    // $quoteStrategy = new ReservedWordQuoteStrategy();
    // $config->setQuoteStrategy($quoteStrategy);

    $config->setSQLLogger($this->sqlLogger);

    // obtaining the entity manager
    $entityManager = \Doctrine\ORM\EntityManager::create($this->connectionParameters($params), $config, $eventManager);

    return $entityManager;
  }

  public function postConnect(ConnectionEventArgs $args)
  {
    if (!empty($this->userId)) {
      $args->getConnection()->executeStatement("SET @CLOUD_USER_ID = '" . $this->userId . "'");
      $rowAccessTokenHash = $this->authenticationService->getRowAccessToken();
      $args->getConnection()->executeStatement("SET @ROW_ACCESS_TOKEN = '" . $rowAccessTokenHash . "'");
    }
  }

  public function postLoad(LifecycleEventArgs $args)
  {
    $entity = $args->getObject();
    if (\method_exists($entity, '__wakeup')) {
      $entity->__wakeup();
    }
  }
}
