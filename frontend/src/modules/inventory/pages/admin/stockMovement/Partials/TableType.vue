<script lang="ts" setup>
  const props = defineProps<{ item: any }>()

  const getColor = () => {
    switch (props.item.type.id) {
      case 'in': {
        return 'success'
      }
      case 'out': {
        return 'error'
      }
      case 'spoil': {
        return 'warning'
      }
      case 'adjust_add': {
        return 'info'
      }
      case 'adjust_subtract': {
        return 'warning'
      }
      case 'transfer_in': {
        return 'primary'
      }
      case 'transfer_out': {
        return 'secondary'
      }
      case 'return_supplier': {
        return 'error'
      }
      case 'sample': {
        return 'info'
      }
      case 'waste': {
        return 'error'
      }
      default: {
        return 'default'
      }
    }
  }
</script>

<template>
  <VChip
    :color="getColor()"
    size="small"
  >
    {{ item.type.name }}
  </VChip>
</template>
