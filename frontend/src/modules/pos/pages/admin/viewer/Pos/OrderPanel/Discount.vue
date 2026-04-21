<script lang="ts" setup>
  import type { UseCart } from '@/modules/cart/composables/cart.ts'
  import type { PosMeta } from '@/modules/pos/contracts/posViewer.ts'
  import { useI18n } from 'vue-i18n'

  const props = defineProps<{
    meta: PosMeta
    cart: UseCart
  }>()

  const { t } = useI18n()

  const { applyDiscount, applyVoucher } = props.cart
  const loading = ref(false)
  const discount = ref<number | string | null>(null)
  const discountType = ref<'discount' | 'voucher'>('discount')
  const discountTypes = [
    {
      id: 'discount',
      name: t('order::orders.discount'),
      icon: 'tabler-shopping-bag-discount',
      color: '#2980b9',
    },
    {
      id: 'voucher',
      name: t('order::orders.voucher'),
      icon: 'tabler-ticket',
      color: '#16a085',
    },
  ]

  const submit = async () => {
    if (!discount.value || loading.value) return
    loading.value = true
    await (
      discountType.value === 'voucher'
        ? applyVoucher(discount.value as string)
        : applyDiscount(discount.value as number)
    )
    loading.value = false
    discount.value = null
  }

  const updateDiscountType = (type: Record<string, any>) => {
    discountType.value = type.id
  }
</script>

<template>
  <div class="mt-3 mb-4">
    <VDivider />
    <div class="discount-type-container  mt-4">
      <div
        v-for="type in discountTypes"
        :key="type.id"
        class="discount-type-card"
        :class="{ active:type.id === discountType }"
        @click="updateDiscountType(type)"
      >
        <div class="discount-type-info">
          <VIcon :color="type.color" :icon="type.icon" size="23" />
          <span class="name">{{ type.name }}</span>
        </div>
        <div class="checkbox">
          <v-icon v-if="type.id === discountType" color="white" icon="tabler-check" size="20" />
        </div>
      </div>
    </div>
    <div class=" flex items-end gap-2">
      <VTextField
        v-if="discountType ==='voucher'"
        v-model="discount"
        clearable
        :label="t('pos::pos.enter_voucher_code')"
      />
      <VSelect
        v-else
        v-model="discount"
        class="flex-grow"
        item-title="name"
        item-value="id"
        :items="meta.discounts"
        :label="t('pos::pos.discount')"
      />
      <VBtn
        class=" text-white"
        color="primary"
        :disabled="!discount || loading"
        :loading="loading"
        @click="submit"
      >
        <VIcon icon="tabler-circle-dashed-check" start />
        {{ t('pos::pos_viewer.apply') }}
      </VBtn>
    </div>
  </div>
</template>
<style lang="scss" scoped>
.flex {
  display: flex;
  align-items: flex-end;
}

.flex-grow {
  flex-grow: 1;
}

.gap-3 {
  gap: 0.75rem;
}

.discount-type-container {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 0.8rem;
}

.discount-type-card {
  border: 1px dashed #e0e0e0;
  border-radius: 10px;
  padding: 12px 16px;
  display: flex;
  align-items: center;
  justify-content: space-between;
  cursor: pointer;
  transition: all 0.25s ease;
  position: relative;
  width: 48%;
}

.discount-type-info {
  display: flex;
  align-items: center;
  gap: 10px;
}

.discount-type-card .name {
  font-size: 0.9rem;
  font-weight: 700;
}

.discount-type-card .checkbox {
  height: 19px;
  width: 19px;
  border-radius: 50%;
  border: 1px solid #e0e0e0;
  display: flex;
  align-items: center;
  justify-content: center;
}

.discount-type-card.active {
  border-color: rgb(var(--v-theme-primary));
  background-color: rgba(var(--v-theme-primary), 0.05);
}

.discount-type-card.active .checkbox {
  background-color: rgb(var(--v-theme-primary));
  border-color: rgb(var(--v-theme-primary));
}
</style>
