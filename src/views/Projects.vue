<!--
 - @copyright Copyright (c) 2022-2024 Claus-Justus Heine <himself@claus-justus-heine.de>
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
  <div :class="{ 'icon-loading': loading, 'page-container': true, loading, }">
    <h2>
      {{ t(appId, 'Project-Participation of {publicName}', { publicName: memberData.personalPublicName }) }}
    </h2>
    <ul class="project-list">
      <ListItem v-for="participant in memberData.projectParticipation"
                :key="participant.project.id"
                :title="participant.project.name"
                :bold="true"
      >
        <template #details>
          <NcActions class="project-details">
            <NcActionButton icon="icon-info"
                            @click="requestProjectDetails(participant)"
            >
              {{ t(appId, 'details') }}
            </NcActionButton>
          </NcActions>
        </template>
      </ListItem>
    </ul>
    <DebugInfo :debug-data="memberData" />
  </div>
</template>
<script>

import { appName as appId } from '../config.js'
import ListItem from '../components/ListItem.vue'
import DebugInfo from '../components/DebugInfo.vue'
import {
  NcActions,
  NcActionButton,
} from '@nextcloud/vue'
import { generateUrl } from '@nextcloud/router'
import { showError, TOAST_PERMANENT_TIMEOUT } from '@nextcloud/dialogs'
import axios from '@nextcloud/axios'

import { useAppDataStore } from '../stores/appData.js'
import { useMemberDataStore } from '../stores/memberData.js'
import { mapWritableState } from 'pinia'

const viewName = 'Projects'

export default {
  name: viewName,
  components: {
    DebugInfo,
    ListItem,
    NcActionButton,
    NcActions,
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
    ...mapWritableState(useAppDataStore, ['memberRootFolder']),
  },
  async created() {
    await this.memberData.initialize()

    if (this.memberRootFolder === '') {
      try {
        const response = await axios.get(generateUrl('apps/' + appId + '/settings/app/memberRootFolder'), {})
        this.memberRootFolder = response.data.value
      } catch (e) {
        console.error('ERROR', e)
        let message = t(appId, 'reason unknown')
        if (e.response && e.response.data && e.response.data.messages) {
          message = e.response.data.messages
          if (Array.isArray(message)) {
            message = message.join(' ')
          }
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
        },
      })
    },
  },
}
</script>
<style lang="scss" scoped>
.page-container {
  padding-left:50px;
  padding-top:12px;
  min-height:100%;
  &.loading {
    width:100%;
    * {
      display:none;
    }
  }
}

.project-list {
  // min-width:32rem;
}
</style>
