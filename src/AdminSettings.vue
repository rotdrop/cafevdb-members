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
  <SettingsSection :title="t(appName, 'CAFeVDB Database Connector, Admin Settings')">
    <SettingsInputText
      v-model="memberRootFolder"
      :label="t(appName, 'Member-Data Root-Folder')"
      :hint="t(appName, 'Specify the root folder below which all member-data will be mounted.')"
      @update="saveTextInput(...arguments, 'memberRootFolder' /* @todo: how to pass v-model? */)" />
    <div v-if="showSyncProgress">
      <div class="sync-status">
        <span class="sync-text">{{ syncText }}</span>
        <button v-if="syncFinished"
                class="button primary sync-clear"
                :title="t(appName, 'Remove the status feedback from the last sync.')"
                @click="hideProgressFeedback()">
          {{ t(appName, 'Ok') }}
        </button>
        <span class="flex-spacer" />
        <span class="sync-counter">{{ syncCounter }}</span>
      </div>
      <ProgressBar :value="syncPercentage"
                   :error="syncError"
                   size="medium" />
    </div>
    <button v-else
            type="button"
            class="button primary"
            :title="t(appName, 'Synchronize the hierarchy of shared folders below {root} with the projects of the {managementApp}-orchestra-management app.', { root: memberRootFolder + '/', managementApp: 'cafevdb' })"
            @click="synchronizeFolders()">
      {{ t(appName, 'Synchronize Folder-Structure') }}
    </button>
  </SettingsSection>
</template>

<script>
import { appName } from './config.js'
import ProgressBar from '@nextcloud/vue/dist/Components/ProgressBar'
import SettingsSection from '@nextcloud/vue/dist/Components/SettingsSection'
import SettingsInputText from './components/SettingsInputText'
import { generateUrl } from '@nextcloud/router'
import { showError, showSuccess, showInfo, TOAST_PERMANENT_TIMEOUT } from '@nextcloud/dialogs'
import axios from '@nextcloud/axios'

export default {
  name: 'AdminSettings',
  components: {
    SettingsSection,
    SettingsInputText,
    ProgressBar
  },
  data() {
    return {
      memberRootFolder: '',
      memberFolderGroups: [],
      syncFailure: false,
      syncTotals: 0,
      syncDone: 0,
      synchronizing: false,
      syncLabel: '',
    }
  },
  created() {
    this.getData()
  },
  computed: {
    showSyncProgress() {
      return this.synchronizing
    },
    syncPercentage() {
      return this.syncTotals > 0 ? this.syncDone * 100 / this.syncTotals : 0
    },
    syncError() {
      return this.syncFailure
    },
    syncText() {
      return this.syncLabel
    },
    syncFinished() {
      return (this.syncDone > 0 && this.syncDone == this.syncTotals) || this.syncFailure
    }
  },
  methods: {
    async getData() {
      let response = await axios.get(generateUrl('apps/' + appName + '/settings/admin/memberRootFolder'), {})
      this.memberRootFolder = response.data.value
      console.info('ROOT FOLDER', this.memberRootFolder)
      response = await axios.get(generateUrl('apps/' + appName + '/settings/admin/memberFolderGroups'), {})
      this.memberFolderGroups = response.data.value
      console.info('FOLDER GROUPS', this.memberFolderGroups)
    },
    async saveTextInput(value, settingsKey, force) {
      const self = this
      console.info('ARGS', arguments)
      console.info('SAVE INPUTTEST', this.memberRootFolder)
      console.info('THIS', this)
      try {
        const response = await axios.post(generateUrl('apps/' + appName + '/settings/admin/' + settingsKey), { value, force })
        const responseData = response.data;
        if (responseData.status == 'unconfirmed') {
          OC.dialogs.confirm(
            responseData.feedback,
            t(appName, 'Confirmation Required'),
            function(answer) {
              if (answer) {
                self.saveTextInput(value, settingsKey, true);
              } else {
                showInfo(t(appName, 'Unconfirmed, reverting to old value.'))
                self.getData()
              }
            },
            true)
        } else {
          showSuccess(t(appName, 'Successfully set value for {settingsKey} to {value}', { settingsKey, value }))
        }
        console.info('RESPONSE', response)
      } catch (e) {
        let message = t(appName, 'reason unknown')
        if (e.response && e.response.data && e.response.data.message) {
          message = e.response.data.message
          console.info('RESPONSE', e.response)
        }
        showError(t(appName, 'Could not set value for {settingsKey} to {value}: {message}', { settingsKey, value, message }), { timeout: TOAST_PERMANENT_TIMEOUT })
        self.getData()
      }
    },
    async synchronizeFolders() {
      const self = this
      this.synchronizing = true
      this.syncTotals = this.memberFolderGroups.length
      this.syncDone = 0
      this.syncFailure = false
      let group = null
      for (group of this.memberFolderGroups) {
        console.info('GROUP', group)
        this.syncLabel = t(appName, 'Synchronizing for group {group}', { group: group.displayName })
        this.syncCounter = t(appName, '{current} of {totals}', { current: this.syncDone + 1, totals: this.syncTotals })
        try {
          const response = await axios.post(generateUrl('apps/' + appName + '/settings/admin/synchronize'), { value: group.gid })
        } catch (e) {
          let message = t(appName, 'reason unknown')
          if (e.response && e.response.data && e.response.data.message) {
            message = e.response.data.message
            console.info('RESPONSE', e.response)
          }
          showError(t(appName, 'Folder for {group} could not be created: {message}', { group: group.displayName, message }), { timeout: TOAST_PERMANENT_TIMEOUT })
          this.syncFailure = true
          break;
        }
        ++this.syncDone
      }
      this.syncLabel = this.syncFailure
        ? t(appName, 'Failed at group {group} after {numFolders} have been processed successfully, {remainingFolders} are remaining.',
            { group: group.displayName, numFolders: this.syncDone, remainingFolders: this.syncTotals - this.syncDone } )
        : t(appName, 'All done, folder structure for all {numFolders} folders is up to date.', { numFolders: this.syncTotals })
    },
    hideProgressFeedback() {
      this.synchronizing = false
      this.syncFailure = false
    },
  },
}
</script>
<style lang="scss" scoped>
  .settings-section {
    ::v-deep &__title {
      padding-left:60px;
      background-image:url('../img/cafevdbmembers.svg');
      background-repeat:no-repeat;
      background-origin:border-box;
      background-size:45px;
      background-position:left center;
      height:30px;
    }
    .sync-status {
      display:flex;
      flex-direction:row;
      align-items:center;
      width:100%;
      .flex-spacer {
        flex-grow:4;
        height:34px
      }
      button.sync-clear {
        margin-left:1ex;
      }
    }
  }
</style>
