<script>
/**
 * @copyright Copyright (c) 2023 Claus-Justus Heine <himself@claus-justus-heine.de>
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
  <AppContent :class="{ 'icon-loading': loading }">
    <Content v-if="!activeProject">
      <h2>
        {{ t(appId, 'The project registration for all projects is closed.') }}
      </h2>
    </Content>
    <Content v-else
             :app-name="appId"
    >
      <div v-if="!loading" class="page-container">
        <h2 v-if="!!registrationData.personalPublicName">
          {{ t(appId, 'Personal Profile of {publicName}', { publicName: registrationData.personalPublicName || '' }) }}
        </h2>
        <h2 v-else>
          {{ t(appId, 'Personal Profile') }}
        </h2>
        <div class="input-row">
          <InputText v-model="registrationData.firstName"
                     :label="t(appId, 'First Name')"
                     :placeholder="t(appId, 'e.g. Jonathan')"
                     :readonly="readonly"
                     :required="true"
                     @input="updatePublicName"
          />
          <InputText v-model="registrationData.surName"
                     :label="t(appId, 'Sur Name')"
                     :placeholder="t(appId, 'e.g. Smith')"
                     :readonly="readonly"
                     :required="true"
                     @input="updatePublicName"
          />
        </div>
        <div v-show="registrationData.nickName" class="input-row">
          <InputText v-model="registrationData.nickName"
                     :label="t(appId, 'Nick Name (optional)')"
                     :placeholder="t(appId, 'e.g. Jonny')"
                     :readonly="readonly"
                     @input="updatePublicName"
          />
        </div>
        <div v-show="registrationData.addressSupplement" class="input-row">
          <InputText v-model="registrationData.addressSupplement"
                     :label="t(appId, 'Address Supplement')"
                     :placeholder="t(appId, 'e.g. c/o Doe')"
                     :readonly="readonly"
          />
        </div>
        <div class="input-row">
          <InputText v-model="registrationData.street"
                     :label="t(appId, 'Street')"
                     :placeholder="t(appId, 'e.g. Underhill')"
                     :readonly="readonly"
          />
          <InputText v-model="registrationData.streetNumber"
                     type="number"
                     :label="t(appId, 'Number')"
                     :placeholder="t(appId, 'e.g. 13')"
                     :readonly="readonly"
          />
        </div>
        <div class="input-row">
          <InputText v-model="registrationData.postalCode"
                     type="text"
                     :label="t(appId, 'Postal Code')"
                     :placeholder="t(appId, 'e.g. 4711')"
                     :readonly="readonly"
          />
          <InputText v-model="registrationData.city"
                     :label="t(appId, 'City')"
                     :placeholder="t(appId, 'e.g. Bagend')"
                     :readonly="readonly"
          />
        </div>
        <div class="input-row">
          <InputText v-model="registrationCountry"
                     type="multiselect"
                     class="country"
                     :label="t(appId, 'Country')"
                     :placeholder="t(appId, 'e.g. The Shire')"
                     :readonly="readonly"
                     :options="countries"
                     track-by="code"
                     option-label="name"
                     :multiple="false"
                     @change="info(...arguments)"
          />
          <InputText v-model="registrationData.birthday"
                     type="date"
                     class="birthday"
                     :label="t(appId, 'Birthday')"
                     :placeholder="t(appId, 'e.g. 01.01.1970')"
                     :readonly="readonly"
                     :required="true"
          />
        </div>
        <div class="input-row">
          <InputText v-model="registrationData.email"
                     :label="t(appId, 'Email')"
                     :placeholder="t(appId, 'e.g. me@you.tld')"
                     :readonly="readonly"
                     :required="true"
                     icon="email"
          />
        </div>
        <div v-if="registrationData.emailAddresses.length > 1"
             class="input-row"
        >
          <InputText v-model="registrationData.emailAddresses"
                     type="multiselect"
                     :label="t(appId, 'All Email Addresses')"
                     :options="registrationData.emailAddresses"
                     track-by="address"
                     option-label="address"
                     :readonly="readonly"
                     :multiple="true"
          />
        </div>
        <div class="input-row">
          <InputText v-model="registrationData.mobilePhone"
                     :label="t(appId, 'Mobile Phone')"
                     :placeholder="t(appId, 'e.g. +12 34 5678 901234')"
                     :readonly="readonly"
          />
          <InputText v-model="registrationData.fixedLinePhone"
                     :label="t(appId, 'Fixed Line Phone')"
                     :placeholder="t(appId, 'e.g. +12 34 5678 901234')"
                     :readonly="readonly"
          />
        </div>
        <div class="input-row">
          <InputText v-model="registrationData.selectedInstruments"
                     type="multiselect"
                     :label="t(appId, 'My Instruments')"
                     :options="instruments"
                     group-values="instruments"
                     group-label="family"
                     track-by="id"
                     option-label="name"
                     :auto-limit="true"
                     :tag-width="100"
                     :readonly="readonly"
                     :multiple="true"
                     :placeholder="t(appId, 'e.g. double bass')"
                     :required="true"
          />
        </div>
        <div class="input-row">
          <CheckboxRadioSwitch :checked.sync="registrationData.firstTimeApplication"
                               type="radio"
                               value="first-time"
                               :required="true"
          >
            {{ t(appId, 'First time application') }}
          </CheckboxRadioSwitch>
          <CheckboxRadioSwitch :checked.sync="registrationData.firstTimeApplication"
                               type="radio"
                               value="you-know-me"
                               :required="true"
          >
            {{ t(appId, 'You know me') }}
          </CheckboxRadioSwitch>
          <RichContenteditable v-if="registrationData.firstTimeApplication === 'first-time'"
                               :value.sync="registrationData.whoAmI"
                               :maxlength="1024"
                               :auto-complete="autoComplete"
                               :placeholder="t(appId, 'Please introduce yourself!')"
                               :tooltip="t(appId, 'As this is the first time that you try to apply to one of our projects we would like to kindly ask you to introduce yourself shortly (instruments, musicial skills, orchestras you already have played with).')"
                               :multiline="true"
                               :required="registrationData.firstTimeApplication === 'first-time'"
          />
        </div>
        <DebugInfo :debug-data="registrationData" />
      </div>
    </Content>
  </AppContent>
</template>

<script>
import { appName } from './config.js'
import InputText from './components/InputText'
import DebugInfo from './components/DebugInfo'

import { set as vueSet } from 'vue'
import AppContent from '@nextcloud/vue/dist/Components/AppContent'
import Content from '@nextcloud/vue/dist/Components/Content'
import CheckboxRadioSwitch from '@nextcloud/vue/dist/Components/CheckboxRadioSwitch'
import RichContenteditable from '@nextcloud/vue/dist/Components/RichContenteditable'
import { getCurrentUser } from '@nextcloud/auth'
import { loadState } from '@nextcloud/initial-state'

import { useMemberDataStore } from './stores/memberData.js'

const viewName = 'PersonalProfile'

const projects = loadState(appName, 'projects')
const activeProject = loadState(appName, 'activeProject')
const instruments = loadState(appName, 'instruments')
const countries = loadState(appName, 'countries')
const displayLocale = loadState(appName, 'displayLocale')

export default {
  name: 'ProjectRegistration',
  components: {
    AppContent,
    CheckboxRadioSwitch,
    Content,
    DebugInfo,
    InputText,
    RichContenteditable,
  },
  setup() {
    const registrationData = useMemberDataStore()
    return { registrationData }
  },
  data() {
    return {
      loading: true,
      readonly: true,
      activeProject: activeProject >= 0 ? projects[activeProject] : null,
      projects,
      instruments,
      countries,
      registrationCountry: null,
    }
  },
  async created() {
    if (getCurrentUser()) {
      console.info('CURRENT USER', getCurrentUser())
      await this.registrationData.initialize()

      if (this.registrationData.initialized.loaded && !this.registrationData.initialized[viewName]) {
        vueSet(this.registrationData, 'birthday', new Date(this.registrationData.birthday))
        vueSet(this.registrationData, 'selectedInstruments', [])
        for (const instrument of this.registrationData.instruments) {
          this.registrationData.selectedInstruments.push(instrument);
        }
        this.registrationData.initialized[viewName] = true;
      }
    }

    // convert the flat array of instruments to grouped options vor Vue Multiselect
    const groupedInstruments = {};
    for (const instrument of this.instruments) {
      const familyTag = instrument.families.map(family => family.family).join(', ')
      const optionGroup = groupedInstruments[familyTag] || { family: familyTag, sortOrder: 0, instruments: [] }
      optionGroup.instruments.push(instrument)
      optionGroup.sortOrder += instrument.sortOrder
      groupedInstruments[familyTag] = optionGroup
    }
    this.instruments.splice(0, this.instruments.length, ...Object.values(groupedInstruments).sort((a, b) => a.sortOrder - b.sortOrder))
    // console.info('GROUPED INSTRUMENTS', this.instruments)

    console.info('COUNTRIES', this.countries)
    if (!this.registrationData.country) {
      vueSet(this.registrationData, 'country', displayLocale.region)
    }
    this.registrationCountry = this.countries.find(country => country.code === this.registrationData.country)

    vueSet(this.registrationData, 'firstTimeApplication', 'first-time')
    vueSet(this.registrationData, 'whoAmI', '')

    this.readonly = false
    this.loading = false
  },
  watch: {
    registrationCountry(newValue, oldValue) {
      vueSet(this.registrationData, 'country', newValue.code)
    },
  },
  methods: {
    info() {
      console.info('INFO', arguments)
    },
    updatePublicName() {
      this.registrationData.personalPublicName = (this.registrationData.nickName || this.registrationData.firstName || '') + ' ' + (this.registrationData.surName || '')
    },
    autoComplete(search, callback) {
      callback(null)
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

#app-content-vue {
  overflow:auto;
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
