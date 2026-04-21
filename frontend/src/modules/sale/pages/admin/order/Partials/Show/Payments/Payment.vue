<script lang="ts" setup>
  import Money from '@/modules/core/components/Money.vue'
  import { convertHexToRgba } from '@/modules/core/utils/color.ts'

  defineProps<{
    payment: Record<string, any>
    border?: boolean
  }>()
</script>

<template>
  <div class="d-flex align-center justify-space-between py-1" :class="{'border-b-dashed':border}">
    <div class="d-flex align-center ">
      <div
        class="avatar"
        :style="{ backgroundColor: convertHexToRgba(payment.method.color,0.15) }"
      >
        <VIcon :color="payment.method.color" :icon="payment.method.icon" />
      </div>

      <div class="ma-3">
        <div class="text-body-2 font-weight-bold text-grey-darken-1">
          {{ payment.method.name }}
          <span v-if="payment.type.id =='refund'" class="text-error">({{
            payment.type.name
          }})</span>
        </div>
        <div v-if="payment.transaction_id" class="text-caption text-grey-darken-2">
          {{ payment.transaction_id }}
        </div>
      </div>
    </div>

    <div>
      <div class="text-end" :class="{'text-error':payment.type.id =='refund'}">
        <Money :money="payment.amount" />
      </div>
      <div class="text-caption text-grey-darken-2">
        <small>{{ payment.created_at }}</small>
      </div>
    </div>
  </div>
</template>

<style scoped>
.avatar {
  width: 36px;
  height: 36px;
  border-radius: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
}
</style>
