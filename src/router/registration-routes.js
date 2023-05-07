/**
 * @copyright Copyright (c) 2022, 2023 Claus-Justus Heine <himself@claus-justus-heine.de>
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
 */

const routes = [
  {
    path: '/:projectName',
    name: 'home',
    props: true,
  },
  {
    path: '/:projectName/personal-profile',
    component: () => import('../views/Registration/PersonalProfile'),
    name: 'personalProfile',
    props: true,
  },
  {
    path: '/:projectName/participation',
    component: () => import('../views/Registration/Participation'),
    name: 'participation',
    props: true,
  },
  {
    path: '/:projectName/project-options',
    component: () => import('../views/Registration/ProjectOptions'),
    name: 'projectOptions',
    props: true,
  },
]

export default routes
