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
  <Content :class="'app-' + appName" :app-name="'app-' + appName">
    <div>
      <h2>{{ t(appName, 'To be implemented: {view}', { view: t(appName, 'Instrument Insurances'), }) }}</h2>
      <div>{{ t(appName, 'This is currently only a placeholder for the future plan to make the personal data of the orchestra members available to just the respective orchestra member.') }}</div>
    </div>
  </Content>
</template>
<script>
import Content from '@nextcloud/vue/dist/Components/Content'
export default {
  name: 'InstrumentInsurances',
  components: {
    Content,
  },
  data() {
    return {
      loading: true,
    }
  },
  async mounted() {
    console.info('MOUNTED')
    this.loading = false
  },
  methods: {
  },
}
</script>
