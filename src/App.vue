<!--
 * @copyright Copyright (c) 2022-2025 Claus-Justus Heine <himself@claus-justus-heine.de>
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
          <img :src="Icon">
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
<script setup lang="ts">
import { appName as appId } from './config.ts'
import { translate as t } from '@nextcloud/l10n'
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
import { useMemberDataStore } from './stores/memberData.ts'
import { useAppDataStore } from './stores/appData.ts'
import { storeToRefs } from 'pinia'
import {
  computed,
  ref,
  watch,
} from 'vue'
import {
  useRoute,
} from 'vue-router/composables'
import { isAxiosErrorResponse } from './toolkit/types/axios-type-guards'
import type { OCSResponse } from '@nextcloud/typings/ocs'

const initialState = getInitialState()
const memberData = useMemberDataStore()
const appData = useAppDataStore()
const { debug } = storeToRefs(appData)

const orchestraName = computed(() => initialState?.orchestraName || t(appId, '[UNKNOWN]'))
const loading = ref(true)
const showSidebar = ref(false)
const sidebarTitle = ref('')
const sidebarView = ref('')
// eslint-disable-next-line @typescript-eslint/no-explicit-any
const sidebarProps = ref<Record<string, any> >({})
let memberDataPollTimer = null as null|ReturnType<typeof setTimeout>
const memberDataPollTimeout = 60 * 1000

const currentRoute = useRoute()
const isRoot = computed(() => currentRoute.path === '/')
const memberDataError = computed(() => memberData.initialized.error)

watch(memberDataError, (newVal, oldVal) => {
  if (oldVal && memberDataPollTimer) {
    clearTimeout(memberDataPollTimer)
    memberDataPollTimer = null
  } else if (newVal && !memberDataPollTimer) {
    memberDataPollTimer = setTimeout(() => pollMemberData(), memberDataPollTimeout)
  }
})

memberData.initialized.error = null
memberData.initialize(true, true).finally(() => { loading.value = false })

const closeSidebar = () => { showSidebar.value = false }

// eslint-disable-next-line @typescript-eslint/no-explicit-any
const handleDetailsRequest = (data: { title: string, viewName: string, props: Record<string, any> }) => {
  showSidebar.value = true
  sidebarTitle.value = data.title
  sidebarView.value = data.viewName
  sidebarProps.value = data.props
}

const pollMemberData = async () => {
  await memberData.initialize(true, false) // silent, do not reset
  if (memberDataError.value) {
    memberDataPollTimer = setTimeout(() => pollMemberData(), memberDataPollTimeout)
  } else {
    memberDataPollTimer = null
    loading.value = false
  }
}

const putRecryptionRequest = async () => {
  const cloudUser = getCurrentUser()
  if (!cloudUser) {
    showError(t(appId, 'Unable to determine the identity of the current user.'))
    return
  }
  const userId = cloudUser.uid
  try {
    const url = generateOcsUrl('apps/cafevdb/api/v1/maintenance/encryption/recrypt/{userId}', { userId })
    await axios.put(url + '?format=json')
    showInfo(t(appId, 'The authorization request for {userId} has been submitted successfully', { userId }))
  } catch (e) {
    console.info('ERROR', e)
    let message = t(appId, 'reason unknown')
    if (isAxiosErrorResponse(e) && e.response.data) {
      const data = e.response.data as OCSResponse
      if (data.ocs && data.ocs.meta && data.ocs.meta.message) {
        message = data.ocs.meta.message
      }
    }
    showError(
      t(appId, 'Unable to handle access action: {message}', { message }),
      { timeout: TOAST_PERMANENT_TIMEOUT },
    )
  }
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
