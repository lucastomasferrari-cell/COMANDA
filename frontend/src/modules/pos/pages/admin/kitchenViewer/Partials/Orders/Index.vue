<script lang="ts" setup>
  import Order from './Order.vue'

  defineProps<{
    orders: Record<string, any>[]
  }>()

  defineEmits(['refresh'])

</script>

<template>
  <VRow dense>
    <VCol
      v-for="order in orders"
      :key="order.id"
      cols="12"
      lg="2"
      md="3"
      sm="6"
    >
      <Order :order="order" @refresh="$emit('refresh')" />
    </VCol>
  </VRow>
</template>
