<!--
 - @copyright Copyright (c) 2023, 2024, 2025 Claus-Justus Heine <himself@claus-justus-heine.de>
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
  <div :class="{ 'icon-loading': loading, 'page-container': true, loading, 'project-options-view': true, }">
    <h2>
      {{ t(appName, 'Project Fees and Options') }}
    </h2>
    <div v-if="!loading && registrationProject">
      <ul class="project-options">
        <NcListItem v-for="field of projectOptions"
                    :key="field.id"
                    :name="field.name"
        >
          <template #extra>
            <!-- eslint-disable-next-line vue/no-v-html -->
            <div class="option-helptext" v-html="field.tooltip" />
            <NcTextArea v-if="field.dataType === 'html' && field.multiplicity === 'simple'"
                        v-model="registrationProject.options[field.id]"
                        label-outside
            />
            <NcSelect v-else-if="field.multiplicity === 'multiple' || field.multiplicity === 'parallel'"
                      :ref="(el) => addOptionSelectRef(el, field.id)"
                      v-model="registrationProject.options[field.id]"
                      label="label"
                      :multiple="field.multiplicity === 'parallel'"
                      :options="Object.values(field.dataOptions)"
                      :reduce="reduceFieldOptions"
                      label-outside
            >
              <template #option="option">
                <NcEllipsisedOption :name="fieldOptionLabel(option, field)"
                                    :search="optionSelects?.[field.id]?.search || ''"
                />
              </template>
              <template #selected-option="option">
                <NcEllipsisedOption :name="fieldOptionLabel(option, field)"
                                    :search="optionSelects?.[field.id]?.search || ''"
                />
              </template>
            </NcSelect>
            <pre>
              {{ JSON.stringify(field, undefined, 2) }}
            </pre>
          </template>
        </NcListItem>
      </ul>
    </div>
    <pre>
       {{ JSON.stringify(registrationProject, undefined, 2) }}
    </pre>
    <div class="navigation flex flex-row flex-justify-full">
      <RouterButton :to="{ name: 'registrationParticipation', params: { projectName } }"
                    exact
                    icon="icon-history"
                    icon-position="left"
      >
        {{ t(appName, 'back') }}
      </RouterButton>
      <RouterButton :to="{ name: 'registrationSubmission', params: { projectName } }"
                    exact
                    icon="icon-confirm"
                    icon-position="right"
      >
        {{ t(appName, 'Summary and Submission') }}
      </RouterButton>
    </div>
  </div>
</template>
<script setup lang="ts">
import { appName } from '../../config.ts'
import {
  getCanonicalLocale,
  translate as t,
} from '@nextcloud/l10n'
import {
  NcListItem,
  NcSelect,
  NcTextArea,
  NcEllipsisedOption,
} from '@nextcloud/vue'
import RouterButton from '../../components/RouterButton.vue'
import {
  ProjectParticipantFieldDataType,
  useMemberDataStore,
  type ProjectParticipantFieldDataOption,
  type ProjectParticipantField,
} from '../../stores/memberData.ts'
import { useAppDataStore } from '../../stores/appData.ts'
import {
  computed,
  onMounted,
  ref,
} from 'vue'
import { storeToRefs } from 'pinia'

const appData = useAppDataStore()
const registrationData = useMemberDataStore()

const loading = ref(true)
const readonly = ref(true)

const optionSelects: Record<number, typeof NcSelect> = {}

const {
  activeProject,
  projectName,
} = storeToRefs(appData)
const {
  registrationProject,
} = storeToRefs(registrationData)

const projectOptions = computed(
  () => Object.values(activeProject.value?.participantFields || []).filter((fieldData) => !fieldData.deleted && !(fieldData.absenceEvent > 0)),
)

const reduceFieldOptions = (option: ProjectParticipantFieldDataOption) => option.key

const addOptionSelectRef = (el: typeof NcSelect, fieldId: number) => {
  optionSelects[fieldId] = el
}

const formatMoneyValue = (
  value: number,
  currencyCode: string = appData.initialState.currencyCode,
  locale: string = getCanonicalLocale(),
) => {
  return new Intl.NumberFormat(
    locale, {
      style: 'currency',
      currency: currencyCode,
    })
    .format(value)
}

// @todo
// - due date
// - mention deposit and due-date
// - add debit-note info
const fieldOptionLabel = (option: ProjectParticipantFieldDataOption, field: ProjectParticipantField) => {
  switch (field.dataType) {
  case ProjectParticipantFieldDataType.LIABILITIES:
  case ProjectParticipantFieldDataType.RECEIVABLES:
    return option.label + ' -- ' + formatMoneyValue(+option.data)
  default:
    return option.label
  }
}

onMounted(async () => {
  if (!activeProject.value) {
    appData.gotoRegistrationHome()
    return
  }
  await registrationData.initializeRegistrationData()
  readonly.value = false
  loading.value = false
})
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

.project-options {
  ::v-deep .list-item {
    flex-wrap: wrap;
    .list-item__extra {
      width: 100%;
    }
  }
  .option-helptext {
    padding-left: 1ex;
    font-style: italic;
    color: var(--color-text-maxcontrast);
  }
}
</style>
