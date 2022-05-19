<script>
/**
 * @copyright Copyright (c) 2022 Claus-Justus Heine <himself@claus-justus-heine.de>
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
  <Content :app-name="appId">
    <div v-if="loading" class="page-container loading" />
    <div v-else class="page-container">
      <h2>
        {{ t(appId, 'Project-Participation of {publicName}', { publicName: memberData.personalPublicName }) }}
      </h2>
      <ul class="project-list">
        <ListItem v-for="participant in memberData.projectParticipation"
                  :key="participant.project.id"
                  :title="participant.project.name"
                  :bold="true">
          <template #details>
            <Actions class="project-details">
              <ActionButton icon="icon-info"
                            @click="requestProjectDetails(participant)">
                {{ t(appId, 'details') }}
              </ActionButton>
            </Actions>
          </template>
        </ListItem>
      </ul>
      <div v-if="debug" class="debug-container">
        <CheckboxRadioSwitch :checked.sync="debug">
          {{ t(appId, 'Enable Debug') }}
        </CheckboxRadioSwitch>
        <div class="debug">
          <div>{{ t(appId, 'DEBUG: all data') }}</div>
          <pre>{{ JSON.stringify(memberData, null, 2) }}</pre>
        </div>
      </div>
    </div>
  </Content>
</template>
<script>

import { appName as appId } from '../config.js'
import Vue from 'vue'
import Content from '@nextcloud/vue/dist/Components/Content'
import ListItem from '../components/ListItem'
import Actions from '@nextcloud/vue/dist/Components/Actions'
import ActionButton from '@nextcloud/vue/dist/Components/ActionButton'
import CheckboxRadioSwitch from '@nextcloud/vue/dist/Components/CheckboxRadioSwitch'
import '@nextcloud/dialogs/styles/toast.scss'
import { generateUrl } from '@nextcloud/router'
import { showError, TOAST_PERMANENT_TIMEOUT } from '@nextcloud/dialogs'
import axios from '@nextcloud/axios'

import ProjectDetails from './Projects/ProjectDetails'

import { useAppDataStore } from '../stores/appData.js'
import { useMemberDataStore } from '../stores/memberData.js'
import { mapWritableState } from 'pinia'

const viewName = 'Projects'

export default {
  name: viewName,
  components: {
    Content,
    CheckboxRadioSwitch,
    ListItem,
    ProjectDetails,
    Actions,
    ActionButton,
  },
  setup() {
    const memberData = useMemberDataStore()
    return { memberData }
  },
  data() {
    return {
      loading: true,
    }
  },
  computed: {
    ...mapWritableState(useAppDataStore, ['debug', 'memberRootFolder']),
  },
  async created() {
    await this.memberData.initialize()

    if (this.memberRootFolder === '') {
      try {
        let response = await axios.get(generateUrl('apps/' + appId + '/settings/app/memberRootFolder'), {})
        this.memberRootFolder = response.data.value
      } catch (e) {
        console.error('ERROR', e)
        let message = t(appId, 'reason unknown')
        if (e.response && e.response.data && e.response.data.message) {
          message = e.response.data.message
        }
        // Ignore for the time being
        if (this === false) {
          showError(t(appId, 'Could not fetch root-folder of member file space: {message}', { message }), { timeout: TOAST_PERMANENT_TIMEOUT })
        }
      }
    }

    this.loading = false
  },
  methods: {
    requestProjectDetails(participant) {
      this.$emit('view-details', {
        viewName,
        title: participant.project.name,
        props: {
          participant,
          memberRootFolder: this.memberRootFolder,
        }
      })
    },
  },
}
</script>
<style lang="scss" scoped>
.page-container {
  padding-left:0.5rem;
  &.loading {
    width:100%;
  }
}

.debug-container {
  width:100%;
  max-width:32rem;
  overflow:visible;
}

.project-list {
  min-width:32rem;
}
</style>
