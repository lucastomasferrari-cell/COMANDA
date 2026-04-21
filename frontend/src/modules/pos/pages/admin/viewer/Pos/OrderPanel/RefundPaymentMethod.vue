<script lang="ts" setup>
  import type { PosForm, PosMeta } from '@/modules/pos/contracts/posViewer.ts'
  import { useI18n } from 'vue-i18n'
  import { formatPrice } from '@/modules/core/utils/formatters.ts'

  const props = defineProps<{
    form: PosForm
    meta: PosMeta
    overpaidAmount: number
  }>()

  const { t } = useI18n()
  const { refundPaymentMethod } = toRefs(props.form)

  const togglePayment = (id: string | number) => {
    refundPaymentMethod.value = refundPaymentMethod.value === id ? null : id
  }
</script>

<template>
  <VAlert
    border="start"
    color="warning"
    type="warning"
  >
    <div class="text-subtitle-1 font-weight-medium">
      {{ t('pos::pos_viewer.not_user_overpaid_amount', {amount: formatPrice(overpaidAmount, meta.currency)}) }}
    </div>
  </VAlert>
  <div class="mt-3 mb-3">
    <VSlideGroup>
      <VSlideGroupItem
        v-for="method in meta.refundPaymentMethods"
        :key="method.id"
      >
        <div
          class="payment-card"
          :class="{ active:refundPaymentMethod === method.id }"
          @click="togglePayment(method.id)"
        >
          <div class="method-info">
            <VIcon :color="method.color" :icon="method.icon" />
            <span class="name">{{ method.name }}</span>
          </div>
          <div class="checkbox">
            <v-icon v-if="refundPaymentMethod === method.id" color="white" icon="tabler-check" />
          </div>
        </div>
      </VSlideGroupItem>
    </VSlideGroup>
  </div>
</template>

<style lang="scss" scoped>

:deep(.v-slide-group__prev),
:deep(.v-slide-group__next) {
  display: none !important;
}

.payment-card {
  border: 1px dashed #e0e0e0;
  border-radius: 10px;
  padding: 12px 16px;
  display: flex;
  align-items: center;
  justify-content: space-between;
  cursor: pointer;
  transition: all 0.25s ease;
  position: relative;
  width: 200px;
  margin: 0 6px;
}

.method-info {
  display: flex;
  align-items: center;
  gap: 10px;
}

.payment-card .name {
  font-size: 15px;
  font-weight: bold;

}

.payment-card .checkbox {
  height: 22px;
  width: 22px;
  border-radius: 50%;
  border: 1px solid #e0e0e0;
  display: flex;
  align-items: center;
  justify-content: center;
}

.payment-card.active {
  border-color: rgb(var(--v-theme-primary));
  background-color: rgba(var(--v-theme-primary), 0.05);
}

.payment-card.active .checkbox {
  background-color: rgb(var(--v-theme-primary));
  border-color: rgb(var(--v-theme-primary));
}
</style>
