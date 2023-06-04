/**
 * @copyright Copyright (c) 2022, 2023 Claus-Justus Heine <himself@claus-justus-heine.de>
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

import { appName as appId } from '../config.js'
import { set as vueSet } from 'vue'
import '@nextcloud/dialogs/styles/toast.scss'
import { generateUrl, generateOcsUrl } from '@nextcloud/router'
import { getCurrentUser } from '@nextcloud/auth'
import { showError, TOAST_PERMANENT_TIMEOUT } from '@nextcloud/dialogs'
import axios from '@nextcloud/axios'

export const useMemberDataStore = defineStore('member-data', {
  state: () => {
    return {
      firstName: null,
      surName: null,
      nickName: null,
      personalPublicName: null,
      addressSupplement: null,
      street: null,
      streetNumber: null,
      postalCode: null,
      country: null,
      birthday: null,
      email: null,
      emailAddresses: [],
      mobilePhone: null,
      fixedLinePhone: null,
      selectedInstruments: [],
      instruments: [],
      sepaBankAccounts: [],
      instrumentInsurances: {
        self: [],
        forOthers: [],
        byOthers: [],
        receivables: [],
      },
      projectParticipation: [],
      initialized: {
        loaded: false,
        promise: null,
        error: false,
        recryptRequest: null,
      },
    }
  },
  actions: {
    async initialize(silent, reset) {
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
          vueSet(this, key, value)
        }
        // do some basic initializations ...
        vueSet(this, 'birthday', new Date(this.birthday))
        vueSet(this, 'selectedInstruments', [])
        for (const instrument of this.instruments) {
          this.selectedInstruments.push(instrument)
        }
        this.initialized.promise = null
        this.initialized.error = false
        this.initialized.loaded = true
      } catch (e) {
        console.error('ERROR', e)
        let message = t(appId, 'general failure')
        if (e.response && e.response.data) {
          message = e.response.data.messages
          if (Array.isArray(message)) {
            message = message.join(' ')
          }
        }
        this.initialized.error = message
        if (!silent) {
          showError(t(appId, 'Could not fetch musician(s): {message}', { message }), { timeout: TOAST_PERMANENT_TIMEOUT })
        }
        const cloudUser = getCurrentUser() || {}
        this.initialized.recryptRequest = null
        if (cloudUser.uid) {
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
      this.$reset()
      await this.initialize()
    },
  },
})
