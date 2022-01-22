<template>
  <SettingsSection :title="t('cafevdbmembers', 'CAFeVDB Database Connector')">
    <SettingsInputText :id="'test-input'" :label="t('cafevdbmembers', 'Test Input')">
    </SettingsInputText>
  </SettingsSection>
</template>

<script>
import SettingsSection from '@nextcloud/vue/dist/Components/SettingsSection'
import SettingsInputText from '@nextcloud/vue/dist/Components/SettingsInputText'
export default {
  name: 'PersonalSettings',
  components: {
    SettingsSection,
    SettingsInputText,
  },
}
</script>
<style scoped>
  div.foo {
    display:none;
  }
</style>
