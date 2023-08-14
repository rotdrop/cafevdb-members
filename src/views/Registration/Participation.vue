<script>
/**
 * @copyright Copyright (c) 2023 Claus-Justus Heine <himself@claus-justus-heine.de>
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
  <div :class="{ 'icon-loading': loading, 'page-container': true, loading, 'participation-view': true, }">
    <h2>
      {{ t(appId, 'Instrumentation, Rehearsals and Concerts for "{name}"', activeProject) }}
    </h2>
    <h3>
      {{ t(appId, 'Please configure the instrument or the role you intend to play in this project.') }}
    </h3>
    <div class="input-row">
      <InputText v-model="registrationData.selectedInstruments"
                 type="multiselect"
                 :label="t(appId, 'All my Instruments or Roles')"
                 :options="instruments"
                 group-values="instruments"
                 group-label="family"
                 track-by="id"
                 option-label="name"
                 :auto-limit="true"
                 :tag-width="100"
                 :readonly="readonly"
                 :multiple="true"
                 :placeholder="t(appId, 'e.g. double bass')"
                 :required="true"
      />
    </div>
    <div class="input-row">
      <InputText v-model="registrationProject.instruments"
                 type="multiselect"
                 :label="t(appId, 'Project Instruments or Roles')"
                 :options="personalProjectInstrumentOptions"
                 track-by="id"
                 option-label="name"
                 :auto-limit="true"
                 :tag-width="100"
                 :readonly="readonly"
                 :multiple="true"
                 :placeholder="t(appId, 'e.g. double bass')"
                 :required="true"
      />
    </div>
    <div v-if="personalProjectInstrumentOptions.length === 0">
      {{ t(appId, 'You do not seem to play any instrument configured for the project: {instruments}.', { instruments: projectInstrumentsText }) }}
    </div>
    <div class="event-list">
      <h3>
        {{ t(appId, 'Timetable') }}
      </h3>
      <CheckboxRadioSwitch :checked.sync="noAbsenceCheck"
                           :disabled="!noAbsence"
      >
        {{ t(appId, 'I will participate in all events and not miss a single one!') }}
      </CheckboxRadioSwitch>
      <div v-if="!noAbsenceCheck"
           class="absence-instructions"
      >
        {{ t(appId, 'Please open the dots menu for each particular event you cannot participate in, toggle the contained checkbox and give a short explanation!') }}
      </div>
      <div v-if="!noAbsenceCheck"
           class="absence-instructions"
      >
        {{ t(appId, 'Please understand that applications of people without or with less absence are preferred.') }}
      </div>
      <ul class="event-list">
        <ListItem v-for="event in activeProject.projectEvents"
                  :key="event.id"
                  :title="calendarDateTime(event.calendarObject)"
                  :details="event.calendarObject.summary"
                  :force-display-actions="true"
                  class="calendar-event"
        >
          <template v-if="event.calendarObject.location" #subtitle>
            {{ event.calendarObject.location }}
          </template>
          <template v-if="registrationProject.absence[event.id]" #indicator>
            <AbsenceIndicator :size="24" fill-color="#ff0000" />
          </template>
          <template v-if="!noAbsenceCheck && event.absenceField > 0" #actions>
            <ActionCheckbox value="absent"
                            :checked="registrationProject.absence[event.id]"
                            @check="registrationProject.absence[event.id] = true"
                            @uncheck="registrationProject.absence[event.id] = false"
            >
              {{ t(appId, 'I cannot participate') }}
            </ActionCheckbox>
            <ActionTextEditable v-if="registrationProject.absence[event.id]"
                                :value="registrationProject.absenceReasons[event.id]"
                                :name="t(appId, '... because ...')"
                                required
                                @submit="registrationProject.absenceReasons[event.id] = $event.target.getElementsByTagName('textarea')[0].value"
            >
              <template #icon>
                <Pencil :size="20" />
              </template>
            </ActionTextEditable>
          </template>
          <template #extra>
            <div class="event-description">
              {{ event.calendarObject.description }}
            </div>
          </template>
        </ListItem>
      </ul>
    </div>
    <div class="navigation flex flex-row flex-justify-full">
      <RouterButton :to="{ name: 'registrationPersonalProfile', params: { projectName } }"
                    exact
                    icon="icon-history"
                    icon-position="left"
      >
        {{ t(appId, 'back') }}
      </RouterButton>
      <RouterButton :to="{ name: 'registrationProjectOptions', params: { projectName } }"
                    exact
                    icon="icon-confirm"
                    icon-position="right"
      >
        {{ t(appId, 'next') }}
      </RouterButton>
    </div>
  </div>
</template>
<script>
import Pencil from 'vue-material-design-icons/Pencil.vue'
import AbsenceIndicator from 'vue-material-design-icons/AlertOctagon'
import { appName } from '../../config.js'
import InputText from '../../components/InputText'
import DebugInfo from '../../components/DebugInfo'
import RouterButton from '../../components/RouterButton'

import { set as vueSet } from 'vue'
import CheckboxRadioSwitch from '@nextcloud/vue/dist/Components/NcCheckboxRadioSwitch'
import ActionCheckbox from '@nextcloud/vue/dist/Components/NcActionCheckbox'
import ActionTextEditable from '@nextcloud/vue/dist/Components/NcActionTextEditable'
import Highlight from '@nextcloud/vue/dist/Components/NcHighlight'
import ListItem from '@nextcloud/vue/dist/Components/NcListItem'

import mixinRegistrationData from '../../mixins/registrationData.js'
import { useMemberDataStore } from '../../stores/memberData.js'

export default {
  name: 'Participation',
  components: {
    AbsenceIndicator,
    ActionCheckbox,
    ActionTextEditable,
    CheckboxRadioSwitch,
    DebugInfo,
    Highlight,
    InputText,
    ListItem,
    Pencil,
    RouterButton,
  },
  setup() {
    const registrationData = useMemberDataStore()
    return { registrationData }
  },
  mixins: [
    mixinRegistrationData,
  ],
  data() {
    return {
      loading: true,
      readonly: true,
      noAbsenceCheck: true,
    }
  },
  async created() {
    if (!this.activeProject) {
      this.routerGoHome()
      return
    }
    await this.initializeRegistrationData()
    this.readonly = false
    this.loading = false
    this.noAbsenceCheck = this.noAbsence
  },
  computed: {},
  watch: {},
  methods: {
    info() {
      console.info(...arguments)
    },
    calendarDateTime(calendarEvent) {
      if (calendarEvent.allday) {
        const end = new Date(calendarEvent.end)
        // end dates of whole day event always point to midnight of the day AFTER the event
        end.setDate(end.getDate() - 1)
        if (end <= calendarEvent.start) {
          return this.localeDate(calendarEvent.start)
        } else {
          return this.localeDate(calendarEvent.start) + ' - ' + this.localeDate(end)
        }
      } else {
        const startDate = this.localeDate(calendarEvent.start)
        const endDate = this.localeDate(calendarEvent.end)
        const startTime = this.localeTime(calendarEvent.start)
        const endTime = this.localeTime(calendarEvent.end)
        if (startDate === endDate) {
          return startDate + ', ' + startTime + ' - ' + endTime
        } else {
          return startDate + ', ' + startTime + ' - ' + endDate + ', ' + endTime
        }
      }
    },
    localeDateTime(dateTime) {
      const options = {
        timeStyle: 'short',
        dateStyle: 'medium',
      }
      return dateTime.toLocaleString(undefined, options)
    },
    localeTime(dateTime) {
      const options = {
        timeStyle: 'short',
      }
      return dateTime.toLocaleTimeString(undefined, options)
    },
    localeDate(dateTime) {
      const options = {
        dateStyle: 'medium',
      }
      return dateTime.toLocaleDateString(undefined, options)
    },
  },
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
