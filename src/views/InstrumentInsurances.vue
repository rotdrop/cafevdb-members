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
    <div v-if="loading" class="page-container loading" />
    <div v-else class="page-container">
      <h2>{{ t(appName, 'Instrument Insurances of {publicName}', {publicName: memberData.personalPublicName }) }}</h2>
      <CheckboxRadioSwitch v-if="haveDeleted" :checked.sync="showDeleted">
        {{ t(appName, 'show deleted') }}
      </CheckboxRadioSwitch>
      <ul class="insurance-sections">
        <ListItem :title="t(appName, 'Summary')"
                  :bold="true"
                  class="summary">
          <template #subtitle>
            <ul class="insurance-summary">
              <ListItem :title="t(appName, 'Total Insured Value')"
                        :details="totalInsuredValue + ' ' + currencySymbol" />
              <ListItem v-if="totalInsuredValue != totalPayableValue"
                        :title="t(appName, 'Total Payable Value')"
                        :details="totalPayableValue + ' ' + currencySymbol" />
              <ListItem :title="t(appName, 'Yearly Insurance fees w/o taxes')"
                        :details="totalPayableFees.toFixed(2) + ' ' + currencySymbol" />
              <ListItem :title="t(appName, 'Yearly Insurance fees with {taxes}% taxes', { taxes: taxRate*100.0 })"
                        :details="(totalPayableFees * (1.0 + taxRate)).toFixed(2) + ' ' + currencySymbol" />
            </ul>
          </template>
        </ListItem>
        <ListItem v-if="memberData.instrumentInsurances.forOthers.length > 0"
                  :title="t(appname, 'Paid for Others')"
                  :details="t(appName, 'paid by me, instrument used by someone else')"
                  :bold="true">
          <template #subtitle>
            <ul v-for="insurance in memberData.instrumentInsurances.forOthers"
                :key="insurance.id"
                class="insurance-list for-others">
              <ListItem :title="insurance.object"
                        :details="insurance.insuranceAmount + ' ' + currencySymbol"
                        :bold="true">
                <template #subtitle>
                  <ul class="insurance-details">
                    <ListItem :title="t(appName, 'manufacturer')" :details="insurance.manufacturer" />
                    <ListItem :title="t(appName, 'manufacturered')" :details="insurance.yearOfConstruction" />
                    <ListItem :title="t(appName, 'insurance broker')" :details="insurance.insuranceRate.broker.shortName" />
                    <ListItem :title="t(appName, 'insurance start')" :details="formatDate(insurance.startOfInsurance)" />
                    <ListItem :title="t(appName, 'geographical scope')" :details="t(appName, insurance.insuranceRate.geographicalScope)" />
                    <ListItem :title="t(appName, 'insurance rate')" :details="insurance.insuranceRate.rate*100.0 + '%'" />
                    <ListItem :title="t(appName, 'insurance fees')" :details="(insurance.insuranceAmount * insurance.insuranceRate.rate * (1. + taxRate)).toFixed(2) + ' ' + currencySymbol" />
                    <ListItem :title="t(appName, 'due date')" :details="formatDate(insurance.insuranceRate.dueDate, 'omit-year')" />
                  </ul>
                </template>
              </ListItem>
            </ul>
          </template>
        </ListItem>
        <ListItem v-if="memberData.instrumentInsurances.byOthers.length > 0"
                  :title="t(appname, 'Paid by Others')"
                  :details="t(appName, 'paid by someone else, instrument used by me')"
                  :bold="true">
          <template #subtitle>
            <ul v-for="insurance in memberData.instrumentInsurances.byOthers"
                :key="insurance.id"
                class="insurance-list by-others">
              <ListItem :title="insurance.object"
                        :details="insurance.insuranceAmount + ' ' + currencySymbol"
                        :bold="true">
                <template #subtitle>
                  <ul class="insurance-details">
                    <ListItem :title="t(appName, 'manufacturer')" :details="insurance.manufacturer" />
                    <ListItem :title="t(appName, 'manufacturered')" :details="insurance.yearOfConstruction" />
                    <ListItem :title="t(appName, 'insurance broker')" :details="insurance.insuranceRate.broker.shortName" />
                    <ListItem :title="t(appName, 'insurance start')" :details="formatDate(insurance.startOfInsurance)" />
                    <ListItem :title="t(appName, 'geographical scope')" :details="t(appName, insurance.insuranceRate.geographicalScope)" />
                    <ListItem :title="t(appName, 'insurance rate')" :details="insurance.insuranceRate.rate*100.0 + '%'" />
                    <ListItem :title="t(appName, 'insurance fees')" :details="(insurance.insuranceAmount * insurance.insuranceRate.rate * (1. + taxRate)).toFixed(2) + ' ' + currencySymbol" />
                    <ListItem :title="t(appName, 'due date')" :details="formatDate(insurance.insuranceRate.dueDate, 'omit-year')" />
                  </ul>
                </template>
              </ListItem>
            </ul>
          </template>
        </ListItem>
        <ListItem v-if="memberData.instrumentInsurances.self.length > 0"
                  :title="haveOthers ? t(appName, 'Self Used and Paid') : t(appName, 'Insured Instruments')"
                  :details="haveOthers ? t(appName, 'paid and used by myself') : ''"
                  :bold="true">
          <template #subtitle>
            <ul v-for="insurance in memberData.instrumentInsurances.self"
                :key="insurance.id"
                class="insurance-list self">
              <ListItem :title="insurance.object"
                        :details="insurance.insuranceAmount + ' ' + currencySymbol"
                        :bold="true">
                <template #subtitle>
                  <ul class="insurance-details">
                    <ListItem :title="t(appName, 'manufacturer')" :details="insurance.manufacturer" />
                    <ListItem :title="t(appName, 'manufacturered')" :details="insurance.yearOfConstruction" />
                    <ListItem :title="t(appName, 'insurance broker')" :details="insurance.insuranceRate.broker.shortName" />
                    <ListItem :title="t(appName, 'insurance start')" :details="formatDate(insurance.startOfInsurance)" />
                    <ListItem :title="t(appName, 'geographical scope')" :details="t(appName, insurance.insuranceRate.geographicalScope)" />
                    <ListItem :title="t(appName, 'insurance rate')" :details="insurance.insuranceRate.rate*100.0 + '%'" />
                    <ListItem :title="t(appName, 'insurance fees')" :details="(insurance.insuranceAmount * insurance.insuranceRate.rate * (1. + taxRate)).toFixed(2) + ' ' + currencySymbol" />
                    <ListItem :title="t(appName, 'due date')" :details="formatDate(insurance.insuranceRate.dueDate, 'omit-year')" />
                  </ul>
                </template>
              </ListItem>
            </ul>
          </template>
        </ListItem>
      </ul>
      <div class="debug-container">
        <CheckboxRadioSwitch :checked.sync="debug">
          {{ t(appName, 'Enable Debug') }}
        </CheckboxRadioSwitch>
        <div v-if="debug" class="debug">
          <div>{{ t(appName, 'DEBUG: all data') }}</div>
          <pre>{{ JSON.stringify(memberData, null, 2) }}</pre>
        </div>
      </div>
    </div>
  </Content>
</template>
<script>

import { appName } from '../config.js'
import Vue from 'vue'
import Content from '@nextcloud/vue/dist/Components/Content'
import ListItem from '@nextcloud/vue/dist/Components/ListItem'
import CheckboxRadioSwitch from '@nextcloud/vue/dist/Components/CheckboxRadioSwitch'
import '@nextcloud/dialogs/styles/toast.scss'
import { generateUrl } from '@nextcloud/router'
import { showError, TOAST_PERMANENT_TIMEOUT } from '@nextcloud/dialogs'
import axios from '@nextcloud/axios'
import { getLocale, getCanonicalLocale, } from '@nextcloud/l10n'
import moment from '@nextcloud/moment'

import { getInitialState } from '../services/InitialStateService'

const initialState = getInitialState()

export default {
  name: 'InstrumentInsurances',
  components: {
    Content,
    CheckboxRadioSwitch,
    ListItem,
  },
  mixins: [
    {
      data() {
        return {
          currencyCode: initialState.currencyCode,
          currencySymbol: initialState.currencySymbol,
          orchstraLocate: initialState.orchestraLocale,
        }
      },
      methods: {
        moment,
      },
    },
  ],
  data() {
    return {
      memberData: {
        instrumentInsurances: {
          self: [],
          forOthers: [],
          byOthers: [],
        },
      },
      taxRate: 0.19, // @todo make this configurable
      totalInsuredValue: 0.0,
      totalPayableValue: 0.0,
      totalPayableFees: 0.0,
      loading: true,
      debug: false,
      showDeleted: false,
      haveDeleted: false,
      haveOthers: false,
    }
  },
  async created() {
    const self = this;
    try {
      const response = await axios.get(generateUrl('/apps/' + appName + '/member'))
      for (const [key, value] of Object.entries(response.data)) {
        Vue.set(this.memberData, key, value)
      }
      const ownInsurances = []; // holder === debitor
      const insurancesForOthers = []; // debitor === thisMember, holder different
      const insurancesByOthers = []; // holer === thisMember, debitor different
      this.totalInsuredValue = 0.0;
      for (const insurance of this.memberData.instrumentInsurances) {
        if (insurance.deleted) {
          this.haveDeleted = true
        } else {
          this.totalInsuredValue += insurance.insuranceAmount
          if (insurance.isDebitor) {
            this.totalPayableValue += insurance.insuranceAmount
            this.totalPayableFees += insurance.insuranceAmount * insurance.insuranceRate.rate
          }
        }
        if (insurance.isDebitor === insurance.isHolder) {
          ownInsurances.push(insurance)
        } else if (insurance.isDebitor) {
          insurancesForOthers.push(insurance)
        } else {
          insurancesByOthers.push(insurance)
        }
      }
      this.haveOthers = (insurancesByOthers.length + insurancesForOthers.length) > 0;
      Vue.set(this.memberData, 'instrumentInsurances', {})
      Vue.set(this.memberData.instrumentInsurances, 'forOthers', insurancesForOthers)
      Vue.set(this.memberData.instrumentInsurances, 'byOthers', insurancesByOthers)
      Vue.set(this.memberData.instrumentInsurances, 'self', ownInsurances)
    } catch (e) {
      console.error('ERROR', e)
      let message = t(appName, 'reason unknown')
      if (e.response && e.response.data && e.response.data.message) {
        message = e.response.data.message
        console.info('RESPONSE', e.response)
      }
      // Ignore for the time being
      if (this === false) {
        showError(t(appName, 'Could not fetch musician(s): {message}', { message }), { timeout: TOAST_PERMANENT_TIMEOUT })
      }
    }
    this.loading = false
  },
  methods: {
    formatDate(date, flavour) {
      flavour = flavour || 'medium'
      switch (flavour) {
        case 'short':
        case 'medium':
        case 'long':
          return moment(date).format('L');
        case 'omit-year': {
          const event = new Date(date);
          const options = { month: 'short', day: 'numeric' };
          return event.toLocaleString(getCanonicalLocale(), options);
        }
      }
      return moment(data).format(flavour);
    },
  },
}
</script>
<style lang="scss" scoped>
.page-container {
  padding-left:0.5rem;
  &.loading {
    width:100%;
  }
}

.debug-container {
  width:100%;
}

.insurance-sections {
  min-width:32rem;
  ::v-deep > li:not(.summary) > .list-item {
    &:hover, &:focus {
      background-color:inherit;
    }
  }
}

::v-deep {
  .list-item {
    padding-right: 0;
    ul .list-item {
      padding-top:2px;
      padding-bottom:2px;
    }
  }

  .line-two__subtitle {
    padding-right:0;
  }

  .line-one--bold {
    &.line-one {
      .line-one__details {
        font-weight:inherit;
      }
    }
    &.line-two {
      font-weight: normal;
    }
  }
}
</style>
