/**
 * @copyright Copyright (c) 2022-2025 Claus-Justus Heine <himself@claus-justus-heine.de>
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

import { defineStore } from 'pinia'

import { appName as appId } from '../config.ts'
import { translate as t } from '@nextcloud/l10n'
import { set as vueSet } from 'vue'
import {
  generateUrl as generateAppUrl,
  generateOcsUrl as generateAppOcsUrl,
} from '../toolkit/util/generate-url.js'
import { getCurrentUser } from '@nextcloud/auth'
import { showError, TOAST_PERMANENT_TIMEOUT } from '@nextcloud/dialogs'
import axios from '@nextcloud/axios'
import { isAxiosErrorResponse } from '../toolkit/types/axios-type-guards.ts'
import type { Project, Instrument } from './appData.ts'
import {
  computed,
  reactive,
  ref,
  watch,
} from 'vue'
import { deepCopy } from 'walkjs'
import { useAppDataStore } from './appData.ts'

export interface SepaDebitMandate {
  sequence: number,
  mandateReference: string,
  modified?: string,
  deleted?: string,
  lastUsedDate?: { date: string },
  mandateDate: { date: string },
}

export interface SepaBankAccount {
  sequence: number,
  iban: string,
  deleted: null|string,
  created: string,
  modified: string,
  bankAccountOwner: string,
  sepaDebitMandates: SepaDebitMandate[],
  numActiveDebitMandates: number,
  numDeletedDebitMandates: number,
}

export interface ProjectParticipantFieldData {
  optionKey: string,
  dataOption: {
    label: string,
    data: string,
  }
}

export interface ProjectParticipantField {
  name: string,
  untranslatedName: string,
  fieldData: Record<string, ProjectParticipantFieldData>,
}

export interface ProjectInstrument {
  id: number,
  name: string,
  voice: number,
  sectionLeader: boolean,
}

export interface ProjectParticipant {
  project: Project,
  participantFields: Record<number, ProjectParticipantField>,
  projectInstruments: ProjectInstrument[],
}

export interface InstrumentInsurance {
  id: number,
  deleted?: string,
  isHolder: boolean,
  isDebitor: boolean,
  isOwner: boolean,
  object: string,
  insuranceAmount: number,
  insuranceRate: {
    rate: number,
    broker: {
      shortName: string,
    },
    geographicalScope: string,
    dueDate: string,
  }
  manufacturer: string,
  yearOfConstruction: string,
  startOfInsurance: string,
}

export interface Receivable extends ProjectParticipantFieldData {
  supportingDocumentId?: number,
}

export interface InsuranceDetails {
  self: InstrumentInsurance[],
  forOthers: InstrumentInsurance[],
  byOthers: InstrumentInsurance[],
  receivables: Receivable[],
}

export interface RegistrationProject {
  instruments: Instrument[],
  absence: Record<number, boolean>,
  absenceReasons: Record<number, string>,
}

export const useMemberDataStore = defineStore('member-data', () => {
  const simpleState = {
    addressSupplement: ref(undefined as string|undefined),
    birthday: ref(undefined as Date|undefined),
    city: ref(undefined as string|undefined),
    country: ref(undefined as string|undefined),
    email: ref(undefined as string|undefined),
    emailAddresses: ref([]),
    firstName: ref(undefined as string|undefined),
    fixedLinePhone: ref(undefined as string|undefined),
    initialized: ref({
      loaded: false,
      promise: null as null|Promise<any>,
      error: null as null|string,
      recryptRequest: null,
      registration: undefined as undefined|boolean,
    }),
    instrumentInsurances: ref([] as InstrumentInsurance[]),
    instruments: ref([] as Instrument[]),
    insuranceDetails: ref({
      self: [],
      forOthers: [],
      byOthers: [],
      receivables: [],
    } as InsuranceDetails),
    mobilePhone: ref(undefined as string|undefined),
    nickName: ref(undefined as string|undefined),
    personalPublicName: ref(undefined as string|undefined),
    postalCode: ref(undefined as string|undefined),
    projectApplication: ref([]),
    projectParticipation: ref([] as ProjectParticipant[]),
    sepaBankAccounts: ref([] as SepaBankAccount[]),
    street: ref(undefined as string|undefined),
    streetNumber: ref(undefined as string|undefined),
    surName: ref(undefined as string|undefined),
    //
    // perhaps there should be another store for just the registration
    //
    whoAmI: ref(undefined as string|undefined),
    projects: ref({} as Record<number, RegistrationProject>),
    selectedInstruments: ref([] as Instrument[]),
    firstTimeApplication: ref(undefined as undefined|'you-know-me'|'first-time'),
  }
  const initialState = Object.fromEntries(Object.entries(simpleState).map(([key, value]) => {
    return [key, typeof value.value === 'object' && value.value !== null ? deepCopy(value.value) : value.value]
  }))

  const resetState = () => {
    for (const [key, value] of Object.entries(initialState)) {
      simpleState[key].value = value
    }
  }

  const initialize = async (silent?: boolean, reset?: boolean) => {
    console.info('INIT')
    const initialized = simpleState.initialized.value
    if (initialized.loaded && !reset) {
      return
    }
    if (initialized.promise !== null) {
      await initialized.promise
      return
    }
    if (reset) {
      resetState()
    }
    try {
      initialized.promise = axios.get(generateAppUrl('member'))
      const response = await initialized.promise
      for (const [key, value] of Object.entries(response.data)) {
        if (key === 'birthday') {
          simpleState[key].value = new Date(value as string)
        } else {
          simpleState[key].value = value
        }
      }
      // do some basic initializations ...
      vueSet(simpleState, 'selectedInstruments', [])
        for (const instrument of simpleState.instruments.value) {
          simpleState.selectedInstruments.value.push(instrument)
        }
      initialized.promise = null
      initialized.error = null
      initialized.loaded = true
    } catch (e) {
      console.error('ERROR', e)
      let message = t(appId, 'general failure')
      if (isAxiosErrorResponse(e) && e.response.data) {
        const messages = (e.response.data as { messages?: string[] }).messages
        if (Array.isArray(messages)) {
          message = messages.join(' ')
        }
      }
      initialized.error = message
      if (!silent) {
        showError(t(appId, 'Could not fetch musician(s): {message}', { message }), { timeout: TOAST_PERMANENT_TIMEOUT })
      }
      const cloudUser = getCurrentUser()
      initialized.recryptRequest = null
      if (cloudUser?.uid) {
        try {
          const url = generateAppOcsUrl('api/v1/maintenance/encryption/recrypt/{userId}', {
            userId: cloudUser.uid,
          })
          const response = await axios.get(url + '?format=json')
          initialized.recryptRequest = response.data.ocs.data.request
        } catch (e) {
          console.error('Error retrieving recryption request', e)
        }
      }
      initialized.promise = null
    }
  }

  const load = async () => {
    console.info('LOAD')
    resetState()
    await initialize()
  }

  const appData = useAppDataStore()

  // computed data
  const registrationProject = computed(() =>
    appData.activeProject ? simpleState.projects.value[appData.activeProject.id] : null
  )

  const noAbsence = computed(() =>
    registrationProject.value
      ? !Object.values(registrationProject.value.absence).reduce((result, current) => result || !!current, false)
      : true
  )

  const personalProjectInstrumentOptions = computed(() => {
    if (!appData.activeProject) {
      return []
    }
    const possibleInstruments = appData.activeProject.instrumentation.filter(
      instrumentationNumber => instrumentationNumber.voice === 0 && simpleState.selectedInstruments.value.find(instrument => instrument.id === instrumentationNumber.instrument.id),
      )
      return possibleInstruments.map(instrumentationNumber => instrumentationNumber.instrument)
  })

  // if just one element is selected as "I can play this" then inject
  // it as chosen instrument for the project.
  watch(simpleState.selectedInstruments, (newValue, _oldValue) => {
    if (!appData.activeProject || newValue.length !== 1) {
      return
    }
    if (personalProjectInstrumentOptions.value.length === 1
      && personalProjectInstrumentOptions.value[0].id === newValue[0].id) {
      const projectId = appData.activeProject.id
      vueSet(simpleState.projects.value[projectId], 'instruments', newValue)
    }
  })

  const initializeRegistrationData = async () => {
    const initialized = simpleState.initialized.value
    if (!initialized.registration) {
      if (getCurrentUser()) {
        console.info('CURRENT USER', getCurrentUser())
        await initialize()
        simpleState.firstTimeApplication.value = 'you-know-me'
      } else {
        console.info('NOT LOGGED IN')
        simpleState.firstTimeApplication.value = 'first-time'
      }
      if (!simpleState.country.value) {
        simpleState.country.value = appData.displayLocale.region
      }
      initialized.registration = true
    }
    if (appData.activeProject) {
      if (!registrationProject.value) {
        const newRegistrationProject: RegistrationProject = reactive({
          instruments: [],
          absence: {},
          absenceReasons: {},
        })
        vueSet(simpleState.projects.value, appData.activeProject.id, newRegistrationProject)
        console.info('REGISTRATION PROJECT', { registrationProject: { ...newRegistrationProject } })
        for (const event of appData.activeProject.projectEvents) {
          newRegistrationProject.absence[event.id] = false
          newRegistrationProject.absenceReasons[event.id] = ''
        }
      }
    }
  }

  return {
    initialize,
    initializeRegistrationData,
    load,
    ...simpleState,
    registrationProject,
    noAbsence,
    personalProjectInstrumentOptions,
  }
})
