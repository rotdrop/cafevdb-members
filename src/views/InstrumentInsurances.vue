<script>
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
</script>
<template>
  <div :class="{ 'icon-loading': loading, 'page-container': true, loading, }">
    <h2>{{ t(appId, 'Instrument Insurances of {publicName}', {publicName: memberData.personalPublicName }) }}</h2>
    <CheckboxRadioSwitch v-if="haveDeleted" :checked.sync="showDeleted">
      {{ t(appId, 'show deleted') }}
    </CheckboxRadioSwitch>
    <div v-if="memberData.instrumentInsurances.length === 0">
      {{ t(appId, 'You do not have any instrument insurances.') }}
    </div>
    <ul v-else class="insurance-sections">
      <ListItem :title="t(appId, 'Summary')"
                :bold="true"
                class="summary"
      >
        <template #subtitle>
          <ul class="insurance-summary">
            <ListItem :title="t(appId, 'Total Insured Value')"
                      :details="totalInsuredValue + ' ' + currencySymbol"
            />
            <ListItem v-if="totalInsuredValue != totalPayableValue"
                      :title="t(appId, 'Total Payable Value')"
                      :details="totalPayableValue + ' ' + currencySymbol"
            />
            <ListItem :title="t(appId, 'Yearly Insurance fees w/o taxes')"
                      :details="totalPayableFees.toFixed(2) + ' ' + currencySymbol"
            />
            <ListItem :title="t(appId, 'Yearly Insurance fees with {taxes}% taxes', { taxes: taxRate*100.0 })"
                      :details="(totalPayableFees * (1.0 + taxRate)).toFixed(2) + ' ' + currencySymbol"
            />
            <ListItem :title="t(appId, 'Yearly Insurance Bills')">
              <template #details>
                <Actions class="insurance-bill-list">
                  <ActionLink v-for="receivable in insuranceBills"
                              :key="receivable.optionKey"
                              icon="icon-download"
                              :href="optionDownloadUrl(receivable.optionKey)"
                  >
                    {{ receivable.dataOption.label }}
                  </ActionLink>
                </Actions>
              </template>
            </ListItem>
          </ul>
        </template>
      </ListItem>
      <ListItem v-if="memberData.insuranceDetails.forOthers.length > 0"
                :title="t(appId, 'Paid for Others')"
                :details="t(appId, 'instrument used by someone else')"
                :bold="true"
      >
        <template #subtitle>
          <ul class="insurance-list for-others">
            <ListItem v-for="insurance in memberData.insuranceDetails.forOthers"
                      :key="insurance.id"
                      :title="insurance.object"
                      class="insurance-item"
            >
              <template #details>
                <span class="insurance-amount">{{ insurance.insuranceAmount + ' ' + currencySymbol }}</span>
                <Actions class="insurance-details">
                  <ActionButton icon="icon-info"
                                @click="requestInsuranceDetails(insurance)"
                  >
                    {{ t(appId, 'details') }}
                  </ActionButton>
                </Actions>
              </template>
            </ListItem>
          </ul>
        </template>
      </ListItem>
      <ListItem v-if="memberData.insuranceDetails.byOthers.length > 0"
                :title="t(appId, 'Paid by Others')"
                :details="t(appId, 'instrument owned or used by me')"
                :bold="true"
      >
        <template #subtitle>
          <ul class="insurance-list by-others">
            <ListItem v-for="insurance in memberData.insuranceDetails.byOthers"
                      :key="insurance.id"
                      :title="insurance.object"
                      class="insurance-item"
            >
              <template #details>
                <span class="insurance-amount">{{ insurance.insuranceAmount + ' ' + currencySymbol }}</span>
                <Actions class="insurance-details">
                  <ActionButton icon="icon-info"
                                @click="requestInsuranceDetails(insurance)"
                  >
                    {{ t(appId, 'details') }}
                  </ActionButton>
                </Actions>
              </template>
            </ListItem>
          </ul>
        </template>
      </ListItem>
      <ListItem v-if="memberData.insuranceDetails.self.length > 0"
                :title="haveOthers ? t(appId, 'Self Used and Paid') : t(appId, 'Insured Instruments')"
                :details="haveOthers ? t(appId, 'instrument owned or used by me') : ''"
                :bold="true"
      >
        <template #subtitle>
          <ul class="insurance-list self">
            <ListItem v-for="insurance in memberData.insuranceDetails.self"
                      :key="insurance.id"
                      :title="insurance.object"
                      class="insurance-item"
            >
              <template #details>
                <span class="insurance-amount">{{ insurance.insuranceAmount + ' ' + currencySymbol }}</span>
                <Actions class="insurance-details">
                  <ActionButton icon="icon-info"
                                @click="requestInsuranceDetails(insurance)"
                  >
                    {{ t(appId, 'details') }}
                  </ActionButton>
                </Actions>
              </template>
            </ListItem>
          </ul>
        </template>
      </ListItem>
    </ul>
    <DebugInfo :debug-data="memberData" />
  </div>
</template>
<script>

import { appName as appId } from '../config.js'
import { set as vueSet } from 'vue'
import ListItem from '../components/ListItem'
import DebugInfo from '../components/DebugInfo'
import Actions from '@nextcloud/vue/dist/Components/NcActions'
import ActionLink from '@nextcloud/vue/dist/Components/NcActionLink'
import ActionButton from '@nextcloud/vue/dist/Components/NcActionButton'
import CheckboxRadioSwitch from '@nextcloud/vue/dist/Components/NcCheckboxRadioSwitch'
import AppSidebar from '@nextcloud/vue/dist/Components/NcAppSidebar'
import AppSidebarTab from '@nextcloud/vue/dist/Components/NcAppSidebarTab'
import { generateUrl } from '@nextcloud/router'
import { getLocale, getCanonicalLocale, } from '@nextcloud/l10n'
import { getInitialState } from '../toolkit/services/InitialStateService'
import { getRequestToken } from '@nextcloud/auth'

const initialState = getInitialState()
import { useMemberDataStore } from '../stores/memberData.js'

const viewName ='InstrumentInsurances'

export default {
  name: viewName,
  components: {
    CheckboxRadioSwitch,
    ListItem,
    DebugInfo,
    Actions,
    ActionLink,
    ActionButton,
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
        return this.memberData.insuranceDetails.receivables.filter(x => x.supportingDocumentId)
    },
  },
  async created() {
    await this.memberData.initialize()

    if (this.memberData.initialized.loaded && !this.memberData.initialized[viewName]) {
      // extract insurances information
      const ownInsurances = []; // holder or owner === debitor
      const insurancesForOthers = []; // debitor === thisMember, holder and owner different
      const insurancesByOthers = []; // holder or owner === thisMember, debitor different
      for (const insurance of this.memberData.instrumentInsurances) {
        if (insurance.isDebitor) {
          if (insurance.isHolder) {
            ownInsurances.push(insurance);
          } else {
            insurancesForOthers.push(insurance)
          }
        } else {
          insurancesByOthers.push(insurance)
        }
      }
      vueSet(this.memberData.insuranceDetails, 'forOthers', insurancesForOthers)
      vueSet(this.memberData.insuranceDetails, 'byOthers', insurancesByOthers)
      vueSet(this.memberData.insuranceDetails, 'self', ownInsurances)

      const insuranceReceivables = [];
      for (const participant of this.memberData.projectParticipation) {
        console.info('PROJECT', participant)
        if (participant.project.clubMembers) {
          // extract insurance receivables and supporting documents
          for (const [id, field] of Object.entries(participant.participantFields)) {
            console.info('FIELD', id, field)
            if (field.name === 'Instrument Insurance'
                || field.untranslatedName === 'Instrument Insurance'
                || field.name === t(appId, 'Instrument Insurance')
                || field.untranslatedName === t(appId, 'Instrument Insurance')) {
              for (const [key, receivable] of Object.entries(field.fieldData)) {
                console.info('RECEIVABLE', key, receivable)
                insuranceReceivables.push(receivable)
              }
            }
          }
        }
      }
      insuranceReceivables.sort((left, right) => - parseInt(left.dataOption.data) + parseInt(right.dataOption.data))

      vueSet(this.memberData.insuranceDetails, 'receivables', insuranceReceivables)

      this.memberData.initialized[viewName] = true;
    }

    if (this.memberData.initialized[viewName]) {

      this.totalInsuredValue = 0.0;
      for (const insurance of this.memberData.insuranceDetails.self.concat(
        this.memberData.insuranceDetails.forOthers,
        this.memberData.insuranceDetails.byOthers
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
        this.memberData.insuranceDetails.byOthers.length
        +
        this.memberData.insuranceDetails.forOthers.length
      ) > 0
    }

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
          includeRole: this.haveOthers,
        }
      })
    },
  },
}
</script>
<style lang="scss" scoped>
.page-container {
  padding-left:50px;
  padding-top:12px;
  min-height:100%;
  &.loading {
    width:100%;
    * {
      display:none;
    }
  }
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
