<template>
  <SettingsSection :title="t('cafevdbmembers', 'CAFeVDB Database Connector')">
    <SettingsInputText
      :id="'test-input'"
      v-model="inputTest"
      :label="t('cafevdbmembers', 'Test Input')"
      :hint="t('cafevdbmembers', 'Test Hint')"
      @update="saveInputTest" />
  </SettingsSection>
</template>

<script>
import SettingsSection from '@nextcloud/vue/dist/Components/SettingsSection'
import SettingsInputText from './components/SettingsInputText'
import { generateOcsUrl } from '@nextcloud/router'
import axios from '@nextcloud/axios'

export default {
  name: 'PersonalSettings',
  components: {
    SettingsSection,
    SettingsInputText,
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
      const response = await axios.get(generateOcsUrl('apps/provisioning_api/api/v1/config/apps/cafevdbmembers/input_test'), {})
      console.info('RESPONSE', response)
      this.inputTest = response.data.ocs.data.data
      console.info('VALUE', this.inputTest)
    },
    saveInputTest() {
      console.info('SAVE INPUTTEST', this.inputTest)
      OCP.AppConfig.setValue('cafevdbmembers', 'input_test', this.inputTest)
    },
  },
}
</script>
<style scoped>
  div.foo {
    display:none;
  }
</style>
