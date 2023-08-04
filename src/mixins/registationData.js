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

import { appName } from '../config.js'
import { set as vueSet } from 'vue'
import { getCurrentUser } from '@nextcloud/auth'
import { loadState } from '@nextcloud/initial-state'
import { useAppDataStore } from '../stores/appData.js'
import { mapWritableState } from 'pinia'

const displayLocale = loadState(appName, 'displayLocale')

export default {
  methods: {
    async initializeRegistrationData() {
      if (!this.registrationData.initialized.registration) {
        vueSet(this.registrationData, 'whoAmI', '')
        if (getCurrentUser()) {
          console.info('CURRENT USER', getCurrentUser())
          await this.registrationData.initialize()
          vueSet(this.registrationData, 'firstTimeApplication', 'you-know-me')
        } else {
          console.info('NOT LOGGED IN')
          vueSet(this.registrationData, 'firstTimeApplication', 'first-time')
        }
        if (!this.registrationData.country) {
          vueSet(this.registrationData, 'country', displayLocale.region)
        }
        this.registrationData.initialized.registration = true
      }
      if (!this.registrationData.project) {
        vueSet(this.registrationData, 'project', {})
      }
      if (this.activeProject) {
        if (!this.registrationData.project[this.activeProject.id]) {
          vueSet(this.registrationData.project, this.activeProject.id, {
            instruments: [],
          })
        }
      }
    },
    info() {
      console.info('INFO', arguments)
    },
    routerDestination(routeName) {
      // The Vue-Rouer can handle optional parameters, but seemingly not empty parameters
      const params = this.projectName ? { projectName: this.projectName } : {}
      return { name: routeName, params }
    },
    routerGoHome() {
      this.$router.replace(this.routerDestination('registrationHome'))
    },
  },
  computed: {
    routePath() {
      console.info('ROUTE PATH', this.$route.path)
      return this.$route.path
    },
    ...mapWritableState(useAppDataStore, [
      'orchestraName',
      'projects',
      'activeProject',
      'instruments',
      'countries',
      'displayLocale',
      'memberRootFolder',
      'debug',
    ]),
    projectName() {
      return this.activeProject ? this.activeProject.name : ''
    },
    projectInstruments() {
      if (!this.activeProject) {
        return []
      }
      const possibleInstruments = this.activeProject.instrumentation.filter(
        instrumentationNumber => instrumentationNumber.voice === 0
      )
      return possibleInstruments.map(instrumentationNumber => instrumentationNumber.instrument)
    },
    projectInstrumentsText() {
      if (!this.activeProject) {
        return ''
      }
      return this.projectInstruments.map(instrument => instrument.name).join(', ')
    },
    personalProjectInstrumentOptions() {
      if (!this.activeProject) {
        return []
      }
      const possibleInstruments = this.activeProject.instrumentation.filter(
        instrumentationNumber => instrumentationNumber.voice === 0 && this.registrationData.selectedInstruments.find(instrument => instrument.id === instrumentationNumber.instrument.id)
      )
      return possibleInstruments.map(instrumentationNumber => instrumentationNumber.instrument)
    },
  },
  watch: {
    routePath(newValue, oldValue) {
      console.info('ROUTE PATH WATCHER', newValue, oldValue)
      if (newValue === '/') {
        return
      }
      const pathComponents = newValue.split('/')
      const projectName = pathComponents.length >= 3 ? pathComponents[2] : undefined
      const project = projectName ? this.projects.find(project => project.name === projectName) : undefined
      if (project && project.id !== this.activeProject.id) {
        console.info('Changing active project')
        this.activeProject = project
      }
    },
    'registrationData.selectedInstruments'(newValue, oldValue) {
      if (!this.activeProject || newValue.length !== 1) {
        return
      }
      if (this.personalProjectInstrumentOptions.length === 1
          && this.personalProjectInstrumentOptions[0].id === newValue[0].id) {
        const projectId = this.activeProject.id
        vueSet(this.registrationData.project[projectId], 'instruments', newValue)
      }
    },
  },
}
