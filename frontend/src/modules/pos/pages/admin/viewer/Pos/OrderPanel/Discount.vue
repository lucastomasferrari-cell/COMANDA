<script lang="ts" setup>
  import type { UseCart } from '@/modules/cart/composables/cart.ts'
  import type { PosMeta } from '@/modules/pos/contracts/posViewer.ts'
  import { computed, ref, watch } from 'vue'
  import { useI18n } from 'vue-i18n'

  // Sprint 3.A.10 — Discount pasa de bloque permanente del footer del
  // check a VDialog invocado desde el overflow menu. El formato bloque +
  // dialog conviviendo (que hubo brevemente en 3.A.9) quedó eliminado:
  // todo el flujo discount/voucher vive ahora en este archivo.

  const props = defineProps<{
    modelValue: boolean
    meta: PosMeta
    cart: UseCart
    // Tab pre-seleccionada al abrir. Si el user vino del item "Aplicar
    // descuento" del overflow, arranca en 'discount'; si vino de
    // "Aplicar cupón", arranca en 'voucher'.
    initialType?: 'discount' | 'voucher'
  }>()

  const emit = defineEmits<{
    (e: 'update:modelValue', value: boolean): void
  }>()

  const { t } = useI18n()
  const { applyDiscount, applyVoucher } = props.cart

  const open = computed({
    get: () => props.modelValue,
    set: v => emit('update:modelValue', v),
  })

  const discountType = ref<'discount' | 'voucher'>(props.initialType ?? 'discount')
  const selectedValue = ref<number | string | null>(null)
  const loading = ref(false)

  watch(
    () => props.modelValue,
    isOpen => {
      if (isOpen) {
        discountType.value = props.initialType ?? 'discount'
        selectedValue.value = null
      }
    },
  )

  const types = [
    {
      id: 'discount' as const,
      label: t('order::orders.discount'),
      icon: 'tabler-shopping-bag-discount',
    },
    {
      id: 'voucher' as const,
      label: t('order::orders.voucher'),
      icon: 'tabler-ticket',
    },
  ]

  const submit = async (): Promise<void> => {
    if (!selectedValue.value || loading.value) return
    loading.value = true
    try {
      await (
        discountType.value === 'voucher'
          ? applyVoucher(selectedValue.value as string)
          : applyDiscount(selectedValue.value as number)
      )
      open.value = false
    } finally {
      loading.value = false
    }
  }
</script>

<template>
  <VDialog v-model="open" max-width="460">
    <VCard class="discount-dialog">
      <VCardTitle>
        {{ discountType === 'voucher'
          ? t('pos::pos_viewer.check_header.overflow.apply_voucher')
          : t('pos::pos_viewer.check_header.overflow.apply_discount') }}
      </VCardTitle>

      <VCardText>
        <!-- Segmented control entre Descuento y Cupón. Mantiene la chance
             de togglear dentro del mismo dialog sin tener que cerrar y
             reabrir desde el overflow. -->
        <div class="type-toggle d-flex ga-2 mb-4">
          <button
            v-for="type in types"
            :key="type.id"
            type="button"
            class="type-toggle__btn"
            :class="{ 'type-toggle__btn--active': discountType === type.id }"
            @click="discountType = type.id; selectedValue = null"
          >
            <VIcon class="me-2" :icon="type.icon" size="20" />
            {{ type.label }}
          </button>
        </div>

        <VTextField
          v-if="discountType === 'voucher'"
          v-model="selectedValue"
          autofocus
          clearable
          hide-details
          :label="t('pos::pos.enter_voucher_code')"
          variant="outlined"
        />
        <VSelect
          v-else
          v-model="selectedValue"
          autofocus
          hide-details
          item-title="name"
          item-value="id"
          :items="meta.discounts ?? []"
          :label="t('pos::pos.discount')"
          variant="outlined"
        />
      </VCardText>

      <VCardActions class="px-5 pb-5 ga-2">
        <VSpacer />
        <VBtn variant="text" @click="open = false">
          {{ t('admin::admin.buttons.cancel') }}
        </VBtn>
        <VBtn
          color="primary"
          :disabled="!selectedValue || loading"
          :loading="loading"
          variant="flat"
          @click="submit"
        >
          <VIcon icon="tabler-check" start />
          {{ t('pos::pos_viewer.apply') }}
        </VBtn>
      </VCardActions>
    </VCard>
  </VDialog>
</template>

<style lang="scss" scoped>
.type-toggle__btn {
  all: unset;
  cursor: pointer;
  flex: 1 1 0;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 12px 16px;
  border: 1px solid rgba(var(--v-theme-on-surface), 0.12);
  border-radius: 10px;
  font-weight: 600;
  font-size: 0.95rem;
  color: rgb(var(--v-theme-on-surface-variant));
  transition: border-color 150ms ease, background-color 150ms ease, color 150ms ease;

  &:hover {
    border-color: rgba(var(--v-theme-on-surface), 0.24);
  }
}

.type-toggle__btn--active {
  background-color: rgba(var(--v-theme-primary), 0.08);
  border-color: rgb(var(--v-theme-primary));
  color: rgb(var(--v-theme-primary));
}
</style>
