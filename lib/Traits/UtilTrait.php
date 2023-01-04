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

use OCP\IL10N;

/** Some static helper methods. */
trait UtilTrait
{
  /** @var IL10N */
  private $l;

  /**
   * Take any dashed or "underscored" lower-case string and convert to
   * camel-case.
   *
   * @param null|string $string the string to convert.
   *
   * @param bool $capitalizeFirstCharacter self explaining.
   *
   * @param string $dashes Characters to replace.
   *
   * @return string
   */
  protected static function dashesToCamelCase(?string $string, bool $capitalizeFirstCharacter = false, string $dashes = '_-'):string
  {
    $str = str_replace(str_split($dashes), '', ucwords($string, $dashes));

    if (!$capitalizeFirstCharacter) {
      $str[0] = strtolower($str[0]);
    }

    return $str;
  }

  /**
   * Take an camel-case string and convert to lower-case with dashes
   * or underscores between the words. First letter may or may not
   * be upper case.
   *
   * @param null|string $string String to work on.
   *
   * @param string $separator Separator to use, defaults to '-'.
   *
   * @return string
   */
  protected static function camelCaseToDashes(?string $string, string $separator = '-'):string
  {
    return strtolower(preg_replace('/([A-Z])/', $separator.'$1', lcfirst($string)));
  }

  /**
   * Return the locale as string, e.g. de_DE.UTF-8.
   *
   * @return string
   */
  protected function getLocale():string
  {
    $locale = $this->l->getLocaleCode();
    $primary = locale_get_primary_language($locale);
    if ($primary == $locale) {
      $locale = $locale.'_'.strtoupper($locale);
    }
    if (strpos($locale, '.') === false) {
      $locale .= '.UTF-8';
    }
    return $locale;
  }

  /**
   * Transliterate the given string to the given or default locale.
   *
   * @param string $string
   *
   * @param null|string $locale
   *
   * @return string
   *
   * @todo We should define a user-independent locale based on the
   * location of the orchestra.
   */
  protected function transliterate(string $string, ?string $locale = null):string
  {
    $oldlocale = setlocale(LC_CTYPE, '0');
    empty($locale) && $locale = $this->getLocale();
    setlocale(LC_CTYPE, $locale);
    $result = iconv('UTF-8', 'ASCII//TRANSLIT', $string);
    setlocale(LC_CTYPE, $oldlocale);
    return $result;
  }
}
