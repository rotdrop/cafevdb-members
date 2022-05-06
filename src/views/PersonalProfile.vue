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
      <h2>{{ t(appName, 'Personal Profile of {publicName}', { publicName: memberData.personalPublicName }) }}</h2>
      <div class="input-row">
        <InputText v-model="memberData.firstName"
                   :label="t(appName, 'First Name')"
                   :placeholder="t(appName, 'e.g. Jonathan')"
                   :readonly="readonly" />
        <InputText v-model="memberData.surName"
                   :label="t(appName, 'Sur Name')"
                   :placeholder="t(appName, 'e.g. Smith')"
                   :readonly="readonly" />
      </div>
      <div class="input-row">
        <InputText v-model="memberData.nickName"
                   :label="t(appName, 'Nick Name')"
                   :placeholder="t(appName, 'e.g. Jonny')"
                   :readonly="readonly" />
      </div>
      <div class="input-row">
        <InputText v-model="memberData.street"
                   :label="t(appName, 'Street')"
                   :placeholder="t(appName, 'e.g. Underhill')"
                   :readonly="readonly" />
        <InputText v-model="memberData.streetNumber"
                   type="number"
                   :label="t(appName, 'Number')"
                   :placeholder="t(appName, 'e.g. 13')"
                   :readonly="readonly" />
      </div>
      <div class="input-row">
        <InputText v-model="memberData.postalCode"
                   type="text"
                   :label="t(appName, 'Postal Code')"
                   :placeholder="t(appName, 'e.g. 4711')"
                   :readonly="readonly" />
        <InputText v-model="memberData.city"
                   :label="t(appName, 'City')"
                   :placeholder="t(appName, 'e.g. Bagend')"
                   :readonly="readonly" />
      </div>
      <div class="input-row">
        <InputText v-model="memberData.country"
                   class="country"
                   :label="t(appName, 'Country')"
                   :placeholder="t(appName, 'e.g. The Shire')"
                   :readonly="readonly" />
        <InputText v-model="memberData.birthday"
                   type="date"
                   class="birthday"
                   :label="t(appName, 'Birthday')"
                   :placeholder="t(appName, 'e.g. 01.01.1970')"
                   :readonly="readonly" />
      </div>
      <div class="input-row">
        <InputText v-model="memberData.email"
                   :label="t(appName, 'Email')"
                   :placeholder="t(appName, 'e.g. me@you.tld')"
                   :readonly="readonly"
                   icon="email" />
      </div>
      <div class="input-row">
        <InputText v-model="memberData.mobilePhone"
                   :label="t(appName, 'Mobile Phone')"
                   :placeholder="t(appName, 'e.g. +12 34 5678 901234')"
                   :readonly="readonly" />
        <InputText v-model="memberData.fixedLinePhone"
                   :label="t(appName, 'Fixed Line Phone')"
                   :placeholder="t(appName, 'e.g. +12 34 5678 901234')"
                   :readonly="readonly" />
      </div>
      <div class="input-row">
        <InputText v-model="memberData.selectedInstruments"
                   type="multiselect"
                   :label="t(appName, 'Instruments')"
                   :options="memberData.instruments"
                   track-by="id"
                   option-label="name"
                   :readonly="readonly"
                   :multiple="true" />
      </div>
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
import InputText from '../components/InputText'

import Vue from 'vue'
import Content from '@nextcloud/vue/dist/Components/Content'
import CheckboxRadioSwitch from '@nextcloud/vue/dist/Components/CheckboxRadioSwitch'
import '@nextcloud/dialogs/styles/toast.scss'
import { generateUrl } from '@nextcloud/router'
import { showError, TOAST_PERMANENT_TIMEOUT } from '@nextcloud/dialogs'
import axios from '@nextcloud/axios'

export default {
  name: 'PersonalProfile',
  components: {
    Content,
    InputText,
    CheckboxRadioSwitch,
  },
  data() {
    return {
      memberData: {
        selectedInstruments: [],
        instruments: [],
      },
      loading: true,
      readonly: true,
      debug: false,
    }
  },
  computed: {
    isRoot() {
      return this.$route.path === '/'
    },
  },
  /**
   *
   */
  async created() {
    try {
      const response = await axios.get(generateUrl('/apps/' + appName + '/member'))
      for (const [key, value] of Object.entries(response.data)) {
        Vue.set(this.memberData, key, value)
      }
      console.info('BIRTHDAY', this.memberData.birthday)
      Vue.set(this.memberData, 'birthday', new Date(this.memberData.birthday.date.split(' ')[0]))
      console.info('BIRTHDAY', this.memberData.birthday)
      Vue.set(this.memberData, 'selectedInstruments', [])
      for (const instrument of this.memberData.instruments) {
        this.memberData.selectedInstruments.push(instrument);
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
    }
    this.loading = false
  },
  methods: {
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

.input-row {
  display:flex;
  flex-wrap:wrap;
  > * {
    flex: 1 0 40%;
    min-width:20em;
    &.input-type-number {
      flex: 1 0 5%;
      min-width:5em;
    }
    &.input-type-date {
      flex: 0 0 234px;
      width:234px;
      min-width:210px;
    }
  }
  ::v-deep .input-effect {
    margin-bottom:0;
  }
}
</style>
