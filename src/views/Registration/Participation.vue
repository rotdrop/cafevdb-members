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
  <Content v-if="activeProject" :app-name="appId" class="participation-view">
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

import mixinRegistrationData from '../../mixins/registationData.js'
import { useMemberDataStore } from '../../stores/memberData.js'

export default {
  name: 'Participation',
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
  mixins: [
    mixinRegistrationData,
  ],
  data() {
    return {
      loading: true,
      readonly: true,
    }
  },
  async created() {
    await this.initializeRegistrationData()
    this.readonly = false
    this.loading = false
  },
  computed: {},
  watch: {},
  methods: {},
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
