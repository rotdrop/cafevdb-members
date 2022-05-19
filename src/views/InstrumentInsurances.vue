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
              <ListItem :title="t(appId, 'Yearly Insurance Bills')">
                <template #details>
                  <Actions class="insurance-bill-list">
                    <ActionLink v-for="receivable in insuranceBills"
                                :key="receivable.optionKey"
                                icon="icon-download"
                                :href="optionDownloadUrl(receivable.optionKey)">
                      {{ receivable.dataOption.label }}
                    </ActionLink>
                  </Actions>
                </template>
              </ListItem>
            </ul>
          </template>
        </ListItem>
        <ListItem v-if="memberData.instrumentInsurances.forOthers.length > 0"
                  :title="t(appname, 'Paid for Others')"
                  :details="t(appId, 'paid by me, instrument used by someone else')"
                  :bold="true">
          <template #subtitle>
            <ul class="insurance-list for-others">
              <ListItem v-for="insurance in memberData.instrumentInsurances.forOthers"
                        :key="insurance.id"
                        :title="insurance.object"
                        :bold="true">
                <template #details>
                  <span class="insurance-amount">{{ insurance.insuranceAmount + ' ' + currencySymbol }}</span>
                  <Actions class="insurance-details">
                    <ActionButton icon="icon-info"
                                  @click="requestInsuranceDetails(insurance)">
                      {{ t(appId, 'details') }}
                    </ActionButton>
                  </Actions>
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
            <ul class="insurance-list by-others">
              <ListItem v-for="insurance in memberData.instrumentInsurances.byOthers"
                        :key="insurance.id"
                        :title="insurance.object"
                        :bold="true">
                <template #details>
                  <span class="insurance-amount">{{ insurance.insuranceAmount + ' ' + currencySymbol }}</span>
                  <Actions class="insurance-details">
                    <ActionButton icon="icon-info"
                                  @click="requestInsuranceDetails(insurance)">
                      {{ t(appId, 'details') }}
                    </ActionButton>
                  </Actions>
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
            <ul class="insurance-list self">
              <ListItem v-for="insurance in memberData.instrumentInsurances.self"
                        :key="insurance.id"
                        :title="insurance.object"
                        class="insurance-item">
                <template #details>
                  <span class="insurance-amount">{{ insurance.insuranceAmount + ' ' + currencySymbol }}</span>
                  <Actions class="insurance-details">
                    <ActionButton icon="icon-info"
                                  @click="requestInsuranceDetails(insurance)">
                      {{ t(appId, 'details') }}
                    </ActionButton>
                  </Actions>
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
// import ListItem from '@nextcloud/vue/dist/Components/ListItem'
import ListItem from '../components/ListItem'
import Actions from '@nextcloud/vue/dist/Components/Actions'
import ActionLink from '@nextcloud/vue/dist/Components/ActionLink'
import ActionButton from '@nextcloud/vue/dist/Components/ActionButton'
import CheckboxRadioSwitch from '@nextcloud/vue/dist/Components/CheckboxRadioSwitch'
import AppSidebar from '@nextcloud/vue/dist/Components/AppSidebar'
import AppSidebarTab from '@nextcloud/vue/dist/Components/AppSidebarTab'
import InsuranceDetails from './InstrumentInsurances/InsuranceDetails'
import { generateUrl } from '@nextcloud/router'
import { getLocale, getCanonicalLocale, } from '@nextcloud/l10n'
import { getInitialState } from '../services/InitialStateService'
import { getRequestToken } from '@nextcloud/auth'

const initialState = getInitialState()
import { useAppDataStore } from '../stores/appData.js'
import { useMemberDataStore } from '../stores/memberData.js'
import { mapWritableState } from 'pinia'

const viewName ='InstrumentInsurances'

export default {
  name: viewName,
  components: {
    Content,
    CheckboxRadioSwitch,
    ListItem,
    Actions,
    ActionLink,
    ActionButton,
    InsuranceDetails,
    AppSidebar,
    AppSidebarTab,
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
  setup() {
    const memberData = useMemberDataStore()
    return { memberData }
  },
  data() {
    return {
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
    await this.memberData.initialize()

    if (!this.memberData.initialized[viewName]) {
      // extract insurances information
      const ownInsurances = []; // holder === debitor
      const insurancesForOthers = []; // debitor === thisMember, holder different
      const insurancesByOthers = []; // holer === thisMember, debitor different
      for (const insurance of this.memberData.instrumentInsurances) {
        if (insurance.isDebitor === insurance.isHolder) {
          ownInsurances.push(insurance)
        } else if (insurance.isDebitor) {
          insurancesForOthers.push(insurance)
        } else {
          insurancesByOthers.push(insurance)
        }
      }
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

      this.memberData.initialized[viewName] = true;
    }

    this.totalInsuredValue = 0.0;
    for (const insurance of this.memberData.instrumentInsurances.self.concat(
      this.memberData.instrumentInsurances.forOthers,
      this.memberData.instrumentInsurances.byOthers
    )) {
      insurance.showDetails = false
      if (insurance.deleted) {
        this.haveDeleted = true
      } else {
        this.totalInsuredValue += insurance.insuranceAmount
        if (insurance.isDebitor) {
          this.totalPayableValue += insurance.insuranceAmount
          this.totalPayableFees += insurance.insuranceAmount * insurance.insuranceRate.rate
        }
      }
    }
    this.haveOthers = (
      this.memberData.instrumentInsurances.byOthers.length
      +
      this.memberData.instrumentInsurances.forOthers.length
    ) > 0

    this.loading = false
  },
  methods: {
    optionDownloadUrl(key) {
      return generateUrl('/apps/' + appId + '/download/member/' + key + '?requesttoken=' + encodeURIComponent(getRequestToken()))
    },
    requestInsuranceDetails(insurance) {
      this.$emit('view-details', {
        viewName,
        title: t(appId, '{insuredObject} ({insuredValue} {currencySymbol})', {
          insuredObject: insurance.object,
          insuredValue: insurance.insuranceAmount,
          currencySymbol: this.currencySymbol,
        }),
        props: {
          insurance,
          taxRate: this.taxRate,
          currencySymbol: this.currencySymbol,
        }
      })
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

    .list-item__wrapper.insurance-item {
      .line-one__details {
        color:inherit;
        display:flex;
        align-items:center;
        .insurance-amount {
          margin-right:0.2em;
        }
      }
    }

  }
}
</style>
