<template>
  <Content :class="'app-' + appName" :app-name="'app-' + appName">
    <AppNavigation>
      <template #list>
        <AppNavigationItem
          :to="{ name: '/' }"
          :title="t(appName, 'Home')"
          icon="icon-home"
          exact />
        <AppNavigationItem
          :to="{ name: 'personalProfile' }"
          :title="t(appName, 'Personal Profile')"
          icon="icon-files-dark"
          exact />
        <AppNavigationItem
          :to="{ name: 'bankAccounts' }"
          :title="t(appName, 'Bank Accounts')"
          icon="icon-files-dark"
          exact />
        <AppNavigationItem
          :to="{ name: 'instrumentInsurances' }"
          :title="t(appName, 'Instrument Insurances')"
          icon="icon-files-dark"
          exact />
        <AppNavigationItem
          :to="{ name: 'projects' }"
          :title="t(appName, 'Projects')"
          icon="icon-files-dark"
          exact />
      </template>
      <template #footer>
        <AppNavigationSettings>
          {{ t(appName, 'Example Settings') }}
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
            {{ t(appName, '{orchestraName} Orchestra Member Portal', { orchestraName, }) }}
          </p>
        </template>
      </EmptyContent>
    </AppContent>
  </Content>
</template>

<script>
import { appName } from './config.js'
import Content from '@nextcloud/vue/dist/Components/Content'
import AppContent from '@nextcloud/vue/dist/Components/AppContent'
import AppNavigation from '@nextcloud/vue/dist/Components/AppNavigation'
import AppNavigationItem from '@nextcloud/vue/dist/Components/AppNavigationItem'
import AppNavigationSettings from '@nextcloud/vue/dist/Components/AppNavigationSettings'
import EmptyContent from '@nextcloud/vue/dist/Components/EmptyContent'

import Icon from '../img/cafevdbmembers.svg'

import { getInitialState } from './services/InitialStateService'

const initialState = getInitialState()

export default {
  name: 'App',
  components: {
    AppContent,
    AppNavigation,
    AppNavigationItem,
    AppNavigationSettings,
    Content,
    EmptyContent,
  },
  data() {
    return {
      orchestraName: initialState?.orchestraName || t(appName, '[UNKNOWN]'),
      icon: Icon,
      loading: true,
    }
  },
  computed: {
    isRoot() {
      return this.$route.path === '/'
    },
  },
  /**
   * Fetch list of notes when the component is loaded
   */
  async mounted() {
    console.info('MOUNTED')
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
