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
  <Content v-if="activeProject" :app-name="appId">
    <div v-if="!loading" class="page-container">
      <h2>
        {{ t(appId, 'Instrumentation, Rehearsals and Concerts') }}
      </h2>
      <h3>
        {{ t(appId, 'Please configure the instrument or the role you intend to play in this project. Please also inform us about rehearsals or even concerts that you cannot participate in.') }}
      </h3>
      <div class="input-row">
        <InputText v-model="registrationData.project[activeProject.id].instruments"
                   type="multiselect"
                   :label="t(appId, 'Project Instruments or Roles')"
                   :options="personalProjectInstrumentOptions"
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
      <div v-if="personalProjectInstrumentOptions.length === 0">
        {{ t(appId, 'You do not seem to play any instrument configured for the project: {instruments}.', { instruments: projectInstrumentsText }) }}
      </div>
    </div>
  </Content>
</template>

<script>
import { appName } from '../../config.js'
import InputText from '../../components/InputText'
import DebugInfo from '../../components/DebugInfo'

import { set as vueSet } from 'vue'
import AppContent from '@nextcloud/vue/dist/Components/AppContent'
import Content from '@nextcloud/vue/dist/Components/Content'
import CheckboxRadioSwitch from '@nextcloud/vue/dist/Components/CheckboxRadioSwitch'
import RichContenteditable from '@nextcloud/vue/dist/Components/RichContenteditable'
import { getCurrentUser } from '@nextcloud/auth'
import { loadState } from '@nextcloud/initial-state'

import { useMemberDataStore } from '../../stores/memberData.js'

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
    vueSet(this.registrationData, 'whoAmI', '')
    if (getCurrentUser()) {
      await this.registrationData.initialize()

      if (this.registrationData.initialized.loaded && !this.registrationData.initialized[viewName]) {
        vueSet(this.registrationData, 'birthday', new Date(this.registrationData.birthday))
        vueSet(this.registrationData, 'selectedInstruments', [])
        for (const instrument of this.registrationData.instruments) {
          this.registrationData.selectedInstruments.push(instrument);
        }
        this.registrationData.initialized[viewName] = true;
      }
      vueSet(this.registrationData, 'firstTimeApplication', 'you-know-me')
    } else {
    vueSet(this.registrationData, 'firstTimeApplication', 'first-time')
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

    // console.info('COUNTRIES', this.countries)
    if (!this.registrationData.country) {
      vueSet(this.registrationData, 'country', displayLocale.region)
    }
    this.registrationCountry = this.countries.find(country => country.code === this.registrationData.country)

    if (!this.registrationData.project) {
      vueSet(this.registrationData, 'project', {})
    }
    if (!this.registrationData.project[this.activeProject.id]) {
      vueSet(this.registrationData.project, this.activeProject.id, {
        instruments: [],
      })
    }

    this.readonly = false
    this.loading = false
  },
  computed: {
    personalProjectInstrumentOptions() {
      if (!this.activeProject) {
        return []
      }
      const possibleInstruments = this.activeProject.instrumentation.filter(
        instrumentationNumber => instrumentationNumber.voice === 0 && this.registrationData.selectedInstruments.find(instrument => instrument.id === instrumentationNumber.instrument.id)
      )
      return possibleInstruments.map(instrumentationNumber => instrumentationNumber.instrument)
    },
    projectInstruments() {
      if (!this.activeProject) {
        return []
      }
      const possibleInstruments = this.activeProject.instrumentation.filter(
        instrumentationNumber => instrumentationNumber.voice === 0
      )
      return possibleInstruments.map(instrumentationNumber => instrumentationNumber.instrument)
    },
    projectInstrumentsText() {
      if (!this.activeProject) {
        return ''
      }
      return this.projectInstruments.map(instrument => instrument.name).join(', ')
    }
  },
  watch: {
    registrationCountry(newValue, oldValue) {
      vueSet(this.registrationData, 'country', newValue.code)
    },
    'registrationData.selectedInstruments'(newValue, oldValue) {
      if (!this.activeProject || newValue.length !== 1) {
        return
      }
      if (this.personalProjectInstrumentOptions.length === 1
          && this.personalProjectInstrumentOptions[0].id === newValue[0].id) {
        const projectId = this.activeProject.id
        vueSet(this.registrationData.project[projectId], 'instruments', newValue)
      }
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
