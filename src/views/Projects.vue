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
          <template #subtitle>
            <ul class="project-details">
              <ListItem v-if="participant.projectInstruments.length > 1"
                        :title="t(appId, 'Instruments')">
                <template #subtitle>
                  <ul class="project-instruments">
                    <ListItem v-for="instrument in participant.projectInstruments"
                              :key="instrument.id"
                              :title="instrument.name"
                              :details="[instrument.voice > 0 ? t(appId, 'voice {voice}', { voice: instrument.voice }) : '', instrument.sectionLeader ? t(appId, 'section leader') : ''].filter(x => x.length > 0).join(', ')" />
                  </ul>
                </template>
              </ListItem>
              <ListItem v-else-if="participant.projectInstruments.length == 1"
                        :title="participant.projectInstruments[0].name"
                        :details="[participant.projectInstruments[0].voice > 0 ? t(appId, 'voice {voice}', { voice: participant.projectInstruments[0].voice }) : '', participant.projectInstruments[0].sectionLeader ? t(appId, 'section leader') : ''].filter(x => x.length > 0).join(', ')" />
              <li class="photos-link list-item__wrapper">
                <a class="list-item" :target="md5(projectPathUrl(participant.project))" :href="projectPathUrl(participant.project)">
                  <div class="list-item-content">
                    <span class="label">{{ t(appId, 'Photos') }}</span>
                    <span class="link">{{ projectPath(participant.project) }}</span>
                  </div>
                </a>
              </li>
            </ul>
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
import ListItem from '@nextcloud/vue/dist/Components/ListItem'
import CheckboxRadioSwitch from '@nextcloud/vue/dist/Components/CheckboxRadioSwitch'
import '@nextcloud/dialogs/styles/toast.scss'
import { generateUrl } from '@nextcloud/router'
import { showError, TOAST_PERMANENT_TIMEOUT } from '@nextcloud/dialogs'
import axios from '@nextcloud/axios'
import md5 from 'blueimp-md5'

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
  },
  mixins: [
    {
      methods: {
        md5,
      }
    },
  ],
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
    if (!this.memberData.initialized.loaded) {
      try {
        const response = await axios.get(generateUrl('/apps/' + appId + '/member'))
        for (const [key, value] of Object.entries(response.data)) {
          Vue.set(this.memberData, key, value)
        }
        this.memberData.initialized.loaded = true
      } catch (e) {
        console.error('ERROR', e)
        let message = t(appId, 'reason unknown')
        if (e.response && e.response.data && e.response.data.message) {
          message = e.response.data.message
        }
        // Ignore for the time being
        if (this === false) {
          showError(t(appId, 'Could not fetch musician(s): {message}', { message }), { timeout: TOAST_PERMANENT_TIMEOUT })
        }
      }
    }
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
    projectPath(project) {
      const components = [
        this.memberRootFolder,
      ]
      if (project.type === 'temporary') {
        components.push(t(appId, 'projects'))
        components.push(project.year)
      }
      components.push(project.name);
      return '/' + components.join('/')
    },
    projectPathUrl(project) {
      const path = this.projectPath(project)
      return generateUrl('apps/files') + '?dir=' + path
    }
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

::v-deep {
  .list-item {
    padding-right: 0;
    ul .list-item {
      padding-top:2px;
      padding-bottom:2px;
    }
  }

  .line-two__subtitle {
    padding-right:0;
  }

  .line-one--bold {
    &.line-one {
      .line-one__details {
        font-weight:inherit;
      }
    }
    &.line-two {
      font-weight: normal;
    }
  }
}

.photos-link.list-item__wrapper {
  .list-item {
    padding: 2px 0 2px 8px;
    .list-item-content {
      display: flex;
      align-items: center;
      justify-content: space-between;
      white-space: nowrap;
      margin: 0 auto;
      .label {
        flex-grow:1;
      }
      .link {
        color: CornFlowerBlue;
        text-decoration: underline;
        font-weight:normal;
        margin: 0 8px;
        * {
          color:inherit;
        }
      }
    }
  }
}
</style>
