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

namespace OCA\CAFeVDBMembers\Database\ORM\Entities;

use OCA\CAFeVDBMembers\Database\ORM as CAFEVDB;
use Doctrine\ORM\Mapping as ORM;

/**
 * ProjectWebPage
 *
 * @ORM\Table(name="PersonalizedProjectWebPagesView")
 * @ORM\Entity
 */
class ProjectWebPage implements \ArrayAccess
{
  use CAFEVDB\Traits\ArrayTrait;

  /**
   * @ORM\ManyToOne(targetEntity="Project", inversedBy="webPages", fetch="EXTRA_LAZY"))
   * @ORM\Id
   */
  private $project;

  /**
   * @var int
   * @ORM\Column(type="integer", nullable=false, options={"default"="-1"})
   * @ORM\Id
   */
  private $articleId = '-1';

  /**
   * @var string
   *
   * @ORM\Column(type="string", length=128, nullable=false, options={"default"=""})
   */
  private $articleName = '';

  /**
   * @var int
   *
   * @ORM\Column(type="integer", nullable=false, options={"default"="-1"})
   */
  private $categoryId = '-1';

  /**
   * @var int
   *
   * @ORM\Column(type="integer", nullable=false, options={"default"="-1"})
   */
  private $priority = '-1';

  // phpcs:disable Squiz.Commenting.FunctionComment.Missing
  public function __construct()
  {
    $this->arrayCTOR();
  }
  // phpcs:enable

  /**
   * Set project.
   *
   * @param null|Project $project
   *
   * @return ProjectWebPage
   */
  public function setProject($project):ProjectWebPage
  {
    $this->project = $project;

    return $this;
  }

  /**
   * Get project.
   *
   * @return Project
   */
  public function getProject():Project
  {
    return $this->project;
  }

  /**
   * Set articleId.
   *
   * @param int $articleId
   *
   * @return ProjectWebPage
   */
  public function setArticleId(int $articleId):ProjectWebPage
  {
    $this->articleId = $articleId;

    return $this;
  }

  /**
   * Get articleId.
   *
   * @return int
   */
  public function getArticleId():int
  {
    return $this->articleId;
  }

  /**
   * Set articleName.
   *
   * @param string $articleName
   *
   * @return ProjectWebPage
   */
  public function setArticleName(string $articleName):ProjectWebPage
  {
    $this->articleName = $articleName;

    return $this;
  }

  /**
   * Get articleName.
   *
   * @return string
   */
  public function getArticleName():string
  {
    return $this->articleName;
  }

  /**
   * Set categoryId.
   *
   * @param null|int $categoryId
   *
   * @return ProjectWebPage
   */
  public function setCategoryId($categoryId):ProjectWebPage
  {
    $this->categoryId = $categoryId;

    return $this;
  }

  /**
   * Get categoryId.
   *
   * @return int
   */
  public function getCategoryId():int
  {
    return $this->categoryId;
  }

  /**
   * Set priority.
   *
   * @param null|int $priority
   *
   * @return ProjectWebPages
   */
  public function setPriority($priority):ProjectWebPage
  {
    $this->priority = $priority;

    return $this;
  }

  /**
   * Get priority.
   *
   * @return int
   */
  public function getPriority():int
  {
    return $this->priority;
  }
}
