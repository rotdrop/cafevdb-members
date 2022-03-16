<script>
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
</script>
<template>
  <Content :class="'app-' + appName" :app-name="'app-' + appName">
    <div>
      <h2>{{ t(appName, 'To be implemented: {view}', { view: t(appName, 'Personal Profile'), }) }}</h2>
      <div>{{ t(appName, 'This is currently only a placeholder for the future plan to make the personal data of the orchestra members available to just the respective orchestra member.') }}</div>
      <pre>{{ JSON.stringify(memberData, null, 2) }}</pre>
    </div>
  </Content>
</template>
<script>
import { appName } from '../config.js'

import Content from '@nextcloud/vue/dist/Components/Content'

import '@nextcloud/dialogs/styles/toast.scss'
import { generateUrl } from '@nextcloud/router'
import { showError, TOAST_PERMANENT_TIMEOUT } from '@nextcloud/dialogs'
import axios from '@nextcloud/axios'

export default {
  name: 'PersonalProfile',
  components: {
    Content,
  },
  data() {
    return {
      memberData: {},
      loading: true,
    }
  },
  computed: {
    isRoot() {
      return this.$route.path === '/'
    },
  },
  /**
   * Fetch list of notes when the component is loaded
   */
  async mounted() {
    console.info('MOUNTED')
    try {
      const response = await axios.get(generateUrl('/apps/' + appName + '/member'))
      this.memberData = response.data
    } catch (e) {
      console.error('ERROR', e)
      let message = t(appName, 'reason unknown')
      if (e.response && e.response.data && e.response.data.message) {
        message = e.response.data.message
        console.info('RESPONSE', e.response)
      }
      // Ignore for the time being
      if (this === false) {
        showError(t(appName, 'Could not fetch musician(s): {message}', { message }), { timeout: TOAST_PERMANENT_TIMEOUT })
      }
    }
    this.loading = false
  },
  methods: {
  },
}
</script>
