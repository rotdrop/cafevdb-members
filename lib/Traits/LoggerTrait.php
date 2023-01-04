<?php
/**
 * Member's data base connector for CAFEVDB orchetra management app.
 *
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

namespace OCA\CAFeVDBMembers\Traits;

use Throwable;

use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;

use OCP\ILogger;

/** Simplifying logging to the cloud log-file. */
trait LoggerTrait
{
  /** @var LoggerInterface */
  protected $logger;

  /**
   * Return the stored logger class.
   *
   * @return LoggerInterface
   */
  public function logger():LoggerInterface
  {
    return $this->logger;
  }

  /**
   * Map PSR log-levels to ILogger log-levels as the PsrLoggerAdapter only
   * understands those.
   *
   * @param mixed $level
   *
   * @return mixed
   */
  protected function mapLogLevels(mixed $level):mixed
  {
    if (is_int($level) || is_numeric($level)) {
      return $level;
    }
    switch ($level) {
      case LogLevel::EMERGENCY:
        return ILogger::FATAL;
      case LogLevel::ALERT:
        return ILogger::ERROR;
      case LogLevel::CRITICAL:
        return ILogger::ERROR;
      case LogLevel::ERROR:
        return ILogger::ERROR;
      case LogLevel::WARNING:
        return ILogger::WARN;
      case LogLevel::NOTICE:
        return ILogger::INFO;
      case LogLevel::INFO:
        return ILogger::INFO;
      case LogLevel::DEBUG:
        return ILogger::DEBUG;
      default:
        return ILogger::ERROR;
    }
  }

  /**
   * @param mixed $level
   *
   * @param string $message
   *
   * @param array $context
   *
   * @param int $shift
   *
   * @param bool $showTrace
   *
   * @return void
   */
  public function log(mixed $level, string $message, array $context = [], int $shift = 0, bool $showTrace = false):void
  {
    $level = $this->mapLogLevels($level);
    $trace = debug_backtrace();
    $prefix = '';
    $shift = min($shift, count($trace));

    do {
      $caller = $trace[$shift];
      $file = $caller['file']??'unknown';
      $line = $caller['line']??'unknown';
      $caller = $trace[$shift+1]??'unknown';
      $class = $caller['class']??'unknown';
      $method = $caller['function'];

      $prefix .= $file.':'.$line.': '.$class.'::'.$method.'(): ';
    } while ($showTrace && --$shift > 0);
    $this->logger->log($level, $prefix.$message, $context);
  }

    /**
   * @param Throwable $exception
   *
   * @param null|string $message
   *
   * @param int $shift
   *
   * @param bool $showTrace
   *
   * @return void
   */
  public function logException(Throwable $exception, ?string $message = null, int $shift = 0, bool $showTrace = false):void
  {
    $trace = debug_backtrace();
    $caller = $trace[$shift];
    $file = $caller['file']??'unknown';
    $line = $caller['line']??0;
    $caller = $trace[$shift+1];
    $class = $caller['class'];
    $method = $caller['function'];

    $prefix = $file.':'.$line.': '.$class.'::'.$method.': ';

    empty($message) && ($message = "Caught an Exception");
    $this->logger->error($prefix . $message, [ 'exception' => $exception ]);
  }

  /**
   * @param string $message
   *
   * @param array $context
   *
   * @param int $shift
   *
   * @param bool $showTrace
   *
   * @return void
   */
  public function logError(string $message, array $context = [], int $shift = 1, bool $showTrace = false):void
  {
    $this->log(LogLevel::ERROR, $message, $context, $shift, $showTrace);
  }

  /**
   * @param string $message
   *
   * @param array $context
   *
   * @param int $shift
   *
   * @param bool $showTrace
   *
   * @return void
   */
  public function logDebug(string $message, array $context = [], int $shift = 1, bool $showTrace = false):void
  {
    $this->log(LogLevel::DEBUG, $message, $context, $shift, $showTrace);
  }

  /**
   * @param string $message
   *
   * @param array $context
   *
   * @param int $shift
   *
   * @param bool $showTrace
   *
   * @return void
   */
  public function logInfo(string $message, array $context = [], int $shift = 1, bool $showTrace = false):void
  {
    $this->log(LogLevel::INFO, $message, $context, $shift, $showTrace);
  }

  /**
   * @param string $message
   *
   * @param array $context
   *
   * @param int $shift
   *
   * @param bool $showTrace
   *
   * @return void
   */
  public function logWarn(string $message, array $context = [], int $shift = 1, bool $showTrace = false):void
  {
    $this->log(LogLevel::WARNING, $message, $context, $shift, $showTrace);
  }

  /**
   * @param string $message
   *
   * @param array $context
   *
   * @param int $shift
   *
   * @param bool $showTrace
   *
   * @return void
   */
  public function logFatal(string $message, array $context = [], int $shift = 1, bool $showTrace = false):void
  {
    $this->log(LogLevel::EMERGENCY, $message, $context, $shift, $showTrace);
  }
}
