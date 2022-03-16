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

import { appName } from './config.js'
import { generateFilePath } from '@nextcloud/router'
import { getRequestToken } from '@nextcloud/auth'
import { sync } from 'vuex-router-sync'
// import { translate, translatePlural } from '@nextcloud/l10n'
import Vue from 'vue'
import App from './App'
import router from './router'
import store from './store'

// CSP config for webpack dynamic chunk loading
// eslint-disable-next-line
__webpack_nonce__ = btoa(getRequestToken())

// eslint-disable-next-line
__webpack_public_path__ = generateFilePath(appName, '', 'js/')

sync(store, router)

// is really both needed? Prototype and mixin?
Vue.mixin({ data() { return { appName } }, methods: { t, n } })
// Vue.prototype.t = translate
// Vue.prototype.n = translatePlural

export default new Vue({
  el: '#content',
  name: 'BlahBlah',
  router,
  store,
  render: h => h(App),
})
