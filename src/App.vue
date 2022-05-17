<template>
  <Content :app-name="appId">
    <AppNavigation>
      <template #list>
        <AppNavigationItem
          :to="{ name: '/' }"
          :title="t(appId, 'Home')"
          icon="icon-home"
          exact />
        <AppNavigationItem
          :to="{ name: 'personalProfile' }"
          :title="t(appId, 'Personal Profile')"
          icon="icon-files-dark"
          exact />
        <AppNavigationItem
          :to="{ name: 'bankAccounts' }"
          :title="t(appId, 'Bank Accounts')"
          icon="icon-files-dark"
          exact />
        <AppNavigationItem
          :to="{ name: 'instrumentInsurances' }"
          :title="t(appId, 'Instrument Insurances')"
          icon="icon-files-dark"
          exact />
        <AppNavigationItem
          :to="{ name: 'projects' }"
          :title="t(appId, 'Projects')"
          icon="icon-files-dark"
          exact />
      </template>
      <template #footer>
        <AppNavigationSettings>
          <CheckboxRadioSwitch :checked.sync="debug">
            {{ t(appId, 'Enable Debug') }}
          </CheckboxRadioSwitch>
        </AppNavigationSettings>
      </template>
    </AppNavigation>
    <AppContent :class="{'icon-loading': loading}">
      <router-view v-show="!loading" :loading.sync="loading" />
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
import EmptyContent from '@nextcloud/vue/dist/Components/EmptyContent'

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
  },
  data() {
    return {
      orchestraName: initialState?.orchestraName || t(appId, '[UNKNOWN]'),
      icon: Icon,
      loading: true,
    }
  },
  computed: {
    isRoot() {
      return this.$route.path === '/'
    },
    ...mapWritableState(useAppDataStore, ['debug']),
    ...mapWritableState(useMemberDataStore, ['memberData']),
  },
  /**
   * Fetch list of notes when the component is loaded
   */
  async mounted() {
    this.loading = false
  },
  methods: {
  },
}
</script>
<style lang="scss" scoped>
.blah {
  color: red;
}
</style>
