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
  <Content :class="'app-' + appName" :app-name="appName">
    <div class="page-container">
      <h2>
        {{ t(appName, 'Bank Accounts of {publicName} ({count})', { publicName: memberData.personalPublicName, count: showDeleted ? memberData.sepaBankAccounts.length : memberData.numActiveBankAccounts }) }}
      </h2>
      <CheckboxRadioSwitch :checked.sync="showDeleted">
        {{ t(appName, 'show deleted') }}
      </CheckboxRadioSwitch>
      <ul v-for="account in memberData.sepaBankAccounts"
          :key="account.sequence"
          class="sepa-bank-accounts-list">
        <ListItem v-if="showDeleted || !account.deleted"
                  :title="t(appName, 'IBAN')"
                  :details="account.iban"
                  :bold="true">
          <template #subtitle>
            <ul class="sepa-bank-account-details">
              <!-- <ListItem :title="t(appName, 'BIC')" :details="account.bic" /> -->
              <ListItem :title="t(appName, 'owner')" :details="account.bankAccountOwner" />
              <ListItem :title="t(appName, 'registered')" :details="formatDate(account.created)" />
              <ListItem v-if="account.modified" :title="t(appName, 'modified')" :details="formatDate(account.modified)" />
              <ListItem v-if="account.deleted" :title="t(appName, 'revoked')" :details="formatDate(account.deleted)" />
              <ListItem v-if="(showDeleted && account.sepaDebitMandates) || (!showDeleted && account.numActiveDebitMandates > 0)"
                        :title="t(appName, 'Debit Mandates ({count})', { count: showDeleted ? account.sepaDebitMandates.length : account.numActiveDebitMandates, })">
                <template #subtitle>
                  <ul v-for="mandate in account.sepaDebitMandates"
                      :key="mandate.sequence"
                      class="sepa-debit-mandates-list">
                    <ListItem v-if="showDeleted || !mandate.deleted"
                              :title="t(appName, 'reference')"
                              :details="mandate.mandateReference">
                      <template v-if="true || showDeleted || !mandate.deleted" #subtitle>
                        <ul class="sepa-debit-mandate-details">
                          <ListItem :title="t(appName, 'granted')" :details="formatDate(mandate.mandateDate.date)" />
                          <ListItem v-if="mandate.lastUsedDate" :title="t(appName, 'last used')" :details="formatDate(mandate.lastUsedDate.date)" />
                          <ListItem v-if="mandate.modified" :title="t(appName, 'modified')" :details="formatDate(mandate.modified)" />
                          <ListItem v-if="mandate.deleted" :title="t(appName, 'revoked')" :details="formatDate(mandate.deleted)" />
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
import CounterBubble from '@nextcloud/vue/dist/Components/CounterBubble'
import CheckboxRadioSwitch from '@nextcloud/vue/dist/Components/CheckboxRadioSwitch'
import '@nextcloud/dialogs/styles/toast.scss'
import { generateUrl } from '@nextcloud/router'
import { showError, TOAST_PERMANENT_TIMEOUT } from '@nextcloud/dialogs'
import axios from '@nextcloud/axios'
import moment from '@nextcloud/moment'

export default {
  name: 'BankAccounts',
  components: {
    Content,
    CheckboxRadioSwitch,
    ListItem,
    CounterBubble,
  },
  mixins: [
    {
      methods: {
        moment,
      },
    },
  ],
  data() {
    return {
      memberData: {
        sepaBankAccounts: [],
        numActiveBankAccounts: 0,
        numDeletedBankAccounts: 0,
      },
      loading: true,
      debug: false,
      showDeleted: false,
    }
  },
  async created() {
    console.info('MOUNTED')
    try {
      const response = await axios.get(generateUrl('/apps/' + appName + '/member'))
      for (const [key, value] of Object.entries(response.data)) {
        Vue.set(this.memberData, key, value)
      }
      this.memberData.numDeletedBankAccounts = this.memberData.sepaBankAccounts.filter(account => !!account.deleted).length
      this.memberData.numActiveBankAccounts = this.memberData.sepaBankAccounts.length - this.memberData.numDeletedBankAccounts
      this.memberData.sepaBankAccounts.forEach((account, index) => {
        // this.memberData.sepaBankAccounts[index].numDeletedDebitMandates = account.sepaDebitMandates.filter(mandate => !!account.deleted).length
        account.numDeletedDebitMandates = account.sepaDebitMandates.filter(mandate => !!mandate.deleted).length
        account.numActiveDebitMandates = account.sepaDebitMandates.length - account.numDeletedDebitMandates
      })
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
      }
      return moment(data).format(flavour);
    },
  },
}
</script>
<style lang="scss" scoped>
.debug-container {
  width:100%;
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
