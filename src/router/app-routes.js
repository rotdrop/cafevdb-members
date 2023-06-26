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

import { appName } from '../config.js'

const routes = [
  {
    path: '/',
    props: router => ({
      rootTitle: t(appName, 'Home'),
    }),
  },
  {
    path: '/f/personalProfile',
    component: () => import('../views/PersonalProfile.vue'),
    name: 'personalProfile',
    props: router => ({
      rootTitle: t(appName, 'Personal Profile'),
    }),
  },
  {
    path: '/f/bankAccounts',
    component: () => import('../views/BankAccounts.vue'),
    name: 'bankAccounts',
    props: router => ({
      rootTitle: t(appName, 'Bank Accounts'),
    }),
  },
  {
    path: '/f/instrumentInsurances',
    component: () => import('../views/InstrumentInsurances.vue'),
    name: 'instrumentInsurances',
    props: router => ({
      rootTitle: t(appName, 'Instrument Insurances'),
    }),
  },
  {
    path: '/f/projects',
    component: () => import('../views/Projects.vue'),
    name: 'projects',
    props: router => ({
      rootTitle: t(appName, 'Projects'),
    }),
  },
]

export default routes
