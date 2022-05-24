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
  <Content :class="'app-' + appId" :app-name="appId">
    <div v-if="loading" class="page-container loading" />
    <div v-else class="page-container">
      <h2>
        {{ t(appId, 'Bank Accounts of {publicName} ({count})', { publicName: memberData.personalPublicName, count: showDeleted ? memberData.sepaBankAccounts.length : numActiveBankAccounts }) }}
      </h2>
      <CheckboxRadioSwitch v-if="haveDeleted" :checked.sync="showDeleted">
        {{ t(appId, 'show deleted') }}
      </CheckboxRadioSwitch>
      <ul v-for="account in memberData.sepaBankAccounts"
          :key="account.sequence"
          class="sepa-bank-accounts-list">
        <ListItem v-if="showDeleted || !account.deleted"
                  :title="t(appId, 'IBAN')"
                  :details="account.iban"
                  :bold="true">
          <template #subtitle>
            <ul class="sepa-bank-account-details">
              <!-- <ListItem :title="t(appId, 'BIC')" :details="account.bic" /> -->
              <ListItem :title="t(appId, 'owner')" :details="account.bankAccountOwner" />
              <ListItem :title="t(appId, 'registered')" :details="formatDate(account.created)" />
              <ListItem v-if="account.modified" :title="t(appId, 'modified')" :details="formatDate(account.modified)" />
              <ListItem v-if="account.deleted" :title="t(appId, 'revoked')" :details="formatDate(account.deleted)" />
              <ListItem v-if="(showDeleted && account.sepaDebitMandates) || (!showDeleted && account.numActiveDebitMandates > 0)"
                        :title="t(appId, 'Debit Mandates ({count})', { count: showDeleted ? account.sepaDebitMandates.length : account.numActiveDebitMandates, })">
                <template #subtitle>
                  <ul v-for="mandate in account.sepaDebitMandates"
                      :key="mandate.sequence"
                      class="sepa-debit-mandates-list">
                    <ListItem v-if="showDeleted || !mandate.deleted"
                              :title="t(appId, 'reference')"
                              :details="mandate.mandateReference">
                      <template v-if="true || showDeleted || !mandate.deleted" #subtitle>
                        <ul class="sepa-debit-mandate-details">
                          <ListItem :title="t(appId, 'granted')" :details="formatDate(mandate.mandateDate.date)" />
                          <ListItem v-if="mandate.lastUsedDate" :title="t(appId, 'last used')" :details="formatDate(mandate.lastUsedDate.date)" />
                          <ListItem v-if="mandate.modified" :title="t(appId, 'modified')" :details="formatDate(mandate.modified)" />
                          <ListItem v-if="mandate.deleted" :title="t(appId, 'revoked')" :details="formatDate(mandate.deleted)" />
                        </ul>
                      </template>
                    </ListItem>
                  </ul>
                </template>
              </ListItem>
            </ul>
          </template>
        </ListItem>
      </ul>
      <DebugInfo :debug-data="memberData" />
    </div>
  </Content>
</template>
<script>

import { appName as appId } from '../config.js'
import Vue from 'vue'
import Content from '@nextcloud/vue/dist/Components/Content'
import ListItem from '../components/ListItem'
import DebugInfo from '../components/DebugInfo'
import CheckboxRadioSwitch from '@nextcloud/vue/dist/Components/CheckboxRadioSwitch'
import formatDate from '../mixins/formatDate.js'

import { useMemberDataStore } from '../stores/memberData.js'

const viewName = 'BankAccounts'

export default {
  name: viewName,
  components: {
    Content,
    CheckboxRadioSwitch,
    ListItem,
    DebugInfo,
  },
  setup() {
    const memberData = useMemberDataStore()
    return { memberData }
  },
  mixins: [
    formatDate,
  ],
  data() {
    return {
      loading: true,
      showDeleted: false,
      haveDeleted: false,
      numActiveBankAccounts: 0,
      numDeletedBankAccounts: 0,
    }
  },
  async created() {
    await this.memberData.initialize()

    if (this.memberData.initialized.loaded && !this.memberData.initialized[viewName]) {
      this.memberData.sepaBankAccounts.forEach((account, index) => {
        // this.memberData.sepaBankAccounts[index].numDeletedDebitMandates = account.sepaDebitMandates.filter(mandate => !!account.deleted).length
        account.numDeletedDebitMandates = account.sepaDebitMandates.filter(mandate => !!mandate.deleted).length
        account.numActiveDebitMandates = account.sepaDebitMandates.length - account.numDeletedDebitMandates
      })
      this.memberData.initialized[viewName] = true;
    }

    if (this.memberData.initialized[viewName]) {

      this.numDeletedBankAccounts = this.memberData.sepaBankAccounts.filter(account => !!account.deleted).length
      this.haveDeleted = this.numDeletedBankAccounts > 0;
      this.numActiveBankAccounts = this.memberData.sepaBankAccounts.length - this.numDeletedBankAccounts
      const self = this;
      this.memberData.sepaBankAccounts.forEach((account, index) => {
        self.haveDeleted = self.haveDeleted || (account.numDeletedDebitMandates > 0)
      })
    }

    this.loading = false
  },
  methods: {},
}
</script>
<style lang="scss" scoped>
.page-container {
  padding-left:0.5rem;
  &.loading {
    width:100%;
  }
}

.sepa-bank-accounts-list {
  min-width:32rem;
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
