<script lang="ts" setup>
  import type { UseCart } from '@/modules/cart/composables/cart.ts'
  import type { PosForm } from '@/modules/pos/contracts/posViewer.ts'
  import { useI18n } from 'vue-i18n'
  import DateTimePicker from '@/modules/core/components/Form/DateTimePicker.vue'

  const props = defineProps<{
    form: PosForm
    cart: UseCart
  }>()
  const { processing, data } = props.cart
  const { t } = useI18n()
  const { form } = toRefs(props)
</script>

<template>
  <div>
    <VRow dense>
      <VCol v-if="['pre_order','catering'].includes(data.orderType?.id as string)" cols="12">
        <DateTimePicker
          v-model="form.meta.scheduledAt"
          clearable
          :disabled="processing"
          :label="t('order::attributes.orders.scheduled_at')"
          :min="new Date().toLocaleDateString('en-CA')"
        />
      </VCol>
      <template v-if="data.orderType?.id === 'drive_thru'">
        <VCol cols="12">
          <VTextField
            v-model="form.meta.carPlate"
            clearable
            :label="t('order::attributes.orders.car_plate')"
            :readonly="processing"
          />
        </VCol>
        <VCol cols="12">
          <VTextField
            v-model="form.meta.carDescription"
            clearable
            :label="t('order::attributes.orders.car_description')"
            :readonly="processing"
          />
        </VCol>
      </template>
      <VCol cols="12">
        <VTextarea
          v-model="form.meta.notes"
          auto-grow
          clearable
          :label="t('order::attributes.orders.notes')"
          :readonly="processing"
          rows="2"
        />
      </VCol>
      <!-- Sprint 2 B.2 — comensales editable inline (reemplaza
           GuestCountDialog que aparecía ANTES de abrir la mesa). El campo
           default=1 se setea al crear la comanda; el mozo lo ajusta acá
           con los botones +/- (44x44 touch target) o tipeando directo.
           Validación de guest_count >= 1 al enviar a cocina (B.5), no
           al abrir: FILOSOFIA.md "confirmaciones solo para acciones
           irreversibles". Abrir mesa no lo es. -->
      <VCol cols="12">
        <div class="d-flex align-center ga-2">
          <span class="text-body-2 flex-grow-1">{{ t('order::attributes.orders.guest_count') }}</span>
          <VBtn
            aria-label="decrement guest count"
            :disabled="processing || Number(form.meta.guestCount) <= 1"
            icon="tabler-minus"
            size="default"
            variant="tonal"
            @click="form.meta.guestCount = Math.max(1, Number(form.meta.guestCount ?? 1) - 1)"
          />
          <VTextField
            v-model.number="form.meta.guestCount"
            class="guest-count-input"
            hide-details
            min="1"
            :readonly="processing"
            type="number"
            variant="outlined"
          />
          <VBtn
            aria-label="increment guest count"
            :disabled="processing"
            icon="tabler-plus"
            size="default"
            variant="tonal"
            @click="form.meta.guestCount = Number(form.meta.guestCount ?? 0) + 1"
          />
        </div>
      </VCol>
    </VRow>
  </div>
</template>

<style lang="scss" scoped>
.guest-count-input {
  max-width: 80px;
  :deep(input) {
    text-align: center;
    font-weight: 600;
  }
}
</style>
