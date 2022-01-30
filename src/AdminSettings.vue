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
  </SettingsSection>
</template>

<script>
import { appName } from './config.js'
import SettingsSection from '@nextcloud/vue/dist/Components/SettingsSection'
import SettingsInputText from './components/SettingsInputText'
import { generateUrl } from '@nextcloud/router'
import { showError, showSuccess } from '@nextcloud/dialogs'
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
    async saveTextInput(value, settingsKey) {
      console.info('ARGS', arguments)
      console.info('SAVE INPUTTEST', this.memberRootFolder)
      try {
        const response = await axios.post(generateUrl('apps/' + appName + '/settings/admin/' + settingsKey), { value })
        showSuccess(t(appName, 'Successfully set value for {settingsKey} to {value}', { settingsKey, value }))
        console.info('RESPONSE', response)
      } catch (e) {
        let message = t(appName, 'reason unknown')
        if (e.response && e.response.data && e.response.data.message) {
          message = e.response.data.message
          console.info('RESPONSE', e.response)
        }
        showError(t(appName, 'Could set value for {settingsKey} to {value}', { settingsKey, value }), { timeout: TOAST_PERMANENT_TIMEOUT })
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
