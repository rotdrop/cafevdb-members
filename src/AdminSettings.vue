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
    <button class="button primary"
            :title="t(appName, 'Synchronize the hierarchy of shared folders below {root} with the projects of the {managementApp}-orchestra-management app.', { root: memberRootFolder + '/', managementApp: 'cafevdb' })"
            @click="synchronizeFolders()">
      {{ t(appName, 'Synchronize Folder-Structure') }}
    </button>
  </SettingsSection>
</template>

<script>
import { appName } from './config.js'
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
  },
  data() {
    return {
      memberRootFolder: '',
    }
  },
  created() {
    this.getData()
  },
  methods: {
    async getData() {
      const response = await axios.get(generateUrl('apps/' + appName + '/settings/admin/memberRootFolder'), {})
      console.info('RESPONSE', response)
      this.memberRootFolder = response.data.value
      console.info('VALUE', this.memberRootFolder)
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
      try {
        const response = await axios.post(generateUrl('apps/' + appName + '/settings/admin/synchronize'))
        showSuccess(t(appName, 'Successfully synchronized the shared-folder structure with the orchestra projects'))
      } catch (e) {
        let message = t(appName, 'reason unknown')
        if (e.response && e.response.data && e.response.data.message) {
          message = e.response.data.message
          console.info('RESPONSE', e.response)
        }
        showError(t(appName, 'Folder-structure could not be synchronized: {message}', { message }), { timeout: TOAST_PERMANENT_TIMEOUT })
      }
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
  }
</style>
