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
          :to="{ name: 'registrationHome', params: { projectName } }"
          :title="isPublicPage ? t(appId, 'Home') : t(appId, 'Start Registration')"
          icon="icon-home"
          exact
        />
        <AppNavigationItem
          :to="{ name: 'registrationPersonalProfile', params: { projectName } }"
          :title="t(appId, 'Personal Profile')"
          icon="icon-user"
          :class="{ disabled: !activeProject }"
          exact
        />
        <AppNavigationItem
          :to="{ name: 'registrationParticipation', params: { projectName } }"
          :title="t(appId, 'Instrumentation and Events')"
          icon="icon-music"
          :class="{ disabled: !activeProject }"
          exact
        />
        <AppNavigationItem
          :to="{ name: 'registrationProjectOptions', params: { projectName } }"
          :title="t(appId, 'Options')"
          icon="icon-details"
          :class="{ disabled: !activeProject }"
          exact
        />
        <AppNavigationItem
          :to="{ name: 'registrationSubmission', params: { projectName } }"
          :title="t(appId, 'Summary and Submission')"
          icon="icon-checkmark"
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
          <div v-if="activeProject"
               class="flex-container flex-center"
          >
            <Actions v-if="projects.length > 1"
                     :menu-title="t(appId, 'choose another one')"
            >
              <ActionRouter v-for="project in projects"
                            :key="project.id"
                            :title="project.name"
                            :to="{ name: 'registrationHome', params: { projectName: project.name } }"
              />
            </Actions>
            <span v-if="projects.length > 1" class="start-button-junctor">{{ t(appId, 'or') }}</span>
            <RouterButton :to="{ name: 'registrationPersonalProfile', params: { projectName } }"
                          exact
                          icon="icon-confirm"
                          icon-position="right"
            >
              {{ t(appId, 'start') }}
            </RouterButton>
          </div>
          <h2 v-else>
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
import Actions from '@nextcloud/vue/dist/Components/NcActions'
import ActionRouter from '@nextcloud/vue/dist/Components/NcActionRouter'
import AppContent from '@nextcloud/vue/dist/Components/NcAppContent'
import AppNavigation from '@nextcloud/vue/dist/Components/NcAppNavigation'
import AppNavigationItem from '@nextcloud/vue/dist/Components/NcAppNavigationItem'
import AppNavigationSettings from '@nextcloud/vue/dist/Components/NcAppNavigationSettings'
import Content from '@nextcloud/vue/dist/Components/NcContent'
import CheckboxRadioSwitch from '@nextcloud/vue/dist/Components/NcCheckboxRadioSwitch'
import EmptyContent from '@nextcloud/vue/dist/Components/NcEmptyContent'
import { getCurrentUser } from '@nextcloud/auth'
import { loadState } from '@nextcloud/initial-state'
import RouterButton from './components/RouterButton'

import { getInitialState } from './toolkit/services/InitialStateService'
import { useMemberDataStore } from './stores/memberData.js'
import { useAppDataStore } from './stores/appData.js'
import { mapWritableState } from 'pinia'

import Icon from '../img/cafevdbmembers.svg'

import mixinRegistrationData from './mixins/registationData.js'

import { prefix as registrationPrefix } from './router/registration-routes.js'

const projects = loadState(appName, 'projects')
const activeProject = loadState(appName, 'activeProject')
const instruments = loadState(appName, 'instruments')
const countries = loadState(appName, 'countries')
const displayLocale = loadState(appName, 'displayLocale')

const initialState = getInitialState()

export default {
  name: 'ProjectRegistration',
  components: {
    Actions,
    ActionRouter,
    AppContent,
    AppNavigation,
    AppNavigationItem,
    AppNavigationSettings,
    CheckboxRadioSwitch,
    Content,
    DebugInfo,
    EmptyContent,
    InputText,
    RouterButton,
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
    this.setPageTitle()
    this.readonly = false
    this.loading = false
  },
  mounted() {
  },
  computed: {
    isRoot() {
      const result = this.$route.path === registrationPrefix
          || this.$route.path === registrationPrefix + '/'
          || this.$route.path === registrationPrefix + '/' + this.projectName
      console.info('ROUTE PATH', this.$route.path, registrationPrefix, this.projectName, result)
      return result
    },
    isPublicPage() {
      return !getCurrentUser()
    },
  },
  watch: {
    activeProject(newValue, oldValue) {
      this.setPageTitle()
    },
  },
  methods: {
    setPageTitle() {
      if (getCurrentUser()) {
        return
      }
      const pageTitleElement = document.getElementById('nextcloud')
      const pageTitle = this.activeProject
        ? t(appName, 'Project Application for {projectName}', this)
        : t(appName, 'Project Application')
      pageTitleElement.innerHTML = pageTitle
    },
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

span {
  &[class^='icon-'], &[class*=' icon-'] {
    display: inline-block;
  }
  &.right-icon {
    margin-left: 1ex;
  }
  &.left-icon {
    margin-right: 1ex;
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

.start-button-junctor {
  margin: 0 1ex;
}

.flex-container {
  display: flex;
  &.flex-center {
    align-items:center;
  }
}
</style>
