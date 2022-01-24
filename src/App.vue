<template>
  <div id="content" :class="'app-' + appName">
    <AppNavigation>
      <AppNavigationNew v-if="!loading"
                        :text="t(appName, 'Dummy New Button')"
                        :disabled="false"
                        :button-id="appName + '-new-button'"
                        button-class="icon-add"
                        @click="dummyClick" />
    </AppNavigation>
    <AppContent>
      <div class="data-display">
        <!-- <div class="icon-file" /> -->
        <h2>{{ t(appName, 'Dummy Text') }}</h2>
        <pre>{{ JSON.stringify(memberData, null, 2) }}</pre>
      </div>
    </AppContent>
  </div>
</template>

<script>
import { appName } from './config.js'

import AppContent from '@nextcloud/vue/dist/Components/AppContent'
import AppNavigation from '@nextcloud/vue/dist/Components/AppNavigation'
import AppNavigationNew from '@nextcloud/vue/dist/Components/AppNavigationNew'

import '@nextcloud/dialogs/styles/toast.scss'
import { generateUrl } from '@nextcloud/router'
import { showError, TOAST_PERMANENT_TIMEOUT } from '@nextcloud/dialogs'
import axios from '@nextcloud/axios'

export default {
  name: 'App',
  components: {
    AppContent,
    AppNavigation,
    AppNavigationNew,
  },
  data() {
    return {
      memberData: {},
      loading: true,
    }
  },
  /**
   * Fetch list of notes when the component is loaded
   */
  async mounted() {
    console.info('MOUNTED')
    try {
      const response = await axios.get(generateUrl('/apps/' + appName + '/member'))
      this.memberData = response.data
    } catch (e) {
      console.error('ERROR', e)
      let message = t(appName, 'reason unknown')
      if (e.response && e.response.data && e.response.data.message) {
        message = e.response.data.message
        console.info('RESPONSE', e.response)
      }
      showError(t(appName, 'Could not fetch musician(s): {message}', { message }), { timeout: TOAST_PERMANENT_TIMEOUT })
    }
    this.loading = false
  },
  methods: {
    dummyClick() {
      console.info('DUMMY CLICK')
    },
  },
}
</script>
<style lang="scss" scoped>
  #app-content-vue > div {
    width: 100%;
    height: 100%;
    padding: 20px;
    display: flex;
    flex-direction: column;
    flex-grow: 1;
    &.data-display {
      h2 {
        margin-left:44px;
      }
    }
  }

  input[type='text'] {
    width: 100%;
  }

  textarea {
    flex-grow: 1;
    width: 100%;
  }
</style>
