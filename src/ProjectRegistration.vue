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
        <h2 v-if="!!memberData.personalPublicName">
          {{ t(appId, 'Personal Profile of {publicName}', { publicName: memberData.personalPublicName || '' }) }}
        </h2>
        <h2 v-else>
          {{ t(appId, 'Personal Profile') }}
        </h2>
        <div class="input-row">
          <InputText v-model="memberData.firstName"
                     :label="t(appId, 'First Name')"
                     :placeholder="t(appId, 'e.g. Jonathan')"
                     :readonly="readonly"
                     :required="true"
                     @input="updatePublicName"
          />
          <InputText v-model="memberData.surName"
                     :label="t(appId, 'Sur Name')"
                     :placeholder="t(appId, 'e.g. Smith')"
                     :readonly="readonly"
                     :required="true"
                     @input="updatePublicName"
          />
        </div>
        <div v-show="memberData.nickName" class="input-row">
          <InputText v-model="memberData.nickName"
                     :label="t(appId, 'Nick Name (optional)')"
                     :placeholder="t(appId, 'e.g. Jonny')"
                     :readonly="readonly"
                     @input="updatePublicName"
          />
        </div>
        <div v-show="memberData.addressSupplement" class="input-row">
          <InputText v-model="memberData.addressSupplement"
                     :label="t(appId, 'Address Supplement')"
                     :placeholder="t(appId, 'e.g. c/o Doe')"
                     :readonly="readonly"
          />
        </div>
        <div class="input-row">
          <InputText v-model="memberData.street"
                     :label="t(appId, 'Street')"
                     :placeholder="t(appId, 'e.g. Underhill')"
                     :readonly="readonly"
          />
          <InputText v-model="memberData.streetNumber"
                     type="number"
                     :label="t(appId, 'Number')"
                     :placeholder="t(appId, 'e.g. 13')"
                     :readonly="readonly"
          />
        </div>
        <div class="input-row">
          <InputText v-model="memberData.postalCode"
                     type="text"
                     :label="t(appId, 'Postal Code')"
                     :placeholder="t(appId, 'e.g. 4711')"
                     :readonly="readonly"
          />
          <InputText v-model="memberData.city"
                     :label="t(appId, 'City')"
                     :placeholder="t(appId, 'e.g. Bagend')"
                     :readonly="readonly"
          />
        </div>
        <div class="input-row">
          <InputText v-model="memberData.country"
                     class="country"
                     :label="t(appId, 'Country')"
                     :placeholder="t(appId, 'e.g. The Shire')"
                     :readonly="readonly"
          />
          <InputText v-model="memberData.birthday"
                     type="date"
                     class="birthday"
                     :label="t(appId, 'Birthday')"
                     :placeholder="t(appId, 'e.g. 01.01.1970')"
                     :readonly="readonly"
          />
        </div>
        <div class="input-row">
          <InputText v-model="memberData.email"
                     :label="t(appId, 'Email')"
                     :placeholder="t(appId, 'e.g. me@you.tld')"
                     :readonly="readonly"
                     :required="true"
                     icon="email"
          />
        </div>
        <div v-if="memberData.emailAddresses.length > 1"
             class="input-row"
        >
          <InputText v-model="memberData.emailAddresses"
                     type="multiselect"
                     :label="t(appId, 'All Email Addresses')"
                     :options="memberData.emailAddresses"
                     track-by="address"
                     option-label="address"
                     :readonly="readonly"
                     :multiple="true"
          />
        </div>
        <div class="input-row">
          <InputText v-model="memberData.mobilePhone"
                     :label="t(appId, 'Mobile Phone')"
                     :placeholder="t(appId, 'e.g. +12 34 5678 901234')"
                     :readonly="readonly"
          />
          <InputText v-model="memberData.fixedLinePhone"
                     :label="t(appId, 'Fixed Line Phone')"
                     :placeholder="t(appId, 'e.g. +12 34 5678 901234')"
                     :readonly="readonly"
          />
        </div>
        <div class="input-row">
          <InputText v-model="memberData.selectedInstruments"
                     type="multiselect"
                     :label="t(appId, 'Instruments')"
                     :options="instruments"
                     group-values="instruments"
                     group-label="family"
                     track-by="id"
                     option-label="name"
                     :readonly="readonly"
                     :multiple="true"
                     :placeholder="t(appId, 'e.g. double bass')"
                     :required="true"
          />
        </div>
        <DebugInfo :debug-data="memberData" />
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
import { getCurrentUser } from '@nextcloud/auth'
import { loadState } from '@nextcloud/initial-state'

import { useMemberDataStore } from './stores/memberData.js'

const viewName = 'PersonalProfile'

const projects = loadState(appName, 'projects');
const activeProject = loadState(appName, 'activeProject');
const instruments = loadState(appName, 'instruments');

console.info('PROJECTS', activeProject, projects, instruments);

export default {
  name: 'ProjectRegistration',
  components: {
    AppContent,
    Content,
    InputText,
    DebugInfo,
  },
  setup() {
    const memberData = useMemberDataStore()
    return { memberData }
  },
  data() {
    return {
      loading: true,
      readonly: true,
      activeProject: activeProject >= 0 ? projects[activeProject] : null,
      projects,
      instruments,
    }
  },
  async created() {
    if (getCurrentUser()) {
      console.info('CURRENT USER', getCurrentUser())
      await this.memberData.initialize()

      if (this.memberData.initialized.loaded && !this.memberData.initialized[viewName]) {
        vueSet(this.memberData, 'birthday', new Date(this.memberData.birthday))
        vueSet(this.memberData, 'selectedInstruments', [])
        for (const instrument of this.memberData.instruments) {
          this.memberData.selectedInstruments.push(instrument);
        }
        this.memberData.initialized[viewName] = true;
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
    console.info('GROUPED INSTRUMENTS', this.instruments)
    this.readonly = false
    this.loading = false
  },
  methods: {
    updatePublicName() {
      this.memberData.personalPublicName = (this.memberData.nickName || this.memberData.firstName || '') + ' ' + (this.memberData.surName || '')
    }
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
