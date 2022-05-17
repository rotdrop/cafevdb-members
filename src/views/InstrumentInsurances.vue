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
  <Content :app-name="appId">
    <div v-if="loading" class="page-container loading" />
    <div v-else class="page-container">
      <h2>{{ t(appId, 'Instrument Insurances of {publicName}', {publicName: memberData.personalPublicName }) }}</h2>
      <CheckboxRadioSwitch v-if="haveDeleted" :checked.sync="showDeleted">
        {{ t(appId, 'show deleted') }}
      </CheckboxRadioSwitch>
      <ul class="insurance-sections">
        <ListItem :title="t(appId, 'Summary')"
                  :bold="true"
                  class="summary">
          <template #subtitle>
            <ul class="insurance-summary">
              <ListItem :title="t(appId, 'Total Insured Value')"
                        :details="totalInsuredValue + ' ' + currencySymbol" />
              <ListItem v-if="totalInsuredValue != totalPayableValue"
                        :title="t(appId, 'Total Payable Value')"
                        :details="totalPayableValue + ' ' + currencySymbol" />
              <ListItem :title="t(appId, 'Yearly Insurance fees w/o taxes')"
                        :details="totalPayableFees.toFixed(2) + ' ' + currencySymbol" />
              <ListItem :title="t(appId, 'Yearly Insurance fees with {taxes}% taxes', { taxes: taxRate*100.0 })"
                        :details="(totalPayableFees * (1.0 + taxRate)).toFixed(2) + ' ' + currencySymbol" />
              <li class="insurance-bills list-item__wrapper">
                <a class="list-item" href="#">
                  <div class="list-item-content">
                    <span class="label">{{ t(appId, 'Yearly Insurance Bills') }}</span>
                    <span class="menu">
                      <Actions class="insurance-bill-list">
                        <ActionLink v-for="receivable in insuranceBills"
                                    :key="receivable.optionKey"
                                    icon="icon-download"
                                    :href="optionDownloadUrl(receivable.optionKey)">
                          {{ receivable.dataOption.label }}
                        </ActionLink>
                      </Actions>
                    </span>
                  </div>
                </a>
              </li>
            </ul>
          </template>
        </ListItem>
        <ListItem v-if="memberData.instrumentInsurances.forOthers.length > 0"
                  :title="t(appname, 'Paid for Others')"
                  :details="t(appId, 'paid by me, instrument used by someone else')"
                  :bold="true">
          <template #subtitle>
            <ul v-for="insurance in memberData.instrumentInsurances.forOthers"
                :key="insurance.id"
                class="insurance-list for-others">
              <ListItem :title="insurance.object"
                        :details="insurance.insuranceAmount + ' ' + currencySymbol"
                        :bold="true">
                <template #subtitle>
                  <InsuranceDetails :insurance="insurance"
                                    :tax-rate="taxRate"
                                    :currency-symbol="currencySymbol" />
                </template>
              </ListItem>
            </ul>
          </template>
        </ListItem>
        <ListItem v-if="memberData.instrumentInsurances.byOthers.length > 0"
                  :title="t(appname, 'Paid by Others')"
                  :details="t(appId, 'paid by someone else, instrument used by me')"
                  :bold="true">
          <template #subtitle>
            <ul v-for="insurance in memberData.instrumentInsurances.byOthers"
                :key="insurance.id"
                class="insurance-list by-others">
              <ListItem :title="insurance.object"
                        :details="insurance.insuranceAmount + ' ' + currencySymbol"
                        :bold="true">
                <template #subtitle>
                  <InsuranceDetails :insurance="insurance"
                                    :tax-rate="taxRate"
                                    :currency-symbol="currencySymbol" />
                </template>
              </ListItem>
            </ul>
          </template>
        </ListItem>
        <ListItem v-if="memberData.instrumentInsurances.self.length > 0"
                  :title="haveOthers ? t(appId, 'Self Used and Paid') : t(appId, 'Insured Instruments')"
                  :details="haveOthers ? t(appId, 'paid and used by myself') : ''"
                  :bold="true">
          <template #subtitle>
            <ul v-for="insurance in memberData.instrumentInsurances.self"
                :key="insurance.id"
                class="insurance-list self">
              <ListItem :title="insurance.object"
                        :details="insurance.insuranceAmount + ' ' + currencySymbol"
                        :bold="true">
                <template #subtitle>
                  <InsuranceDetails :insurance="insurance"
                                    :tax-rate="taxRate"
                                    :currency-symbol="currencySymbol" />
                </template>
              </ListItem>
            </ul>
          </template>
        </ListItem>
      </ul>
      <div v-if="debug" class="debug-container">
        <CheckboxRadioSwitch :checked.sync="debug">
          {{ t(appId, 'Enable Debug') }}
        </CheckboxRadioSwitch>
        <div class="debug">
          <div>{{ t(appId, 'DEBUG: all data') }}</div>
          <pre>{{ JSON.stringify(memberData, null, 2) }}</pre>
        </div>
      </div>
    </div>
  </Content>
</template>
<script>

import { appName as appId } from '../config.js'
import Vue from 'vue'
import Content from '@nextcloud/vue/dist/Components/Content'
import ListItem from '@nextcloud/vue/dist/Components/ListItem'
import Actions from '@nextcloud/vue/dist/Components/Actions'
import ActionLink from '@nextcloud/vue/dist/Components/ActionLink'
import CheckboxRadioSwitch from '@nextcloud/vue/dist/Components/CheckboxRadioSwitch'
import InsuranceDetails from './InstrumentInsurances/InsuranceDetails'
import '@nextcloud/dialogs/styles/toast.scss'
import { generateUrl } from '@nextcloud/router'
import { showError, TOAST_PERMANENT_TIMEOUT } from '@nextcloud/dialogs'
import axios from '@nextcloud/axios'
import { getLocale, getCanonicalLocale, } from '@nextcloud/l10n'
import { getInitialState } from '../services/InitialStateService'
import { getRequestToken } from '@nextcloud/auth'

const initialState = getInitialState()
import { useAppDataStore } from '../stores/appData.js'
import { mapWritableState } from 'pinia'

export default {
  name: 'InstrumentInsurances',
  components: {
    Content,
    CheckboxRadioSwitch,
    ListItem,
    Actions,
    ActionLink,
    InsuranceDetails,
  },
  mixins: [
    {
      data() {
        return {
          currencyCode: initialState.currencyCode,
          currencySymbol: initialState.currencySymbol,
          orchestraLocale: initialState.orchestraLocale,
        }
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
          receivables: [],
        },
      },
      taxRate: 0.19, // @todo make this configurable
      totalInsuredValue: 0.0,
      totalPayableValue: 0.0,
      totalPayableFees: 0.0,
      loading: true,
      showDeleted: false,
      haveDeleted: false,
      haveOthers: false,
    }
  },
  computed: {
    insuranceBills() {
      return this.memberData.instrumentInsurances.receivables.filter(x => x.supportingDocumentId)
    },
    ...mapWritableState(useAppDataStore, ['debug']),
  },
  async created() {
    const self = this;
    try {
      const response = await axios.get(generateUrl('/apps/' + appId + '/member'))
      for (const [key, value] of Object.entries(response.data)) {
        Vue.set(this.memberData, key, value)
      }

      // extract insurances information
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

      const insuranceReceivables = [];
      for (const participant of this.memberData.projectParticipation) {
        if (participant.project.clubMembers) {
          // extract insurance receivables and supporting documents
          for (const [id, field] of Object.entries(participant.participantFields)) {
            if (field.name === 'Instrument Insurance'
                || field.untranslatedName === 'Instrument Insurance'
                || field.name === t(appId, 'Instrument Insurance')
                || field.untranslatedName === t(appId, 'Instrument Insurance')) {
              for (const [key, receivable] of Object.entries(field.fieldData)) {
                insuranceReceivables.push(receivable)
              }
            }
          }
        }
      }
      insuranceReceivables.sort((left, right) => - parseInt(left.dataOption.data) + parseInt(right.dataOption.data))
      Vue.set(this.memberData.instrumentInsurances, 'receivables', insuranceReceivables)
    } catch (e) {
      console.error('ERROR', e)
      let message = t(appId, 'reason unknown')
      if (e.response && e.response.data && e.response.data.message) {
        message = e.response.data.message
      }
      // Ignore for the time being
      if (this === false) {
        showError(t(appId, 'Could not fetch musician(s): {message}', { message }), { timeout: TOAST_PERMANENT_TIMEOUT })
      }
    }
    this.loading = false
  },
  methods: {
    optionDownloadUrl(key) {
      return generateUrl('/apps/' + appId + '/download/member/' + key + '?requesttoken=' + encodeURIComponent(getRequestToken()))
    }
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
  max-width:32rem;
  overflow:visible;
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

  .insurance-bills.list-item__wrapper {
    .list-item {
      padding: 2px 0 2px 8px;
      .list-item-content {
        display: flex;
        align-items: center;
        justify-content: space-between;
        white-space: nowrap;
        /* margin: 0 auto; */
        .label {
          flex-grow:1;
        }
        .menu {
          margin: 0 8px;
        }
      }
    }
  }
}
</style>
