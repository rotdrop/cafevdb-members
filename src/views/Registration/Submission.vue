<!--
 - @copyright Copyright (c) 2023, 2024 Claus-Justus Heine <himself@claus-justus-heine.de>
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
  <div :class="{ 'icon-loading': loading, 'page-container': true, loading, 'submission-view': true, }">
    <h2>
      {{ t(appId, 'Summary and Submission') }}
    </h2>
    <div class="navigation flex flex-row flex-justify-full flex-center">
      <RouterButton :to="{ name: 'registrationProjectOptions', params: { projectName } }"
                    exact
                    icon="icon-history"
                    icon-position="left"
      >
        {{ t(appId, 'back') }}
      </RouterButton>
      <NcButton>
        {{ t(appId, 'Submit') }}
        <template #icon>
          <span class="icon-checkmark" />
        </template>
      </NcButton>
    </div>
  </div>
</template>

<script>
import RouterButton from '../../components/RouterButton.vue'

import { NcButton } from '@nextcloud/vue'

import mixinRegistrationData from '../../mixins/registrationData.js'
import { useMemberDataStore } from '../../stores/memberData.js'

export default {
  name: 'Submission',
  components: {
    NcButton,
    RouterButton,
  },
  mixins: [
    mixinRegistrationData,
  ],
  setup() {
    const registrationData = useMemberDataStore()
    return { registrationData }
  },
  data() {
    return {
      loading: true,
      readonly: true,
    }
  },
  computed: {},
  watch: {},
  async created() {
    if (!this.activeProject) {
      this.routerGoHome()
      return
    }
    await this.initializeRegistrationData()
    this.readonly = false
    this.loading = false
  },
  methods: {},
}
</script>
<style lang="scss" scoped>
.page-container {
  padding: 12px 0.5em 0 50px;
  min-height:100%;
  &.loading {
    width:100%;
    * {
      display:none;
    }
  }
}

.navigation {
  margin:0.5em 0;
}

.flex {
  display: flex;
  &.flex-row {
    flex-direction: row;
  }
  &.flex-justify-full {
    justify-content: space-between;
  }
  &.flex-center {
    align-items: center;
  }
}

.input-row {
  display:flex;
  flex-wrap:wrap;
  > * {
    flex: 1 0 40%;
    min-width:20em;
    &.input-type-number {
      flex: 1 0 5%;
      min-width:5em;
    }
    &.input-type-date {
      flex: 0 0 234px;
      width:234px;
      min-width:210px;
    }
  }
  ::v-deep .input-effect {
    margin-bottom:0;
  }
}
</style>
