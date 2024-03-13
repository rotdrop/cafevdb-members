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
 -->
<template>
  <NcSettingsSection :name="t(appName, 'CAFeVDB Database Connector, Personal Settings')">
    <TextField :id="'test-input'"
               :value.sync="inputTest"
               :label="t(appName, 'Test Input')"
               :hint="t(appName, 'Test Hint')"
               @submit="saveInputTest"
    />
  </NcSettingsSection>
</template>

<script>
import { appName } from './config.js'
import {
  NcSettingsSection,
} from '@nextcloud/vue'
import TextField from '@rotdrop/nextcloud-vue-components/lib/components/TextFieldWithSubmitButton.vue'
import { generateUrl } from '@nextcloud/router'
import axios from '@nextcloud/axios'

export default {
  name: 'PersonalSettings',
  components: {
    NcSettingsSection,
    TextField,
  },
  data() {
    return {
      inputTest: '',
    }
  },
  created() {
    this.getData()
  },
  methods: {
    async getData() {
      const response = await axios.get(generateUrl('apps/' + appName + '/settings/personal/inputTest'), {})
      console.info('RESPONSE', response)
      this.inputTest = response.data.value
      console.info('VALUE', this.inputTest)
    },
    async saveInputTest() {
      console.info('SAVE INPUTTEST', this.inputTest)
      const response = await axios.post(generateUrl('apps/' + appName + '/settings/personal/inputTest'), { value: this.inputTest })
      console.info('RESPONSE', response)
    },
  },
}
</script>
<style lang="scss" scoped>
.settings-section {
  ::v-deep &__name {
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
