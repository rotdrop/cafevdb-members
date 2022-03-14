<?php
/**
 * Orchestra member, musician and project management application.
 *
 * CAFEVDB -- Camerata Academica Freiburg e.V. DataBase.
 *
 * @author Claus-Justus Heine
 * @copyright 2020, 2021, 2022 Claus-Justus Heine <himself@claus-justus-heine.de>
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

namespace OCA\CAFeVDBMembers\Database\ORM\Listeners;

use OCA\CAFEVDB\Service\L10N\BiDirectionalL10N;

/**
 * Override the default listener to use a modified event subscriber
 * which also queries other sources of translations if a concrete
 * translations has not been persisted yet.
 */
class GedmoTranslatableListener extends \Gedmo\Translatable\TranslatableListener
{
  /** @var BiDirectionalL10N */
  private $musicL10n;

  public function __construct(BiDirectionalL10N $musicL10n)
  {
    parent::__construct();
    $this->musicL10n = $musicL10n;
  }

  /**
   * {@inheritdoc}
   */
  protected function getFallbackTranslation($originalValue)
  {
    $translatedValue = $this->musicL10n->t($originalValue);
    return ($translatedValue !== $originalValue) ? $translatedValue : null;
  }

  /**
   * {@inheritdoc}
   */
  protected function getFallbackUntranslation($translatedValue)
  {
    $originalValue = $this->musicL10n->backTranslate($translatedValue);
    return ($translatedValue !== $originalValue) ? $originalValue : null;
  }
}
