<script lang="ts" setup>
const props = defineProps<{ role?: Record<string, unknown> | null }>()

const getColor = () => {
  switch (props.role?.name) {
    case 'super_admin': {
      return '#e53935'
    }
    case 'admin': {
      return '#1e88e5'
    }
    case 'manager': {
      return '#00acc1'
    }
    case 'waiter': {
      return '#43a047'
    }
    case 'cashier': {
      return '#fb8c00'
    }
    case 'kitchen': {
      return '#8e24aa'
    }
    case 'admin_branch': {
      return '#05c46b'
    }
    case 'customer': {
      return '#6c5ce7'
    }
    case 'hr_manager': {
      return '#00897b'
    }
    case 'hr_officer': {
      return '#26a69a'
    }
    default: {
      return '#757575'
    }
  }
}
</script>

<template>
  <VChip
    v-if="role"
    :color="getColor()"
    size="x-small"
    style="font-size: 10px"
    text-color="white"
  >
    {{ role?.display_name }}
  </VChip>
</template>
