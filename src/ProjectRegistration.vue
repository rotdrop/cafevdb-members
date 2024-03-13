<!--
 - @copyright Copyright (c) 2023, 2024 Claus-Justus Heine <himself@claus-justus-heine.de>
 -
 - @author Claus-Justus Heine <himself@claus-justus-heine.de>
 -
 - @license AGPL-3.0-or-later
 -
 - This program is free software: you can redistribute it and/or modify
 - it under the terms of the GNU Affero General Public License as
 - published by the Free Software Foundation, either version 3 of the
 - License, or (at your option) any later version.
 -
 - This program is distributed in the hope that it will be useful,
 - but WITHOUT ANY WARRANTY; without even the implied warranty of
 - MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 - GNU Affero General Public License for more details.
 -
 - You should have received a copy of the GNU Affero General Public License
 - along with this program. If not, see <http://www.gnu.org/licenses/>.
 -->
<template>
  <NcContent :class="{ 'icon-loading': loading, 'root-view': true }" :app-name="appId">
    <NcAppNavigation>
      <template #list>
        <NcAppNavigationItem :to="routerDestination('registrationHome')"
                             :title="isPublicPage ? t(appId, 'Home') : t(appId, 'Start Registration')"
                             icon="icon-home"
                             exact
        />
        <NcAppNavigationItem :to="routerDestination('registrationPersonalProfile')"
                             :title="t(appId, 'Personal Profile')"
                             icon="icon-user"
                             :class="{ disabled: !activeProject }"
                             exact
        />
        <NcAppNavigationItem :to="routerDestination('registrationParticipation')"
                             :title="t(appId, 'Instrumentation and Events')"
                             icon="icon-music"
                             :class="{ disabled: !activeProject }"
                             exact
        />
        <NcAppNavigationItem :to="routerDestination('registrationProjectOptions')"
                             :title="t(appId, 'Options')"
                             icon="icon-details"
                             :class="{ disabled: !activeProject }"
                             exact
        />
        <NcAppNavigationItem :to="routerDestination('registrationSubmission')"
                             :title="t(appId, 'Summary and Submission')"
                             icon="icon-checkmark"
                             :class="{ disabled: !activeProject }"
                             exact
        />
      </template>
      <template #footer>
        <NcAppNavigationSettings>
          <NcCheckboxRadioSwitch :checked.sync="debug">
            {{ t(appId, 'Enable Debug') }}
          </NcCheckboxRadioSwitch>
        </NcAppNavigationSettings>
      </template>
    </NcAppNavigation>
    <NcAppContent :class="{ 'icon-loading': loading }">
      <router-view v-show="!loading" :loading.sync="loading" />
      <NcEmptyContent v-if="isRoot" class="emp-content">
        <template #icon>
          <img :src="icon">
        </template>
        <template #title>
          <div v-if="activeProject">
            {{ t(appId, '{orchestraName} project registration for {projectName}', { orchestraName, projectName }) }}
          </div>
          <div v-else>
            {{ t(appId, '{orchestraName} project registration', { orchestraName }) }}
          </div>
        </template>
        <template #description>
          <div v-if="activeProject"
               class="flex-container flex-center"
          >
            <NcActions v-if="projects.length > 1"
                       :menu-title="t(appId, 'choose another one')"
            >
              <NcActionRouter v-for="project in projects"
                              :key="project.id"
                              :title="project.name"
                              :to="{ name: 'registrationHome', params: { projectName: project.name } }"
              />
            </NcActions>
            <span v-if="projects.length > 1" class="start-button-junctor">{{ t(appId, 'or') }}</span>
            <RouterButton :to="{ name: 'registrationPersonalProfile', params: projectName ? { projectName } : {} }"
                          exact
                          icon="icon-confirm"
                          icon-position="right"
            >
              {{ t(appId, 'register') }}
            </RouterButton>
            <span v-if="isPublicPage" class="start-button-junctor">{{ t(appId, 'or') }}</span>
            <RouterButton v-if="isPublicPage"
                          :to="loginRedirection('registrationHome')"
                          :external="true"
                          icon="icon-confirm"
                          icon-position="right"
            >
              {{ t(appId, 'login and register') }}
            </RouterButton>
          </div>
          <h2 v-else>
            {{ t(appId, 'The project registration for all projects is closed.') }}
          </h2>
        </template>
      </NcEmptyContent>
    </NcAppContent>
  </NcContent>
</template>

<script>
import { appName } from './config.js'

import {
  NcActions,
  NcActionRouter,
  NcAppContent,
  NcAppNavigation,
  NcAppNavigationItem,
  NcAppNavigationSettings,
  NcContent,
  NcCheckboxRadioSwitch,
  NcEmptyContent,
} from '@nextcloud/vue'
import { getCurrentUser } from '@nextcloud/auth'
import RouterButton from './components/RouterButton.vue'

import { useMemberDataStore } from './stores/memberData.js'

import Icon from '../img/cafevdbmembers.svg'

import mixinRegistrationData from './mixins/registrationData.js'

import { prefix as registrationPrefix } from './router/registration-routes.js'

export default {
  name: 'ProjectRegistration',
  components: {
    NcActionRouter,
    NcActions,
    NcAppContent,
    NcAppNavigation,
    NcAppNavigationItem,
    NcAppNavigationSettings,
    NcCheckboxRadioSwitch,
    NcContent,
    NcEmptyContent,
    RouterButton,
  },
  mixins: [
    mixinRegistrationData,
  ],
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
  async created() {
    await this.initializeRegistrationData()
    this.setPageTitle()
    this.readonly = false
    this.loading = false
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

.app-navigation-entry-wrapper.balhdisabled::v-deep {
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
