<script lang="ts" setup>
  import { useI18n } from 'vue-i18n'
  import OrderProduct from './OrderProduct.vue'

  defineProps<{
    order: Record<string, any>
  }>()
  defineEmits(['refresh'])

  const { t } = useI18n()

</script>

<template>
  <VCard class="pa-3 modern-card">

    <div class="d-flex justify-space-between align-center mb-4">
      <h3 class="text-h6 font-weight-bold">{{ t('pos::pos_viewer.order') }} #{{ order.order_number }}</h3>
      <div class="d-flex gap-2">
        <VChip
          v-if="order.table"
          color="primary"
          size="small"
          variant="tonal"
        >
          {{ order.table.name }}
        </VChip>
        <div class="text-caption">
          <VIcon class="mr-1" icon="tabler-clock" size="small" />
          {{ order.time }}
        </div>
      </div>
    </div>

    <div class="mb-4">
      <OrderProduct
        v-for="item in order.products"
        :key="item.id"
        :item="item"
        :order-id="order.id"
        @refresh="$emit('refresh')"
      />
    </div>

    <div class="d-flex ga-2 align-center">
      <TableStatus :item="order" />
      <TableEnum column="type" :item="order" />

    </div>
  </VCard>
</template>

<style lang="scss" scoped>
.modern-card {
  transition: 0.3s;
}

.modern-card:hover {
  transform: translateY(-3px);
}
</style>
