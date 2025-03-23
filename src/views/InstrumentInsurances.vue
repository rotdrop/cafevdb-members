<!--
 - @copyright Copyright (c) 2022-2025 Claus-Justus Heine <himself@claus-justus-heine.de>
 -
 - @author Claus-Justus Heine <himself@claus-justus-heine.de>
 -
 - @license AGPL-3.0-or-later
 -
 - This program is free software: you can redistribute it and/or modify
 - it under the terms of the GNU Affero General Public License as
 - published by the Free Software Foundation, either version 3 of the
 - License, or (at your option) any later version.
 -
 - This program is distributed in the hope that it will be useful,
 - but WITHOUT ANY WARRANTY; without even the implied warranty of
 - MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 - GNU Affero General Public License for more details.
 -
 - You should have received a copy of the GNU Affero General Public License
 - along with this program. If not, see <http://www.gnu.org/licenses/>.
 -->
<template>
  <div :class="{ 'icon-loading': loading, 'page-container': true, loading, }">
    <h2>{{ t(appId, 'Instrument Insurances of {publicName}', {publicName: memberData.personalPublicName }) }}</h2>
    <NcCheckboxRadioSwitch v-if="haveDeleted" :checked.sync="showDeleted">
      {{ t(appId, 'show deleted') }}
    </NcCheckboxRadioSwitch>
    <div v-if="memberData.instrumentInsurances.length === 0">
      {{ t(appId, 'You do not have any instrument insurances.') }}
    </div>
    <ul v-else class="insurance-sections">
      <NcListItem :name="t(appId, 'Summary')"
                  :bold="true"
                  class="summary"
      >
        <template #subtitle>
          <ul class="insurance-summary">
            <NcListItem :name="t(appId, 'Total Insured Value')"
                        :details="totalInsuredValue + ' ' + currencySymbol"
            />
            <NcListItem v-if="totalInsuredValue != totalPayableValue"
                        :name="t(appId, 'Total Payable Value')"
                        :details="totalPayableValue + ' ' + currencySymbol"
            />
            <NcListItem :name="t(appId, 'Yearly Insurance fees w/o taxes')"
                        :details="totalPayableFees.toFixed(2) + ' ' + currencySymbol"
            />
            <NcListItem :name="t(appId, 'Yearly Insurance fees with {taxes}% taxes', { taxes: taxRate*100.0 })"
                        :details="(totalPayableFees * (1.0 + taxRate)).toFixed(2) + ' ' + currencySymbol"
            />
            <NcListItem :name="t(appId, 'Yearly Insurance Bills')">
              <template #details>
                <NcActions class="insurance-bill-list">
                  <NcActionLink v-for="receivable in insuranceBills"
                                :key="receivable.optionKey"
                                icon="icon-download"
                                :href="optionDownloadUrl(receivable.optionKey)"
                  >
                    {{ receivable.dataOption.label }}
                  </NcActionLink>
                </NcActions>
              </template>
            </NcListItem>
          </ul>
        </template>
      </NcListItem>
      <NcListItem v-if="memberData.insuranceDetails.forOthers.length > 0"
                  :name="t(appId, 'Paid for Others')"
                  :details="t(appId, 'instrument used by someone else')"
                  :bold="true"
      >
        <template #subtitle>
          <ul class="insurance-list for-others">
            <NcListItem v-for="insurance in memberData.insuranceDetails.forOthers"
                        :key="insurance.id"
                        :name="insurance.object"
                        class="insurance-item"
            >
              <template #details>
                <span class="insurance-amount">{{ insurance.insuranceAmount + ' ' + currencySymbol }}</span>
                <NcActions class="insurance-details">
                  <NcActionButton icon="icon-info"
                                  @click="requestInsuranceDetails(insurance)"
                  >
                    {{ t(appId, 'details') }}
                  </NcActionButton>
                </NcActions>
              </template>
            </NcListItem>
          </ul>
        </template>
      </NcListItem>
      <NcListItem v-if="memberData.insuranceDetails.byOthers.length > 0"
                  :name="t(appId, 'Paid by Others')"
                  :details="t(appId, 'instrument owned or used by me')"
                  :bold="true"
      >
        <template #subtitle>
          <ul class="insurance-list by-others">
            <NcListItem v-for="insurance in memberData.insuranceDetails.byOthers"
                        :key="insurance.id"
                        :name="insurance.object"
                        class="insurance-item"
            >
              <template #details>
                <span class="insurance-amount">{{ insurance.insuranceAmount + ' ' + currencySymbol }}</span>
                <NcActions class="insurance-details">
                  <NcActionButton icon="icon-info"
                                  @click="requestInsuranceDetails(insurance)"
                  >
                    {{ t(appId, 'details') }}
                  </NcActionButton>
                </NcActions>
              </template>
            </NcListItem>
          </ul>
        </template>
      </NcListItem>
      <NcListItem v-if="memberData.insuranceDetails.self.length > 0"
                  :name="haveOthers ? t(appId, 'Self Used and Paid') : t(appId, 'Insured Instruments')"
                  :details="haveOthers ? t(appId, 'instrument owned or used by me') : ''"
                  :bold="true"
      >
        <template #subtitle>
          <ul class="insurance-list self">
            <NcListItem v-for="insurance in memberData.insuranceDetails.self"
                        :key="insurance.id"
                        :name="insurance.object"
                        class="insurance-item"
            >
              <template #details>
                <span class="insurance-amount">{{ insurance.insuranceAmount + ' ' + currencySymbol }}</span>
                <NcActions class="insurance-details">
                  <NcActionButton icon="icon-info"
                                  @click="requestInsuranceDetails(insurance)"
                  >
                    {{ t(appId, 'details') }}
                  </NcActionButton>
                </NcActions>
              </template>
            </NcListItem>
          </ul>
        </template>
      </NcListItem>
    </ul>
    <DebugInfo :debug-data="memberData" />
  </div>
</template>
<script setup lang="ts">
import { appName as appId } from '../config.ts'
import { translate as t } from '@nextcloud/l10n'
import {
  ref,
  computed,
  set as vueSet,
  onBeforeMount,
} from 'vue'
import DebugInfo from '../components/DebugInfo.vue'
import {
  NcActions,
  NcActionLink,
  NcActionButton,
  NcCheckboxRadioSwitch,
  NcListItem,
} from '@nextcloud/vue'
import generateAppUrl from '../toolkit/util/generate-url.ts'
import { getInitialState } from '../toolkit/services/InitialStateService.js'
import { getRequestToken } from '@nextcloud/auth'
import { useMemberDataStore } from '../stores/memberData.ts'
import type {
  InstrumentInsurance,
  Receivable,
} from '../stores/memberData.ts'

const initialState = getInitialState()

const viewName = 'InstrumentInsurances'

const emit = defineEmits(['view-details'])

const memberData = useMemberDataStore()

// const currencyCode = computed(() => initialState.currencyCode)
const currencySymbol = computed(() => initialState.currencySymbol)
// const orchestraLocale = computed(() => initialState.orchestraLocale)

const taxRate = computed(() => 0.19) // @todo make this configurable
const totalInsuredValue = ref(0.0)
const totalPayableValue = ref(0.0)
const totalPayableFees = ref(0.0)
const loading = ref(true)
const showDeleted = ref(false)
const haveDeleted = ref(false)
const haveOthers = ref(false)

const insuranceBills = computed(
  () => memberData.insuranceDetails.receivables.filter(x => x.supportingDocumentId),
)

const optionDownloadUrl = (key: string) =>
  generateAppUrl('download/member/' + key + '?requesttoken=' + encodeURIComponent(getRequestToken() || ''))

const requestInsuranceDetails = (insurance: InstrumentInsurance) => {
  emit('view-details', {
    viewName,
    title: t(appId, '{insuredObject} ({insuredValue} {currencySymbol})', {
      insuredObject: insurance.object,
      insuredValue: insurance.insuranceAmount,
      currencySymbol: currencySymbol.value,
    }),
    props: {
      insurance,
      taxRate: taxRate.value,
      currencySymbol: currencySymbol.value,
      includeRole: haveOthers.value,
    },
  })
}

onBeforeMount(async () => {
  await memberData.initialize()

  if (memberData.initialized.loaded && !memberData.initialized[viewName]) {
    // extract insurances information
    const ownInsurances: InstrumentInsurance[] = [] // holder or owner === debitor
    const insurancesForOthers: InstrumentInsurance[] = [] // debitor === thisMember, holder and owner different
    const insurancesByOthers: InstrumentInsurance[] = [] // holder or owner === thisMember, debitor different
    for (const insurance of memberData.instrumentInsurances) {
      if (insurance.isDebitor) {
        if (insurance.isHolder) {
          ownInsurances.push(insurance)
        } else {
          insurancesForOthers.push(insurance)
        }
      } else {
        insurancesByOthers.push(insurance)
      }
    }
    vueSet(memberData.insuranceDetails, 'forOthers', insurancesForOthers)
    vueSet(memberData.insuranceDetails, 'byOthers', insurancesByOthers)
    vueSet(memberData.insuranceDetails, 'self', ownInsurances)

    const insuranceReceivables: Receivable[] = []
    for (const participant of memberData.projectParticipation) {
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
    insuranceReceivables.sort((left, right) => -parseInt(left.dataOption.data) + parseInt(right.dataOption.data))

    vueSet(memberData.insuranceDetails, 'receivables', insuranceReceivables)

    memberData.initialized[viewName] = true
  }

  if (memberData.initialized[viewName]) {

    totalInsuredValue.value = 0.0
    for (const insurance of memberData.insuranceDetails.self.concat(
      memberData.insuranceDetails.forOthers,
      memberData.insuranceDetails.byOthers,
    )) {
      if (insurance.deleted) {
        haveDeleted.value = true
      } else {
        totalInsuredValue.value += insurance.insuranceAmount
        if (insurance.isDebitor) {
          totalPayableValue.value += insurance.insuranceAmount
          totalPayableFees.value += insurance.insuranceAmount * insurance.insuranceRate.rate
        }
      }
    }
    haveOthers.value = (
      memberData.insuranceDetails.byOthers.length
      + memberData.insuranceDetails.forOthers.length
    ) > 0
  }

  loading.value = false
})
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
