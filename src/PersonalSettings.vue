<template>
  <SettingsSection :title="t('cafevdbmembers', 'CAFeVDB Database Connector, Personal Settings')">
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
import { generateUrl } from '@nextcloud/router'
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
      const response = await axios.get(generateUrl('apps/cafevdbmembers/settings/personal/inputTest'), {})
      console.info('RESPONSE', response)
      this.inputTest = response.data.value
      console.info('VALUE', this.inputTest)
    },
    async saveInputTest() {
      console.info('SAVE INPUTTEST', this.inputTest)
      const response = await axios.post(generateUrl('apps/cafevdbmembers/settings/personal/inputTest'), { value: this.inputTest })
      console.info('RESPONSE', response)
    },
  },
}
</script>
<style scoped>
  div.foo {
    display:none;
  }
</style>
