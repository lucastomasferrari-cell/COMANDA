<script lang="ts" setup>
  import { useI18n } from 'vue-i18n'
  import { useAppStore } from '@/modules/core/stores/appStore.ts'

  defineProps<{
    invoice: Record<string, any>
  }>()

  const { t } = useI18n()
  const appStore = useAppStore()
</script>

<template>
  <div class="border-b-dashed pb-2 d-flex justify-space-between align-center">
    <div class="d-flex ga-2 align-center">
      <img
        v-if="appStore.logo"
        alt="logo"
        height="50px"
        :src="appStore.logo"
        width="50px"
      >
      <div>
        <div class="text-subtitle-1 font-weight-bold">
          {{ invoice.seller?.legal_name }} - {{ invoice.branch?.name }}
        </div>
        <div class="text-caption text-medium-emphasis">
          {{ invoice.seller?.email }}
        </div>
      </div>
    </div>

    <div>
      <div class="title font-weight-bold">
        {{
          t(`invoice::invoices.show.${invoice.invoice_kind.id === 'credit_note' ? 'credit_note' : 'invoice'}`).toUpperCase()
        }}
      </div>
      <div v-if="invoice.reference_invoice" class="text-caption mt-2 text-medium-emphasis">
        {{ invoice.reference_invoice.invoice_number }}
      </div>
    </div>
  </div>
</template>

<style lang="scss" scoped>
:deep(.logo svg) {
  width: 60px;
}

.title {
  font-size: 1.6rem;
}
</style>
