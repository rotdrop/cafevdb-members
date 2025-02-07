<?php
/**
 * Member's data base connector for CAFEVDB orchetra management app.
 *
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

namespace OCA\CAFeVDBMembers\Database\ORM\Traits;

use Doctrine\ORM\Mapping as ORM;

trait ArrayTrait
{
  private $keys;

  /**
   * Use reflection inspection to export all of the private keys;
   * automatically called on post-load.
   *
   * @ORM\PostLoad
   */
  protected function arrayCTOR() {
    $this->keys = (new \ReflectionClass(__CLASS__))
                ->getProperties(\ReflectionProperty::IS_PRIVATE|\ReflectionProperty::IS_PROTECTED);

    $this->keys = array_map(function($property) {
      $doc = $property->getDocComment();
      $name = $property->getName();
      if (preg_match('/@ORM\\\\(Column|(Many|One)To(Many|One))/i', $doc)) {
        return $name;
      }
      return false;
    }, $this->keys);

    unset($this->keys['keys']);
    $this->keys = array_filter($this->keys);
  }

  public function __wakeup()
  {
    $this->arrayCTOR();
  }

  public function toArray()
  {
    $result = [];
    foreach ($this->keys as $key) {
      $result[$key] = $this->offsetGet($key);
    }
    return $result;
  }

  public function offsetExists($offset):bool {
    if (empty($this->keys)) {
      $this->arrayCTOR();
    }
    return is_array($this->keys) && in_array(self::offsetNormalize($offset), $this->keys);
  }

  public function offsetGet($offset) {
    if (!$this->offsetExists($offset)) {
      throw new \Exception('Offset '.self::offsetNormalize($offset).' does not exist in '.__CLASS__.', keys '.print_r($this->keys, true));
    }
    $method = self::methodName('get', $offset);
    if (!method_exists($this, $method)) {
      throw new \Exception('Method '.$method.' does not exist in '.__CLASS__.', please implement it.');
    }
    return $this->$method();
  }

  public function offsetSet($offset, $value):void
  {
    if (!$this->offsetExists($offset)) {
      throw new \Exception('Offset '.self::offsetNormalize($offset).' does not exist in '.__CLASS__.', keys '.print_r($this->keys, true));
    }
    $method = self::methodName('set', $offset);
    if (!method_exists($this, $method)) {
      throw new \Exception('Method '.$method.' does not exist in '.__CLASS__.', please implement it.');
    }
    $this->$method($value);
  }

  public function offsetUnset($offset):void
  {
    $this->offsetSet($offset, null);
  }

  private static function methodName($prefix, $offset) {
    return $prefix . ucfirst(self::offsetNormalize($offset));
  }

  private static function offsetNormalize($offset)
  {
    $words = explode('_', $offset);
    if ($words[0] == strtoupper($words[0])) {
      $words[0] = strtolower($words[0]);
    }
    $words = array_map('ucfirst', $words);
    return lcfirst(implode('', $words));
  }
}
