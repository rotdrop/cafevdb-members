<!--
 - @copyright Copyright (c) 2023, 2024, 2025 Claus-Justus Heine <himself@claus-justus-heine.de>
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
  <div :class="{ 'icon-loading': loading, 'page-container': true, loading, 'submission-view': true, }">
    <h2>
      {{ t(appName, 'Summary and Submission') }}
    </h2>
    <div class="navigation flex flex-row flex-justify-full flex-center">
      <RouterButton :to="{ name: 'registrationProjectOptions', params: { projectName } }"
                    exact
                    icon="icon-history"
                    icon-position="left"
      >
        {{ t(appName, 'back') }}
      </RouterButton>
      <NcButton @click="submit">
        {{ t(appName, 'Submit') }}
        <template #icon>
          <span class="icon-checkmark" />
        </template>
      </NcButton>
    </div>
  </div>
</template>
<script setup lang="ts">
import { appName } from '../../config.ts'
import { translate as t } from '@nextcloud/l10n'
import RouterButton from '../../components/RouterButton.vue'
import { NcButton } from '@nextcloud/vue'
import { useMemberDataStore } from '../../stores/memberData.ts'
import { useAppDataStore } from '../../stores/appData.ts'
import {
  ref,
  onBeforeMount,
} from 'vue'
import { storeToRefs } from 'pinia'
import axios from '@nextcloud/axios'
import generateAppUrl from '../../toolkit/util/generate-url.ts'
import { isAxiosErrorResponse } from '../../toolkit/types/axios-type-guards'
import { showError, TOAST_PERMANENT_TIMEOUT } from '@nextcloud/dialogs'
import logger from '../../logger.ts'

const loading = ref(true)
const readonly = ref(true)

const registrationData = useMemberDataStore()
const appData = useAppDataStore()
const {
  activeProject,
  projectName,
} = storeToRefs(appData)
const {
  registrationProject,
} = storeToRefs(registrationData)

const submit = async () => {
  try {
    const response = await axios.post(
      generateAppUrl(
        'registration/{projectName}/submit',
        { projectName: projectName.value },
      ),
      {
        data: registrationProject.value,
      },
    )
    logger.info('Submission Response', { response })
  } catch (e) {
    console.error('ERROR', e)
    let message = t(appName, 'reason unknown')
    if (isAxiosErrorResponse(e) && e.response.data) {
      const data = e.response.data as { messages?: string[] }
      if (Array.isArray(data.messages)) {
        message = data.messages.join(' ')
      }
    }
    showError(t(appName, 'Could submit the registration data: {message}', { message }), { timeout: TOAST_PERMANENT_TIMEOUT })
  }
}

onBeforeMount(async () => {
  if (!activeProject.value) {
    appData.gotoRegistratzionHome()
    return
  }
  await registrationData.initializeRegistrationData()
  readonly.value = false
  loading.value = false
})
</script>
<style lang="scss" scoped>
.page-container {
  padding: 12px 0.5em 0 50px;
  min-height:100%;
  &.loading {
    width:100%;
    * {
      display:none;
    }
  }
}

.navigation {
  margin:0.5em 0;
}

.flex {
  display: flex;
  &.flex-row {
    flex-direction: row;
  }
  &.flex-justify-full {
    justify-content: space-between;
  }
  &.flex-center {
    align-items: center;
  }
}

.input-row {
  display:flex;
  flex-wrap:wrap;
  > * {
    flex: 1 0 40%;
    min-width:20em;
    &.input-type-number {
      flex: 1 0 5%;
      min-width:5em;
    }
    &.input-type-date {
      flex: 0 0 234px;
      width:234px;
      min-width:210px;
    }
  }
  ::v-deep .input-effect {
    margin-bottom:0;
  }
}
</style>
