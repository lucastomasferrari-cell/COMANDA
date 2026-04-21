<script lang="ts" setup>
  const props = defineProps<{ item: any }>()

  const getColor = () => {
    switch (props.item.type.id) {
      case 'inclusive': {
        return 'primary'
      }
      case 'exclusive': {
        return 'info'
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
