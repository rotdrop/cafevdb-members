<!--
 - @copyright Copyright (c) 2022, 2024 Claus-Justus Heine <himself@claus-justus-heine.de>
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
  <ul v-if="insurance !== false" class="insurance-details">
    <ListItem :title="t(appId, 'manufacturer')" :details="insurance.manufacturer" />
    <ListItem :title="t(appId, 'manufacturered')" :details="insurance.yearOfConstruction" />
    <ListItem :title="t(appId, 'insurance broker')" :details="insurance.insuranceRate.broker.shortName" />
    <ListItem :title="t(appId, 'insurance start')" :details="formatDate(insurance.startOfInsurance)" />
    <ListItem :title="t(appId, 'geographical scope')" :details="t(appId, insurance.insuranceRate.geographicalScope)" />
    <ListItem :title="t(appId, 'insurance rate')" :details="insurance.insuranceRate.rate*100.0 + '%'" />
    <ListItem :title="t(appId, 'value')" :details="insurance.insuranceAmount + ' ' + currencySymbol" />
    <ListItem :title="t(appId, 'insurance fee')" :details="(insurance.insuranceAmount * insurance.insuranceRate.rate * (1. + taxRate)).toFixed(2) + ' ' + currencySymbol" />
    <ListItem :title="t(appId, 'due date')" :details="formatDate(insurance.insuranceRate.dueDate, 'omit-year')" />
    <ListItem v-if="includeRole" :title="t(appId, 'my role')" :details="roles" />
  </ul>
</template>
<script>
import { appName as appId } from '../../config.js'
import ListItem from '../../components/ListItem'
import formatDate from '../../mixins/formatDate.js'

export default {
  components: {
    ListItem,
  },
  props: {
    insurance: { type: [Object,Boolean], required: true, default: false },
    taxRate: { type: Number, required: true, default: 0.0 },
    currencySymbol: { type: String, required: true, default: '' },
    includeRole: true,
  },
  computed: {
    roles() {
      const roles = []
      this.insurance.isDebitor && roles.push(t(appId, 'debitor'))
      if (this.insurance.isHolder != this.insurance.isOwner) {
        this.insurance.isOwner && roles.push(t(appId, 'owner'))
        this.insurance.isHolder && roles.push(t(appId, 'holder'))
      } else {
        this.insurance.isOwner && roles.push(t(appId, 'owner'))
      }
      return roles.join('; ')
    }
  },
  mixins: [
    formatDate,
  ],
}
</script>
