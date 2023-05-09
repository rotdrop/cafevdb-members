<script>
/**
 * @copyright Copyright (c) 2023 Claus-Justus Heine <himself@claus-justus-heine.de>
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
  <router-link class="button router"
               :to="to"
               :exact="exact"
  >
    <slot v-if="hasIconSlot && iconLeft" name="icon" />
    <span v-else-if="icon && iconLeft"
          :class="[ icon, 'left-icon' ]"
    />
    <slot />
    <slot v-if="hasIconSlot && iconRight" name="icon" />
    <span v-else-if="icon && iconRight"
          :class="[ icon, 'right-icon' ]"
    />
  </router-link>
</template>
<script>

export default {
  props: {
    /**
     * router-link to prop [https://router.vuejs.org/api/#to](https://router.vuejs.org/api/#to)
     */
    to: {
      type: [String, Object],
      default: '',
      required: true,
    },
    /**
     * router-link exact prop [https://router.vuejs.org/api/#exact](https://router.vuejs.org/api/#exact)
     */
    exact: {
      type: Boolean,
      default: false,
    },
    iconPosition: {
      type: String,
      default: 'left',
    },
    icon: {
      type: String,
      default: null,
    },
  },
  data() {
    return {
      /**
       * Making sure the slots are reactive
       */
      slots: this.$slots,
    }
  },
  computed: {
    iconLeft() {
      return this.iconPosition === 'left'
    },
    iconRight() {
      return this.iconPosition === 'right'
    },
    hasIconSlot() {
      return this.slots.icon !== undefined
    },
  },
}
</script>
<style lang="scss" scoped>
span {
  &[class^='icon-'], &[class*=' icon-'] {
    display: inline-block;
  }
  &.right-icon {
    margin-left: 1ex;
  }
  &.left-icon {
    margin-right: 1ex;
  }
}
</style>
