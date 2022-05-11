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
    <div v-if="loading" class="page-container loading" />
    <div v-else class="page-container">
      <h2>{{ t(appName, 'Instrument Insurances of {publicName}', {publicName: memberData.personalPublicName }) }}</h2>
      <div class="debug-container">
        <CheckboxRadioSwitch :checked.sync="debug">
          {{ t(appName, 'Enable Debug') }}
        </CheckboxRadioSwitch>
        <div v-if="debug" class="debug">
          <div>{{ t(appName, 'DEBUG: all data') }}</div>
          <pre>{{ JSON.stringify(memberData, null, 2) }}</pre>
        </div>
      </div>
    </div>
  </Content>
</template>
<script>

import { appName } from '../config.js'
import Vue from 'vue'
import Content from '@nextcloud/vue/dist/Components/Content'
import ListItem from '@nextcloud/vue/dist/Components/ListItem'
import CheckboxRadioSwitch from '@nextcloud/vue/dist/Components/CheckboxRadioSwitch'
import '@nextcloud/dialogs/styles/toast.scss'
import { generateUrl } from '@nextcloud/router'
import { showError, TOAST_PERMANENT_TIMEOUT } from '@nextcloud/dialogs'
import axios from '@nextcloud/axios'
import moment from '@nextcloud/moment'

export default {
  name: 'InstrumentInsurances',
  components: {
    Content,
    CheckboxRadioSwitch,
    ListItem,
  },
  mixins: [
    {
      methods: {
        moment,
      },
    },
  ],
  data() {
    return {
      memberData: {},
      loading: true,
      debug: false,
    }
  },
  async created() {
    const self = this;
    try {
      const response = await axios.get(generateUrl('/apps/' + appName + '/member'))
      for (const [key, value] of Object.entries(response.data)) {
        Vue.set(this.memberData, key, value)
      }
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
    }    this.loading = false
  },
  methods: {
    formatDate(date, flavour) {
      flavour = flavour || 'medium'
      switch (flavour) {
        case 'short':
        case 'medium':
        case 'long':
          return moment(date).format('L');
      }
      return moment(data).format(flavour);
    },
  },
}
</script>
<style lang="scss" scoped>
.page-container {
  padding-left:0.5rem;
  &.loading {
    width:100%;
  }
}

.debug-container {
  width:100%;
}

::v-deep {
  .list-item {
    padding-right: 0;
    ul .list-item {
      padding-top:2px;
      padding-bottom:2px;
    }
  }

  .line-two__subtitle {
    padding-right:0;
  }

  .line-one--bold {
    &.line-one {
      .line-one__details {
        font-weight:inherit;
      }
    }
    &.line-two {
      font-weight: normal;
    }
  }
}
</style>
