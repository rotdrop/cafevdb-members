<script>
/**
 * From: https://swina.github.io/2019/02/how-to-create-a-simple-reusable-vue-input-text-component/
 * and modified quite a bit ..
 */
</script>
<template>
  <div :class="['input__container', 'input-type-' + type, { readonly, collapse }, has_hint, has_icon ]">
    <div :class="['input-effect', filled, { readonly, collapse }, has_hint, has_icon ]">
      <DatetimePicker v-if="isDatePickerType"
                      class="effect"
                      :type="isDatePickerType"
                      :value="value"
                      :data-foo="value"
                      :placeholder="placeholder"
                      :input-class="['effect', 'mx-input', { focusable: isFocusable }]"
                      :disabled="disabled || readonly"
                      :readonly="readonly"
                      v-bind="$attrs"
                      @focus="show = !show;"
                      @blur="show = !show;"
                      @input="$emit('input', $event.target ? $event.target.value : $event);" />
      <Multiselect v-else-if="isMultiselectType"
                   class="effect"
                   :value="value"
                   :placeholder="placeholder"
                   :disabled="disabled || readonly"
                   :readonly="readonly"
                   :label="optionLabel"
                   v-bind="$attrs"
                   v-on="$listeners"
                   @focus="show = !show;"
                   @blur="show = !show;" />
      <input v-else
             :type="type"
             :value="value"
             :placeholder="placeholder"
             :disabled="disabled"
             :class="['effect', has_icon, { focusable: isFocusable }]"
             :readonly="readonly"
             v-bind="$attrs"
             @focus="show = !show;"
             @blur="show = !show;"
             @input="$emit('input', $event.target.value);">
      <label :style="{ color: color }"><span>{{ label }}</span><span class="readonly-indicator"><LockIcon /></span></label>
      <span class="focus-border" :style="focus_border" />
    </div>
    <span v-if="show" class="input__hint">{{ hint }}</span>
    <i class="material-icons input__icon">{{ icon }}</i>
  </div>
</template>

<script>
import DatetimePicker from '@nextcloud/vue/dist/Components/DatetimePicker'
import Multiselect from '@nextcloud/vue/dist/Components/Multiselect'
import LockIcon from 'vue-material-design-icons/Lock.vue'
// The following would interfere with the rest of NC:
// import 'vue-material-design-icons/styles.css'
import 'material-icons/iconfont/material-icons.css'

export default {
  components: {
    Multiselect,
    DatetimePicker,
    LockIcon,
  },
  props: {
    type: { type: String, required: false, default: 'text' },
    disabled: { type: Boolean, required: false, default: false },
    readonly: { type: Boolean, required: false, default: false },
    value: { type: [String, Date, Array], required: false, default: '' },
    label: { type: String, required: false, default: '' },
    hint: { type: String, required: false, default: '' },
    icon: { type: String, required: false, default: '' },
    placeholder: { type: String, required: false, default: '' },
    color: { type: String, required: false, default: 'indigo' },
    optionLabel: { type: String, required: false, default: '' },
    collapse: { type: Boolean, requried: false, default: true },
  },
  data: () => ({
    show: false,
  }),
  computed: {
    filled() {
      if (!this.show && this.value) {
        return 'has-content'
      }
      return ''
    },
    has_icon() {
      if (this.icon) {
        return 'input__has_icon'
      }
      return ''
    },
    has_hint() {
      if (this.hint) {
        return 'input__has_hint'
      }
      return 'input__no_hint'
    },
    focus_border() {
      return {
        'background-color': this.color,
      }
    },
    isMultiselectType() {
      return this.type === 'multiselect'
    },
    isDatePickerType() {
      switch (this.type) {
      case 'date':
      case 'month':
      case 'time':
        return this.type
      case 'datetime-local':
        return 'datetime'
      }
      return false
    },
    /**
     * determines if the action is focusable
     *
     * @return {boolean} is the action focusable ?
     */
    isFocusable() {
      return !this.disabled
    },
  },
}
</script>

<style lang="scss" scoped>
.input__container {
  width: 100%;
  padding: 0.5rem 0.5rem 0 0;
  text-align: left;
  /* &.input__no_hint.collapse {
     .input__icon {
     top: -2rem;
     }
     } */
  &.input__has_icon {
    position: relative;
    left:0;
    top:0;
    .input__icon {
      position: absolute;
      left: 0rem;
      top: 2.5rem;
      opacity: 0.3;
    }
  }
}

.input__icon {
  position: relative;
  left: 0rem;
  top: -3.5rem;
  opacity: 0.3;
}

.input__hint {
  float: left;
  width: 100%;
  margin: -1.2rem 0 0 0;
  position: relative;
  font-size: 0.8rem;
  opacity: 0.6;
}

.input-effect {
  float: left;
  width: 100%;
  margin: 1.5rem 0rem 1.5rem 0;
  position: relative;  /* necessary to give position: relative to parent. */
  &.input__no_hint.collapse {
    margin-bottom:0;
  }
  &.readonly {
    .effect {
      ~ label {
        .readonly-indicator {
          display:inline;
        }
      }
    }
  }
}

input.input__has_icon {
  padding-left: 2rem !important;
}

input.effect {
  &:read-only {
    ~ label {
      .readonly-indicator {
        display:inline;
      }
    }
  }
}

.effect {
  &:not(input) {
    padding:0;
    margin:0;
    border:0;
    width:100%;
  }
  &,
  ::v-deep .mx-input-wrapper input.effect.mx-input {
    border: 0;
    padding: 4px 0;
    border-bottom: 1px solid #ccc;
    background-color: transparent;
    box-shadow:none;
    &:hover {
      border-color: var(--color-primary-element);
      outline: none;
    }
  }
  ::v-deep &.multiselect {
    max-height:37px;
    &.multiselect--disabled {
      &, & .multiselect__single {
        background-color:transparent!important;
      }
    }
    .multiselect__tags {
      border: 0;
      /* padding: 4px 0; */
      border-bottom: 1px solid #ccc;
      background-color: transparent;
      box-shadow:none;
      &:hover {
        border-color: var(--color-primary-element);
        outline: none;
      }
    }
  }
  ~ .focus-border {
    position: absolute;
    bottom: 0;
    left: 0;
    width: 0;
    height: 2px;
    background-color: indigo;
    transition: 0.4s;
  }

  ~ label {
    position: absolute;
    left: 0;
    width: 100%;
    top: -1.3rem;
    color: #aaa;
    transition: 0.3s;
    z-index: -1;
    letter-spacing: 0.5px;
    .readonly-indicator {
      position:absolute;
      top:-0.3em;
      display:none;
      height:1em;
      width:1em;
    }
  }

  &.readonly {
    ~ label {
      .readonly-indicator {
        display:inline;
      }
    }
  }

  &:focus, &:focus-within, &.has-content {
    ~ .focus-border {
      width: 100%;
      transition: 0.4s;
    }
    ~ label {
      top: -1rem;
      font-size: 0.8rem;
      color: indigo;
      transition: 0.3s;
    }
  }
}

::placeholder {
  opacity: 0.4;
}

input {
  &[type='text'], &[type='number'] {
    color: #555;
    width: 100%;
    box-sizing: border-box;
    letter-spacing: 1px;
    outline: none;
    margin-bottom:0;
  }
}

label {
  font-size: 0.9rem;
}
</style>
