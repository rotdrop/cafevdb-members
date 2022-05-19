<template>
  <Content :app-name="appId">
    <AppNavigation>
      <template #list>
        <AppNavigationItem
          :to="{ name: '/' }"
          :title="t(appId, 'Home')"
          icon="icon-home"
          exact
          @click="showSidebar = false" />
        <AppNavigationItem
          :to="{ name: 'personalProfile' }"
          :title="t(appId, 'Personal Profile')"
          icon="icon-files-dark"
          exact
          @click="showSidebar = false" />
        <AppNavigationItem
          :to="{ name: 'bankAccounts' }"
          :title="t(appId, 'Bank Accounts')"
          icon="icon-files-dark"
          exact
          @click="showSidebar = false" />
        <AppNavigationItem
          :to="{ name: 'instrumentInsurances' }"
          :title="t(appId, 'Instrument Insurances')"
          icon="icon-files-dark"
          exact
          @click="showSidebar = false" />
        <AppNavigationItem
          :to="{ name: 'projects' }"
          :title="t(appId, 'Projects')"
          icon="icon-files-dark"
          exact
          @click="showSidebar = false" />
      </template>
      <template #footer>
        <AppNavigationSettings>
          <CheckboxRadioSwitch :checked.sync="debug">
            {{ t(appId, 'Enable Debug') }}
          </CheckboxRadioSwitch>
        </AppNavigationSettings>
      </template>
    </AppNavigation>

    <AppContent :class="{'icon-loading': loading}" @insurance-details="showSidebar = true">
      <router-view v-show="!loading" :loading.sync="loading" @view-details="handleDetailsRequest" />
      <EmptyContent v-if="isRoot" class="emp-content">
        <template #icon>
          <img :src="icon">
        </template>
        <template #desc>
          <p>
            {{ t(appId, '{orchestraName} Orchestra Member Portal', { orchestraName, }) }}
          </p>
        </template>
      </EmptyContent>
    </AppContent>

    <AppSidebar v-show="showSidebar"
                :title="sidebarTitle"
                :loading.sync="loading"
                @close="closeSidebar">
      <AppSidebarTab id="details-side-bar"
                     icon="icon-share"
                     :name="t(appId, 'details')">
        <InsuranceDetails v-bind="sidebarProps" />
      </AppSidebarTab>
    </AppSidebar>
  </Content>
</template>

<script>
import { appName as appId } from './config.js'
import Content from '@nextcloud/vue/dist/Components/Content'
import AppContent from '@nextcloud/vue/dist/Components/AppContent'
import AppNavigation from '@nextcloud/vue/dist/Components/AppNavigation'
import AppNavigationItem from '@nextcloud/vue/dist/Components/AppNavigationItem'
import AppNavigationSettings from '@nextcloud/vue/dist/Components/AppNavigationSettings'
import CheckboxRadioSwitch from '@nextcloud/vue/dist/Components/CheckboxRadioSwitch'
import AppSidebar from '@nextcloud/vue/dist/Components/AppSidebar'
import AppSidebarTab from '@nextcloud/vue/dist/Components/AppSidebarTab'
import EmptyContent from '@nextcloud/vue/dist/Components/EmptyContent'

import InsuranceDetails from './views/InstrumentInsurances/InsuranceDetails'

import Icon from '../img/cafevdbmembers.svg'

import { getInitialState } from './services/InitialStateService'
// import { useMemberDataStore } from './stores/memberData.js'
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
  },
  data() {
    return {
      orchestraName: initialState?.orchestraName || t(appId, '[UNKNOWN]'),
      icon: Icon,
      loading: false,
      showSidebar: false,
      sidebarTitle: '',
      sidebarView: '',
      sidebarProps: {},
    }
  },
  computed: {
    isRoot() {
      console.info('PATH', this.$route.path)
      return this.$route.path === '/'
    },
    ...mapWritableState(useAppDataStore, ['debug']),
    // ...mapWritableState(useMemberDataStore, ['memberData']),
  },
  mounted() {
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
      console.info('VIEW', this.sidebarView)
    },
  },
}
</script>
<style lang="scss" scoped>
.blah {
  color: red;
}
</style>
