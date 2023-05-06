<template>
  <Content :app-name="appId">
    <AppNavigation>
      <template #list>
        <AppNavigationItem
          :to="{ name: '/' }"
          :title="t(appId, 'Home')"
          icon="icon-home"
          exact
          @click="showSidebar = false"
        />
        <AppNavigationItem
          :to="memberDataError ? {} : { name: 'personalProfile' }"
          :title="t(appId, 'Personal Profile')"
          icon="icon-files-dark"
          :class="{ disabled: memberDataError }"
          exact
          @click="showSidebar = false"
        />
        <AppNavigationItem
          :to="memberDataError ? {} : { name: 'bankAccounts' }"
          :title="t(appId, 'Bank Accounts')"
          icon="icon-files-dark"
          :class="{ disabled: memberDataError }"
          exact
          @click="showSidebar = false"
        />
        <AppNavigationItem
          :to="memberDataError ? {} : { name: 'instrumentInsurances' }"
          :title="t(appId, 'Instrument Insurances')"
          icon="icon-files-dark"
          :class="{ disabled: memberDataError }"
          exact
          @click="showSidebar = false"
        />
        <AppNavigationItem
          :to="memberDataError ? {} : { name: 'projects' }"
          :title="t(appId, 'Projects')"
          icon="icon-files-dark"
          :class="{ disabled: memberDataError }"
          exact
          @click="showSidebar = false"
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

    <AppContent :class="{ 'icon-loading': loading }" @insurance-details="showSidebar = true">
      <router-view v-show="!loading && !memberDataError" :loading.sync="loading" @view-details="handleDetailsRequest" />
      <EmptyContent v-if="isRoot || memberDataError" class="emp-content">
        {{ t(appId, '{orchestraName} Orchestra Member Portal', { orchestraName, }) }}
        <template #icon>
          <img :src="icon">
        </template>
        <template #desc>
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
      </EmptyContent>
    </AppContent>

    <AppSidebar v-show="showSidebar"
                :title="sidebarTitle"
                :loading.sync="loading"
                @close="closeSidebar"
    >
      <AppSidebarTab v-if="sidebarView === 'InstrumentInsurances'"
                     id="details-side-bar"
                     icon="icon-share"
                     :name="t(appId, 'details')"
      >
        <InsuranceDetails v-bind="sidebarProps" />
      </AppSidebarTab>
      <AppSidebarTab v-if="sidebarView === 'Projects'"
                     id="details-side-bar"
                     icon="icon-share"
                     :name="t(appId, 'details')"
      >
        <ProjectDetails v-bind="sidebarProps" />
      </AppSidebarTab>
    </AppSidebar>
  </Content>
</template>

<script>
import { appName as appId } from './config.js'
import { getCurrentUser } from '@nextcloud/auth'
import Content from '@nextcloud/vue/dist/Components/Content'
import AppContent from '@nextcloud/vue/dist/Components/AppContent'
import AppNavigation from '@nextcloud/vue/dist/Components/AppNavigation'
import AppNavigationItem from '@nextcloud/vue/dist/Components/AppNavigationItem'
import AppNavigationSettings from '@nextcloud/vue/dist/Components/AppNavigationSettings'
import CheckboxRadioSwitch from '@nextcloud/vue/dist/Components/CheckboxRadioSwitch'
import AppSidebar from '@nextcloud/vue/dist/Components/AppSidebar'
import AppSidebarTab from '@nextcloud/vue/dist/Components/AppSidebarTab'
import EmptyContent from '@nextcloud/vue/dist/Components/EmptyContent'

import '@nextcloud/dialogs/styles/toast.scss'
import { generateOcsUrl } from '@nextcloud/router'
import { showError, showInfo, TOAST_PERMANENT_TIMEOUT } from '@nextcloud/dialogs'
import axios from '@nextcloud/axios'

import InsuranceDetails from './views/InstrumentInsurances/InsuranceDetails'
import ProjectDetails from './views/Projects/ProjectDetails'

import Icon from '../img/cafevdbmembers.svg'

import { getInitialState } from './services/InitialStateService'
import { useMemberDataStore } from './stores/memberData.js'
import { useAppDataStore } from './stores/appData.js'
import { mapWritableState } from 'pinia'

const initialState = getInitialState()

export default {
  name: 'App',
  components: {
    AppContent,
    AppNavigation,
    AppNavigationItem,
    AppNavigationSettings,
    CheckboxRadioSwitch,
    Content,
    EmptyContent,
    AppSidebar,
    AppSidebarTab,
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
