<template>
  <div v-if="message" :class="['alert', alertClass, 'mt-3']">
    <slot>{{ message }}</slot>
  </div>
</template>

<script>
export default {
  name: 'AlertBox',
  props: {
    message: {
      type: String,
      default: ''
    },
    isError: {
      type: Boolean,
      default: false
    },
    type: {
      type: String,
      default: '' // 'success', 'danger', 'warning'
    }
  },
  computed: {
    alertClass() {
      if (this.type) {
        return `alert-${this.type}`
      }
      return this.isError ? 'alert-danger' : 'alert-success'
    }
  }
}
</script>
