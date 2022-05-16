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
<script>
import { appName } from '../../config.js'
import ListItem from '@nextcloud/vue/dist/Components/ListItem'
import formatDate from '../../mixins/formatDate.js'
export default {
  components: {
    ListItem,
  },
  props: {
    insurance: { type: Object, required: true },
    taxRate: { type: Number, required: true },
    currencySymbol: { type: String, required: true },
  },
  mixins: [
    formatDate,
  ],
}
</script>
