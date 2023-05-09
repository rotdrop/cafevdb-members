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
          icon="icon-user"
          :class="{ disabled: !activeProject }"
          exact
        />
        <AppNavigationItem
          :to="{ name: 'participation', params: { projectName } }"
          :title="t(appId, 'Instrumentation and Events')"
          icon="icon-music"
          :class="{ disabled: !activeProject }"
          exact
        />
        <AppNavigationItem
          :to="{ name: 'projectOptions', params: { projectName } }"
          :title="t(appId, 'Options')"
          icon="icon-details"
          :class="{ disabled: !activeProject }"
          exact
        />
        <AppNavigationItem
          :to="{ name: 'submission', params: { projectName } }"
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
          <RouterButton v-if="activeProject"
                        :to="{ name: 'personalProfile', params: { projectName } }"
                        exact
                        icon="icon-confirm"
                        icon-position="right"
          >
            {{ t(appId, 'Start') }}
          </RouterButton>
          <h2 v-else>
            {{ t(appId, 'The project registration for all projects is closed.') }}
          </h2>
        </template>
      </EmptyContent>
      <!-- <Actions>
        <ActionRouter v-for="project in projects"
                      :key="project.id"
                      :title="project.name"
                      :to="{ name: 'home', params: { projectName: project.name } }"
        />
      </Actions> -->
    </AppContent>
  </Content>
</template>

<script>
import { appName } from './config.js'
import InputText from './components/InputText'
import DebugInfo from './components/DebugInfo'

import { set as vueSet } from 'vue'
import Actions from '@nextcloud/vue/dist/Components/Actions'
import ActionRouter from '@nextcloud/vue/dist/Components/ActionRouter'
import AppContent from '@nextcloud/vue/dist/Components/AppContent'
import AppNavigation from '@nextcloud/vue/dist/Components/AppNavigation'
import AppNavigationItem from '@nextcloud/vue/dist/Components/AppNavigationItem'
import AppNavigationSettings from '@nextcloud/vue/dist/Components/AppNavigationSettings'
import Content from '@nextcloud/vue/dist/Components/Content'
import CheckboxRadioSwitch from '@nextcloud/vue/dist/Components/CheckboxRadioSwitch'
import EmptyContent from '@nextcloud/vue/dist/Components/EmptyContent'
import { getCurrentUser } from '@nextcloud/auth'
import { loadState } from '@nextcloud/initial-state'
import RouterButton from './components/RouterButton'

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
    this.attachActionMenuHandlers()
    this.readonly = false
    this.loading = false
  },
  mounted() {
  },
  computed: {
    isRoot() {
      return this.$route.path === '/' || this.$route.path === '/' + this.projectName
    },
  },
  watch: {
    activeProject(newValue, oldValue) {
      const pageTitle = document.getElementById('nextcloud')
      pageTitle.innerHTML = t(appName, 'Project Application for {projectName}', this)
    },
  },
  methods: {
    attachActionMenuHandlers() {
      const headerActionsMenu = document.getElementById('header-actions-menu')
      const headerMenuItems = headerActionsMenu.querySelectorAll('a')
      const primaryAction = document.querySelector('#header-primary-action a')
      const menuToggle = document.getElementById('header-actions-toggle')
      primaryAction.addEventListener('click', (event) => {
        const headerActionsMenu = document.getElementById('header-actions-menu')
        event.preventDefault()
        event.stopPropagation()
        primaryAction.classList.toggle('menu-open')
        if (primaryAction.classList.contains('menu-open')) {
          headerActionsMenu.classList.add('open')
        } else {
          headerActionsMenu.classList.remove('open')
        }
      })
      headerMenuItems.forEach(anchor => anchor.addEventListener('click', (event) => {
        event.preventDefault()
        event.stopPropagation()
        const baseName = anchor.href.split('/').pop()
        if (baseName !== this.projectName) {
          this.$router.push('/' + baseName)
          for (const project of this.projects) {
            if (project.name === baseName) {
              this.activeProject = project
              break
            }
          }
        }
        headerActionsMenu.classList.remove('open')
        primaryAction.classList.remove('menu-open')
      }))
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
</style>
