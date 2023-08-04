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

const prefix = '/registration'

const routes = [
  {
    path: prefix + '/:projectName?',
    name: 'registrationHome',
    props: true,
  },
  {
    path: prefix + '/:projectName?/personal-profile',
    component: () => import('../views/Registration/PersonalProfile.vue'),
    name: 'registrationPersonalProfile',
    props: true,
  },
  {
    path: prefix + '/:projectName?/participation',
    component: () => import('../views/Registration/Participation.vue'),
    name: 'registrationParticipation',
    props: true,
  },
  {
    path: prefix + '/:projectName?/project-options',
    component: () => import('../views/Registration/ProjectOptions.vue'),
    name: 'registrationProjectOptions',
    props: true,
  },
  {
    path: prefix + '/:projectName?/submission',
    component: () => import('../views/Registration/Submission.vue'),
    name: 'registrationSubmission',
    props: true,
  },
]

export {
  prefix,
}

export default routes
