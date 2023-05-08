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
  <Content :class="{ 'icon-loading': loading, 'root-view': true }" :app-name="appId">
    <AppNavigation>
      <template #list>
        <AppNavigationItem
          :to="{ name: 'home', params: { projectName } }"
          :title="t(appId, 'Home')"
          icon="icon-home"
          exact
        />
        <AppNavigationItem
          :to="{ name: 'personalProfile', params: { projectName } }"
          :title="t(appId, 'Personal Profile')"
          icon="icon-files-dark"
          :class="{ disabled: !activeProject }"
          exact
        />
        <AppNavigationItem
          :to="{ name: 'participation', params: { projectName } }"
          :title="t(appId, 'Instrumentation and Events')"
          icon="icon-files-dark"
          :class="{ disabled: !activeProject }"
          exact
        />
        <AppNavigationItem
          :to="{ name: 'projectOptions', params: { projectName } }"
          :title="t(appId, 'Options')"
          icon="icon-files-dark"
          :class="{ disabled: !activeProject }"
          exact
        />
      </template>
      <template #footer>
        <AppNavigationSettings>
          <CheckboxRadioSwitch :checked.sync="debug">
            {{ t(appId, 'Enable Debug') }}
          </CheckboxRadioSwitch>
        </AppNavigationSettings>
      </template>
    </AppNavigation>
    <AppContent :class="{ 'icon-loading': loading }">
      <router-view v-show="!loading" :loading.sync="loading" />
      <EmptyContent v-if="isRoot" class="emp-content">
        <div v-if="activeProject">
          {{ t(appId, '{orchestraName} project registration for {projectName}', { orchestraName, projectName }) }}
        </div>
        <div v-else>
          {{ t(appId, '{orchestraName} project registration', { orchestraName }) }}
        </div>
        <template #icon>
          <img :src="icon">
        </template>
        <template #desc>
          <h2 v-if="!activeProject">
            {{ t(appId, 'The project registration for all projects is closed.') }}
          </h2>
        </template>
      </EmptyContent>
    </AppContent>
  </Content>
</template>

<script>
import { appName } from './config.js'
import InputText from './components/InputText'
import DebugInfo from './components/DebugInfo'

import { set as vueSet } from 'vue'
import AppContent from '@nextcloud/vue/dist/Components/AppContent'
import AppNavigation from '@nextcloud/vue/dist/Components/AppNavigation'
import AppNavigationItem from '@nextcloud/vue/dist/Components/AppNavigationItem'
import AppNavigationSettings from '@nextcloud/vue/dist/Components/AppNavigationSettings'
import Content from '@nextcloud/vue/dist/Components/Content'
import CheckboxRadioSwitch from '@nextcloud/vue/dist/Components/CheckboxRadioSwitch'
import EmptyContent from '@nextcloud/vue/dist/Components/EmptyContent'
import RichContenteditable from '@nextcloud/vue/dist/Components/RichContenteditable'
import { getCurrentUser } from '@nextcloud/auth'
import { loadState } from '@nextcloud/initial-state'

import { getInitialState } from './toolkit/services/InitialStateService'
import { useMemberDataStore } from './stores/memberData.js'
import { useAppDataStore } from './stores/appData.js'
import { mapWritableState } from 'pinia'

import Icon from '../img/cafevdbmembers.svg'

import mixinRegistrationData from './mixins/registationData.js'

const projects = loadState(appName, 'projects')
const activeProject = loadState(appName, 'activeProject')
const instruments = loadState(appName, 'instruments')
const countries = loadState(appName, 'countries')
const displayLocale = loadState(appName, 'displayLocale')

const initialState = getInitialState()

export default {
  name: 'ProjectRegistration',
  components: {
    AppContent,
    AppNavigation,
    AppNavigationItem,
    AppNavigationSettings,
    CheckboxRadioSwitch,
    Content,
    DebugInfo,
    EmptyContent,
    InputText,
    RichContenteditable,
  },
  setup() {
    const registrationData = useMemberDataStore()
    return { registrationData }
  },
  data() {
    return {
      icon: Icon,
      loading: true,
      readonly: true,
    }
  },
  mixins: [
    mixinRegistrationData,
  ],
  async created() {
    await this.initializeRegistrationData()
    this.readonly = false
    this.loading = false
  },
  computed: {
    isRoot() {
      console.info('ROUTE PATH', this.$route.path)
      return this.$route.path === '/' || this.$route.path === '/' + this.projectName
    },
  },
  watch: {
  },
  methods: {
  },
}
</script>
<style lang="scss" scoped>
.content.root-view::v-deep {
  height:100%;
  .app-content {
    overflow-y: auto;
  }
}

.app-navigation-entry.disabled::v-deep {
  opacity: 0.5;
  &, & * {
    cursor: default !important;
    pointer-events: none;
  }
}

.empty-content::v-deep {
  h2 ~ p {
    text-align: center;
  }
  .hint {
    color: var(--color-text-lighter);
  }
  .error-section {
    text-align: center;
    .error-info {
      font-weight: bold;
      font-style: italic;
      max-width: 66ex;
    }
    .hint {
      max-width: 66ex;
    }
  }
}

#app-navigation-vue.app-navigation--close::v-deep {
  .app-navigation-toggle {
    margin-right: calc(0px - var(--navigation-width) - var(--default-clickable-area));
  }
}
</style>
