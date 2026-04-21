<script lang="ts" setup>
  const props = defineProps<{ item: any }>()

  function getColor () {
    if (props.item.status?.color) {
      return props.item.status.color
    }

    switch (props.item.status?.id) {
      case 'received':
      case 'completed':
      case 'served':
      case 'issued':
      case 'approved':
      case 'present':
      case 'paid':
      case 'closed':
      case 'active':
      case 'available': {
        return 'success'
      }
      case 'cancelled':
      case 'failed':
      case 'revoked':
      case 'expired':
      case 'rejected':
      case 'refunded':
      case 'void':
      case 'absent': {
        return 'error'
      }
      case 'pending':
      case 'queued':
      case 'processing':
      case 'preparing':
      case 'reserved':
      case 'late':
      case 'off':
      case 'incomplete': {
        return 'warning'
      }
      case 'partially_received':
      case 'confirmed':
      case 'ready':
      case 'cleaning':
      case 'used':
      case 'draft':
      case 'occupied':
      case 'leave':
      case 'holiday': {
        return 'info'
      }
      case 'disabled': {
        return 'secondary'
      }
      case 'recalculated':
      case 'calculated':
      case 'recalculate': {
        return '#e84393'
      }
      case 'open': {
        return 'primary'
      }
      default: {
        return 'default'
      }
    }
  }
</script>

<template>
  <VChip
    v-if="item.status?.id"
    :color="getColor()"
    size="small"
  >
    <span v-if="item.status.icon" class="d-flex justify-center align-center">
      <VIcon :icon="item.status.icon" /> &nbsp;
    </span>
    {{ item.status.name }}
  </VChip>
  <span v-else>{{ item.status }}</span>
</template>
