<script lang="ts" setup>
  import { computed } from 'vue'
  import { useI18n } from 'vue-i18n'

  const props = defineProps<{
    form: Record<string, any>
    currency: string
  }>()
  const { t } = useI18n()

  const subTotal = computed(() => {
    return props.form.state.items.reduce((total: number, item: Record<string, any>) => {
      const quantity = Number(item.quantity) || 0
      const unitCost = Number(item.unit_cost) || 0
      return total + quantity * unitCost
    }, 0)
  })

  const total = computed(() => (subTotal.value - (Number(props.form.state.discount) || 0)) + (Number(props.form.state.tax) || 0))
</script>

<template>
  <div class="d-flex justify-end mt-4">
    <div class="w-25">
      <div class="d-flex align-center justify-space-between pb-2 w-100">
        <span class="font-weight-bold">
          {{ t('inventory::purchases.form.sub_total') }}:
        </span>
        <span>
          {{ currency }} {{ subTotal }}
        </span>
      </div>
      <div class="d-flex align-center justify-space-between pb-2 w-100">
        <span class="font-weight-bold">
          {{ t('inventory::attributes.purchases.discount') }}:
        </span>
        <span>
          {{ currency }} {{ form.state.discount || 0 }}
        </span>
      </div>
      <div class="d-flex align-center border-b justify-space-between pb-2 w-100">
        <span class="font-weight-bold">
          {{ t('inventory::attributes.purchases.tax') }}:
        </span>
        <span>
          {{ currency }} {{ form.state.tax || 0 }}
        </span>
      </div>
      <div class="d-flex align-center justify-space-between py-2 w-100">
        <span class="font-weight-bold">
          {{ t('inventory::purchases.form.total') }}:
        </span>
        <span>
          {{ currency }} {{ total }}
        </span>
      </div>
    </div>
  </div>
</template>
