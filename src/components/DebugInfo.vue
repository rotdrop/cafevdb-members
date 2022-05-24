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
  <div v-if="debug" class="debug-container">
    <CheckboxRadioSwitch :checked.sync="debug">
      {{ t(appId, 'Enable Debug') }}
    </CheckboxRadioSwitch>
    <div class="debug">
      <div>{{ t(appId, 'DEBUG: all data') }}</div>
      <pre>{{ stringify(debugData) }}</pre>
    </div>
  </div>
</template>

<script>

import { appName as appId } from '../config.js'
import CheckboxRadioSwitch from '@nextcloud/vue/dist/Components/CheckboxRadioSwitch'

import { useAppDataStore } from '../stores/appData.js'
import { mapWritableState } from 'pinia'

export default {
  name: 'DebugInfo',
  components: {
    CheckboxRadioSwitch,
  },
  props: {
    debugData: { type: Object, required: true, default: {} },
  },
  computed: {
    ...mapWritableState(useAppDataStore, ['debug']),
  },
  methods: {
    stringify(data) {
      console.info('DATA', data)
      try {
        const getCircularReplacer = () => {
          const seen = new WeakSet
          return (key, value) => {
            if (key.startsWith('$') || key.startsWith('_')) {
              return
            }
            if (typeof value === "object" && value !== null) {
              if (seen.has(value)) {
                return
              }
              seen.add(value)
            }
            return value
          }
        }
        return JSON.stringify(data, getCircularReplacer(), 2)
      } catch (e) {
        console.error('ERROR', e)
        return ''
      }
    },
  },
}
</script>

<style lang="scss" scoped>
.debug-container {
  width:100%;
  max-width:32rem;
  overflow:visible;
}
</style>
