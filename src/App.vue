<!--
 * @copyright Copyright (c) 2022-2024 Claus-Justus Heine <himself@claus-justus-heine.de>
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
 -
 -->
<template>
  <NcContent :app-name="appId">
    <NcAppNavigation>
      <template #list>
        <NcAppNavigationItem :to="{ name: '/' }"
                             :name="t(appId, 'Home')"
                             icon="icon-home"
                             exact
                             @click="showSidebar = false"
        />
        <NcAppNavigationItem :to="memberDataError ? {} : { name: 'personalProfile' }"
                             :name="t(appId, 'Personal Profile')"
                             icon="icon-files-dark"
                             :class="{ disabled: memberDataError }"
                             exact
                             @click="showSidebar = false"
        />
        <NcAppNavigationItem :to="memberDataError ? {} : { name: 'bankAccounts' }"
                             :name="t(appId, 'Bank Accounts')"
                             icon="icon-files-dark"
                             :class="{ disabled: memberDataError }"
                             exact
                             @click="showSidebar = false"
        />
        <NcAppNavigationItem :to="memberDataError ? {} : { name: 'instrumentInsurances' }"
                             :name="t(appId, 'Instrument Insurances')"
                             icon="icon-files-dark"
                             :class="{ disabled: memberDataError }"
                             exact
                             @click="showSidebar = false"
        />
        <NcAppNavigationItem :to="memberDataError ? {} : { name: 'projects' }"
                             :name="t(appId, 'Projects')"
                             icon="icon-files-dark"
                             :class="{ disabled: memberDataError }"
                             exact
                             @click="showSidebar = false"
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

    <NcAppContent :class="{ 'icon-loading': loading }" @insurance-details="showSidebar = true">
      <router-view v-show="!loading && !memberDataError" :loading.sync="loading" @view-details="handleDetailsRequest" />
      <NcEmptyContent v-if="isRoot || memberDataError" class="emp-content">
        {{ t(appId, '{orchestraName} Orchestra Member Portal', { orchestraName, }) }}
        <template #icon>
          <img :src="icon">
        </template>
        <template #description>
          <div v-if="memberDataError" class="error-section">
            <p class="error-info">
              {{ t(appId, 'Error') + ': ' + memberDataError }}
            </p>
            <button class="button primary" @click="putRecryptionRequest">
              {{ t(appId, 'Request Access to my personal Data') }}
            </button>
            <p class="hint">
              {{ t(appId, 'The authorization request has to be processed by a human being, this means that it will need some time before you are granted access to your data. You will be notified by the cloud-software when the request has been processed.') }}
            </p>
          </div>
        </template>
      </NcEmptyContent>
    </NcAppContent>

    <NcAppSidebar v-show="showSidebar"
                  :name="sidebarTitle"
                  :loading.sync="loading"
                  @close="closeSidebar"
    >
      <NcAppSidebarTab v-if="sidebarView === 'InstrumentInsurances'"
                       id="details-side-bar"
                       icon="icon-share"
                       :name="t(appId, 'details')"
      >
        <InsuranceDetails v-bind="sidebarProps" />
      </NcAppSidebarTab>
      <NcAppSidebarTab v-if="sidebarView === 'Projects'"
                       id="details-side-bar"
                       icon="icon-share"
                       :name="t(appId, 'details')"
      >
        <ProjectDetails v-bind="sidebarProps" />
      </NcAppSidebarTab>
    </NcAppSidebar>
  </NcContent>
</template>

<script>
import { appName as appId } from './config.js'
import { getCurrentUser } from '@nextcloud/auth'
import {
  NcContent,
  NcAppContent,
  NcAppNavigation,
  NcAppNavigationItem,
  NcAppNavigationSettings,
  NcCheckboxRadioSwitch,
  NcAppSidebar,
  NcAppSidebarTab,
  NcEmptyContent,
} from '@nextcloud/vue'

import { generateOcsUrl } from '@nextcloud/router'
import { showError, showInfo, TOAST_PERMANENT_TIMEOUT } from '@nextcloud/dialogs'
import axios from '@nextcloud/axios'

import InsuranceDetails from './views/InstrumentInsurances/InsuranceDetails.vue'
import ProjectDetails from './views/Projects/ProjectDetails.vue'

import Icon from '../img/cafevdbmembers.svg'

import { getInitialState } from './toolkit/services/InitialStateService.js'
import { useMemberDataStore } from './stores/memberData.js'
import { useAppDataStore } from './stores/appData.js'
import { mapWritableState } from 'pinia'

const initialState = getInitialState()

export default {
  name: 'App',
  components: {
    NcAppContent,
    NcAppNavigation,
    NcAppNavigationItem,
    NcAppNavigationSettings,
    NcCheckboxRadioSwitch,
    NcContent,
    NcEmptyContent,
    NcAppSidebar,
    NcAppSidebarTab,
    InsuranceDetails,
    ProjectDetails,
  },
  setup() {
    const memberData = useMemberDataStore()
    return { memberData }
  },
  data() {
    return {
      orchestraName: initialState?.orchestraName || t(appId, '[UNKNOWN]'),
      icon: Icon,
      loading: true,
      showSidebar: false,
      sidebarTitle: '',
      sidebarView: '',
      sidebarProps: {},
      memberDataPollTimer: null,
      memberDataPollTimeout: 60 * 1000,
    }
  },
  computed: {
    isRoot() {
      return this.$route.path === '/'
    },
    memberDataError() {
      return this.memberData.initialized.error
    },
    ...mapWritableState(useAppDataStore, ['debug']),
    // ...mapWritableState(useMemberDataStore, ['memberData']),
  },
  watch: {
    memberDataError(newVal, oldVal) {
      if (oldVal && this.memberDataPollTimer) {
        clearTimeout(this.memberDataPollTimer)
        this.memberDataPollTimer = null
      } else if (newVal && !this.memberDataPollTimer) {
        this.memberDataPollTimer = setTimeout(() => this.pollMemberData(), this.memberDataPollTimeout)
      }
    },
  },
  async created() {
    this.memberData.initialized.error = false
    await this.memberData.initialize(true, true) // silent and reset
    this.loading = false
  },
  methods: {
    closeSidebar() {
      this.showSidebar = false
    },
    handleDetailsRequest(data) {
      this.showSidebar = true
      this.sidebarTitle = data.title
      this.sidebarView = data.viewName
      this.sidebarProps = data.props
    },
    async pollMemberData() {
      await this.memberData.initialize(true, false) // silent, do not reset
      if (this.memberDataError) {
        this.memberDataPollTimer = setTimeout(() => this.pollMemberData(), this.memberDataPollTimeout)
      } else {
        this.memberDataPollTimer = null
        this.loading = false
      }
    },
    async putRecryptionRequest() {
      const cloudUser = getCurrentUser() || {}
      if (!cloudUser.uid) {
        showError(t(appId, 'Unable to determine the identity of the current user.'))
      }
      const userId = cloudUser.uid
      try {
        const url = generateOcsUrl('apps/cafevdb/api/v1/maintenance/encryption/recrypt/{userId}', { userId })
        await axios.put(url + '?format=json')
        showInfo(t(appId, 'The authorization request for {userId} has been submitted successfully', { userId }))
      } catch (e) {
        console.info('ERROR', e)
        let message = t(appId, 'reason unknown')
        if (e.response && e.response.data) {
          const data = e.response.data
          if (data.ocs && data.ocs.meta && data.ocs.meta.message) {
            message = data.ocs.meta.message
          }
        }
        showError(t(appId, 'Unable to handle access action: {message}', { message }), { timeout: TOAST_PERMANENT_TIMEOUT })
      }
    },
  },
}
</script>
<style lang="scss" scoped>
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
</style>
