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
  <div :class="{ 'icon-loading': loading, 'page-container': true, loading, 'participation-view': true, }">
    <h2>
      {{ t(appName, 'Instrumentation, Rehearsals and Concerts for "{name}"', { name: activeProject?.name || '' }) }}
    </h2>
    <h3>
      {{ t(appName, 'Please configure the instrument or the role you intend to play in this project.') }}
    </h3>
    <div v-if="!loading && registrationProject">
      <div class="input-row">
        <InputText v-model="registrationData.selectedInstruments"
                   type="multiselect"
                   :label="t(appName, 'All my Instruments or Roles')"
                   :options="instruments"
                   group-values="instruments"
                   group-label="family"
                   track-by="id"
                   option-label="name"
                   :auto-limit="true"
                   :tag-width="100"
                   :readonly="readonly"
                   :multiple="true"
                   :placeholder="t(appName, 'e.g. double bass')"
                   :required="true"
        />
      </div>
      <div class="input-row">
        <InputText v-model="registrationProject.instruments"
                   type="multiselect"
                   :label="t(appName, 'Project Instruments or Roles')"
                   :options="personalProjectInstrumentOptions"
                   track-by="id"
                   option-label="name"
                   :auto-limit="true"
                   :tag-width="100"
                   :readonly="readonly"
                   :multiple="true"
                   :placeholder="t(appName, 'e.g. double bass')"
                   :required="true"
        />
      </div>
      <div v-if="personalProjectInstrumentOptions.length === 0">
        {{ t(appName, 'You do not seem to play any instrument configured for the project: {instruments}.', {
          instruments: projectInstrumentsText }) }}
      </div>
      <div class="event-list">
        <h3>
          {{ t(appName, 'Timetable') }}
        </h3>
        <NcCheckboxRadioSwitch :checked.sync="noAbsenceCheck" :disabled="!noAbsence">
          {{ t(appName, 'I will participate in all events and not miss a single one!') }}
        </NcCheckboxRadioSwitch>
        <div v-if="!noAbsenceCheck"
             class="absence-instructions"
        >
          {{ t(appName, 'Please open the dots menu for each particular event you cannot participate in, toggle the contained checkbox and give a short explanation!') }}
        </div>
        <div v-if="!noAbsenceCheck" class="absence-instructions">
          {{ t(appName, 'Please understand that applications of people without or with less absence are preferred.') }}
        </div>
        <ul class="event-list">
          <NcListItem v-for="event in activeProject?.projectEvents"
                      :key="event.id"
                      :title="calendarDateTime(event.calendarObject)"
                      :details="event.calendarObject.summary"
                      :force-display-actions="true"
                      class="calendar-event"
          >
            <template v-if="event.calendarObject.location" #subname>
              {{ event.calendarObject.location }}
            </template>
            <template v-if="registrationProject?.absence[event.id]" #indicator>
              <AbsenceIndicator :size="24" fill-color="#ff0000" />
            </template>
            <template v-if="!noAbsenceCheck && event.absenceField > 0" #actions>
              <NcActionCheckbox value="absent"
                                :checked="registrationProject?.absence[event.id] || false"
                                @check="registrationProject.absence[event.id] = true"
                                @uncheck="registrationProject.absence[event.id] = false"
              >
                {{ t(appName, 'I cannot participate') }}
              </NcActionCheckbox>
              <NcActionTextEditable v-if="registrationProject.absence[event.id]"
                                    :value="registrationProject.absenceReasons[event.id]"
                                    :name="t(appName, '... because ...')"
                                    required
                                    @submit="registrationProject.absenceReasons[event.id] = $event.target.getElementsByTagName('textarea')[0].value"
              >
                <template #icon>
                  <Pencil :size="20" />
                </template>
              </NcActionTextEditable>
            </template>
            <template #extra>
              <div class="event-description">
                {{ event.calendarObject.description }}
              </div>
            </template>
          </NcListItem>
        </ul>
      </div>
    </div>
    <div class="navigation flex flex-row flex-justify-full">
      <RouterButton :to="{ name: 'registrationPersonalProfile', params: { projectName } }"
                    exact
                    icon="icon-history"
                    icon-position="left"
      >
        {{ t(appName, 'back') }}
      </RouterButton>
      <RouterButton :to="{ name: 'registrationProjectOptions', params: { projectName } }"
                    exact
                    icon="icon-confirm"
                    icon-position="right"
      >
        {{ t(appName, 'next') }}
      </RouterButton>
    </div>
  </div>
</template>
<script setup lang="ts">
import { appName } from '../../config.ts'
import {
  translate as t,
  getCanonicalLocale,
} from '@nextcloud/l10n'
import Pencil from 'vue-material-design-icons/Pencil.vue'
import AbsenceIndicator from 'vue-material-design-icons/AlertOctagon.vue'
import InputText from '../../components/InputText.vue'
import RouterButton from '../../components/RouterButton.vue'
import {
  NcCheckboxRadioSwitch,
  NcActionCheckbox,
  NcActionTextEditable,
  NcListItem,
} from '@nextcloud/vue'
import { useMemberDataStore } from '../../stores/memberData.ts'
import { useAppDataStore } from '../../stores/appData'
import {
  onBeforeMount,
  ref,
} from 'vue'
import { storeToRefs } from 'pinia'
import type { CalendarObject } from '../../stores/appData.ts'

const registrationData = useMemberDataStore()
const appData = useAppDataStore()

const loading = ref(true)
const readonly = ref(true)
const noAbsenceCheck = ref(true)

const locale = getCanonicalLocale()

const {
  activeProject,
  instruments,
  projectInstrumentsText,
  projectName,
} = storeToRefs(appData)
const {
  noAbsence,
  personalProjectInstrumentOptions,
  registrationProject,
} = storeToRefs(registrationData)

onBeforeMount(async () => {
  if (!activeProject.value) {
    appData.gotoRegistratzionHome()
    return
  }
  await registrationData.initializeRegistrationData()
  readonly.value = false
  loading.value = false
  noAbsenceCheck.value = noAbsence.value
})

// const localeDateTime = (dateTime: Date) => {
//   const options = {
//     timeStyle: 'short',
//     dateStyle: 'medium',
//   }
//   return dateTime.toLocaleString(locale, options)
// }

const localeTime = (dateTime: Date) => {
  const options: Intl.DateTimeFormatOptions = {
    timeStyle: 'short',
  }
  return dateTime.toLocaleTimeString(locale, options)
}

const localeDate = (dateTime: Date) => {
  const options: Intl.DateTimeFormatOptions = {
    dateStyle: 'medium',
  }
  return dateTime.toLocaleDateString(locale, options)
}

const calendarDateTime = (calendarEvent: CalendarObject) => {
  if (calendarEvent.allday) {
    const end = new Date(calendarEvent.endDateTime)
    // end dates of whole day event always point to 00:00 of the day AFTER the event
    end.setDate(end.getDate() - 1)
    if (end <= calendarEvent.startDateTime) {
      return localeDate(calendarEvent.startDateTime)
    } else {
      return localeDate(calendarEvent.startDateTime) + ' - ' + localeDate(end)
    }
  } else {
    const startDate = localeDate(calendarEvent.startDateTime)
    const endDate = localeDate(calendarEvent.endDateTime)
    const startTime = localeTime(calendarEvent.startDateTime)
    const endTime = localeTime(calendarEvent.endDateTime)
    if (startDate === endDate) {
      return startDate + ', ' + startTime + ' - ' + endTime
    } else {
      return startDate + ', ' + startTime + ' - ' + endDate + ', ' + endTime
    }
  }
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

.WIP {
  color:red;
  font-weight:bold;
}

.event-description {
  padding-left: 1ex;
}
</style>
