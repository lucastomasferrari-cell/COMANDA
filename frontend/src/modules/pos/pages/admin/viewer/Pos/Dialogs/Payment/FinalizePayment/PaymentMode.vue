<script lang="ts" setup>
  import { computed } from 'vue'
  import { useI18n } from 'vue-i18n'

  const props = defineProps<{
    meta: Record<string, any>
    form: Record<string, any>
  }>()

  const { t } = useI18n()
  const paymentMode = computed({
    get: () => props.form.state?.payment_mode,
    set: val => {
      if (!props.form.loading.value) {
        props.form.state.payment_mode = val
      }
    },
  })
</script>

<template>
  <h4>{{ t('order::orders.payment_mode') }}</h4>
  <div class="payment-modes-container ga-4 mb-4 mt-4">
    <div
      v-for="mode in meta.paymentModes"
      :key="mode.id"
      class="payment-modes-card"
      :class="{ active:mode.id === paymentMode }"
      @click="paymentMode = mode.id"
    >
      <div class="payment-modes-info">
        <VIcon :color="mode.color" :icon="mode.icon" size="23" />
        <span class="name">{{ mode.name }}</span>
      </div>
      <div class="checkbox">
        <v-icon v-if="mode.id === paymentMode" color="white" icon="tabler-check" size="20" />
      </div>
    </div>
  </div>
</template>

<style lang="scss" scoped>

.payment-modes-container {
  display: flex;
}

.payment-modes-card {
  border: 1px dashed #e0e0e0;
  border-radius: 10px;
  padding: 12px 16px;
  display: flex;
  align-items: center;
  justify-content: space-between;
  cursor: pointer;
  transition: all 0.25s ease;
  position: relative;
  width: 35%;
}

.payment-modes-info {
  display: flex;
  align-items: center;
  gap: 10px;
}

.payment-modes-card .name {
  font-size: 0.9rem;
  font-weight: 700;
}

.payment-modes-card .checkbox {
  height: 19px;
  width: 19px;
  border-radius: 50%;
  border: 1px solid #e0e0e0;
  display: flex;
  align-items: center;
  justify-content: center;
}

.payment-modes-card.active {
  border-color: rgb(var(--v-theme-primary));
  background-color: rgba(var(--v-theme-primary), 0.05);
}

.payment-modes-card.active .checkbox {
  background-color: rgb(var(--v-theme-primary));
  border-color: rgb(var(--v-theme-primary));
}
</style>
