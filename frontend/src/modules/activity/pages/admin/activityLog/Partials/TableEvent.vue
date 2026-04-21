<script lang="ts" setup>
  const props = defineProps<{ item: any, color?: string }>()

  const getColor = () => {
    switch (props.item.event_key) {
      case 'created': {
        return 'success'
      }
      case 'updated': {
        return 'warning'
      }
      case 'deleted': {
        return 'error'
      }
      default: {
        return 'primary'
      }
    }
  }
</script>

<template>
  <TableBadge :color="getColor()" :item="item" item-key="event" />
</template>
