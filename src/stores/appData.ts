/**
 * @copyright Copyright (c) 2022, 2023, 2025 Claus-Justus Heine <himself@claus-justus-heine.de>
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

import { appName } from '../config.ts'
import { defineStore } from 'pinia'
import { translate as t } from '@nextcloud/l10n'
import { getInitialState } from '../toolkit/services/InitialStateService.js'

export interface InstrumentFamily {
  family: string,
}

export interface Instrument {
  sortOrder: number,
  families: InstrumentFamily[],
}

export interface ProjectEvent {
  calendarObject: {
    start: string,
    startDateTime: Date,
    end: string,
    endDateTime: Date,
  }
}

export interface Project {
  id: number,
  name: string,
  projectEvents: ProjectEvent[],
}

export interface InstrumentGroup {
  family: string,
  sortOrder: number,
  instruments: Instrument[],
}

const projects = getInitialState('projects', []) as Project[]
let activeProject = getInitialState('activeProject', null)
const instruments = getInitialState('instruments', []) as Instrument[]
const countries = getInitialState('countries', null)
const displayLocale = getInitialState('displayLocale', null)

const initialState = getInitialState()

// of course, total over-kill ... just playing around
export const useAppDataStore = defineStore('app-data', {
  state: () => {
    // convert the flat array of instruments to grouped options for Vue Multiselect
    const groupedInstruments: Record<string, InstrumentGroup> = {}
    for (const instrument of instruments) {
      const familyTag = instrument.families.map(family => family.family).join(', ')
      const optionGroup = groupedInstruments[familyTag] || { family: familyTag, sortOrder: 0, instruments: [] }
      optionGroup.instruments.push(instrument)
      optionGroup.sortOrder += instrument.sortOrder
      groupedInstruments[familyTag] = optionGroup
    }

    if (activeProject === null && projects) {
      activeProject = 0
    }

    for (const project of projects) {
      for (const event of project.projectEvents) {
        event.calendarObject.startDateTime = new Date(event.calendarObject.start)
        event.calendarObject.endDateTime = new Date(event.calendarObject.end)
      }
      project.projectEvents.sort((a, b) => a.calendarObject.startDateTime.getTime() - b.calendarObject.startDateTime.getTime())
    }

    console.info('PROJECTS', projects)

    return {
      orchestraName: initialState?.orchestraName || t(appName, '[UNKNOWN]'),
      projects,
      activeProject: projects && activeProject >= 0 ? projects[activeProject] : null,
      instruments: Object.values(groupedInstruments).sort((a, b) => a.sortOrder - b.sortOrder),
      countries,
      displayLocale,
      memberRootFolder: '',
      debug: false,
    }
  },
})
