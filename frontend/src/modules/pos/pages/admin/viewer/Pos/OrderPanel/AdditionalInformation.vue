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
      <VCol cols="12">
        <VTextField
          v-model="form.meta.guestCount"
          :label="t('order::attributes.orders.guest_count')"
          :readonly="processing"
        />
      </VCol>
    </VRow>
  </div>
</template>
