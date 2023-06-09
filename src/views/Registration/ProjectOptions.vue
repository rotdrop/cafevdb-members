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
  <Content v-if="activeProject" :app-name="appId" class="project-options-view">
    <div v-if="!loading" class="page-container">
      <h2>
        {{ t(appId, 'Project Fees and Options') }}
      </h2>
      <div class="navigation flex flex-row flex-justify-full">
        <RouterButton :to="{ name: 'registrationParticipation', params: { projectName } }"
                      exact
                      icon="icon-history"
                      icon-position="left"
        >
          {{ t(appId, 'back') }}
        </RouterButton>
        <RouterButton :to="{ name: 'registrationSubmission', params: { projectName } }"
                      exact
                      icon="icon-confirm"
                      icon-position="right"
        >
          {{ t(appId, 'Summary and Submission') }}
        </RouterButton>
      </div>
    </div>
  </Content>
</template>

<script>
import { appName } from '../../config.js'
import InputText from '../../components/InputText'
import DebugInfo from '../../components/DebugInfo'
import RouterButton from '../../components/RouterButton'

import { set as vueSet } from 'vue'
import AppContent from '@nextcloud/vue/dist/Components/AppContent'
import Content from '@nextcloud/vue/dist/Components/Content'
import CheckboxRadioSwitch from '@nextcloud/vue/dist/Components/CheckboxRadioSwitch'

import mixinRegistrationData from '../../mixins/registationData.js'
import { useMemberDataStore } from '../../stores/memberData.js'

export default {
  name: 'ProjectOptions',
  components: {
    AppContent,
    CheckboxRadioSwitch,
    Content,
    DebugInfo,
    InputText,
    RouterButton,
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

.navigation {
  margin-top:0.5em;
}

.flex {
  display: flex;
  &.flex-row {
    flex-direction: row;
  }
  &.flex-justify-full {
    justify-content: space-between;
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
