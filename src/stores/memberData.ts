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
import { generateUrl, generateOcsUrl } from '@nextcloud/router'
import { getCurrentUser } from '@nextcloud/auth'
import { showError, TOAST_PERMANENT_TIMEOUT } from '@nextcloud/dialogs'
import axios from '@nextcloud/axios'
import { isAxiosErrorResponse } from '../toolkit/types/axios-type-guards.ts'
import type { Project } from './appData.ts'

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

export interface ProjectParticipant {
  project: Project,
  participantFields: Record<number, ProjectParticipantField>,
}

export interface InstrumentInsurance {
  id: number,
  deleted?: string,
  isHolder: boolean,
  isDebitor: boolean,
  object: string,
  insuranceAmount: number,
  insuranceRate: {
    rate: number,
  }
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

export const useMemberDataStore = defineStore('member-data', {
  state: () => {
    return {
      firstName: undefined as string|undefined,
      surName: undefined as string|undefined,
      nickName: undefined as string|undefined,
      personalPublicName: undefined as string|undefined,
      addressSupplement: undefined as string|undefined,
      street: undefined as string|undefined,
      streetNumber: undefined as string|undefined,
      postalCode: undefined as string|undefined,
      city: undefined as string|undefined,
      country: undefined as string|undefined,
      birthday: undefined as string|undefined,
      email: undefined as string|undefined,
      emailAddresses: [],
      mobilePhone: undefined as string|undefined,
      fixedLinePhone: undefined as string|undefined,
      selectedInstruments: [],
      instruments: [],
      sepaBankAccounts: [] as SepaBankAccount[],
      instrumentInsurances: [] as InstrumentInsurance[],
      insuranceDetails: {
        self: [],
        forOthers: [],
        byOthers: [],
        receivables: [],
      } as InsuranceDetails,
      projectParticipation: [] as ProjectParticipant[],
      initialized: {
        loaded: false,
        promise: null as null|Promise<any>,
        error: null as null|string,
        recryptRequest: null,
      },
      projectApplication: [],
    }
  },
  actions: {
    async initialize(silent?: boolean, reset?: boolean) {
      console.info('INIT')
      if (this.initialized.loaded && !reset) {
        return
      }
      if (this.initialized.promise !== null) {
        await this.initialized.promise
        return
      }
      if (reset) {
        this.$reset()
      }
      try {
        this.initialized.promise = axios.get(generateUrl('/apps/' + appId + '/member'))
        const response = await this.initialized.promise
        for (const [key, value] of Object.entries(response.data)) {
          if (key === 'birthday') {
            vueSet(this, key, new Date(value as string))
          } else {
            vueSet(this, key, value)
          }
        }
        // do some basic initializations ...
        vueSet(this, 'selectedInstruments', [])
        for (const instrument of this.instruments) {
          this.selectedInstruments.push(instrument)
        }
        this.initialized.promise = null
        this.initialized.error = null
        this.initialized.loaded = true
      } catch (e) {
        console.error('ERROR', e)
        let message = t(appId, 'general failure')
        if (isAxiosErrorResponse(e) && e.response.data) {
          const messages = (e.response.data as { messages?: string[] }).messages
          if (Array.isArray(messages)) {
            message = messages.join(' ')
          }
        }
        this.initialized.error = message
        if (!silent) {
          showError(t(appId, 'Could not fetch musician(s): {message}', { message }), { timeout: TOAST_PERMANENT_TIMEOUT })
        }
        const cloudUser = getCurrentUser()
        this.initialized.recryptRequest = null
        if (cloudUser?.uid) {
          try {
            const url = generateOcsUrl('apps/cafevdb/api/v1/maintenance/encryption/recrypt/{userId}', {
              userId: cloudUser.uid,
            })
            const response = await axios.get(url + '?format=json')
            this.initialized.recryptRequest = response.data.ocs.data.request
          } catch (e) {
            console.error('Error retrieving recryption request', e)
          }
        }
        this.initialized.promise = null
      }
    },
    async load() {
      console.info('LOAD')
      this.$reset()
      await this.initialize()
    },
  },
})
