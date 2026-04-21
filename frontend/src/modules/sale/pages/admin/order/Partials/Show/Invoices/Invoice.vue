<script lang="ts" setup>
  import { useI18n } from 'vue-i18n'
  import Money from '@/modules/core/components/Money.vue'
  import { useInvoice } from '@/modules/sale/composables/invoice.ts'

  const props = defineProps<{ invoice: Record<string, any> }>()

  const { t } = useI18n()
  const { download, print } = useInvoice()

  const isCreditNote = computed(() => props.invoice.invoice_kind.id === 'credit_note')
</script>

<template>
  <div class="invoice-card  mb-2">
    <div class=" d-flex align-center justify-space-between">
      <div class="d-flex align-center ">
        <div>
          <div
            class="text-body-2 font-weight-bold text-grey-darken-1"
            :class="{'text-error':isCreditNote,'text-primary':!isCreditNote}"
          >
            {{
              t(`invoice::invoices.show.${isCreditNote ? 'credit_note' : 'invoice'}`).toUpperCase()
            }}
          </div>
          <div class="text-caption text-grey-darken-2">
            {{ invoice.invoice_number }}
          </div>
        </div>
      </div>
      <div>
        <div
          class="text-end"
          :class="{'text-error':isCreditNote,'text-primary':!isCreditNote}"
        >
          <Money :money="invoice.total" />
        </div>
        <div class="text-caption text-grey-darken-2">
          {{ invoice.issued_at }}
        </div>
      </div>
    </div>
    <div class="d-flex align-center justify-space-between ga-2 mt-3">
      <VBtn size="small" style="width: 50%" @click="print(invoice)">
        <VIcon icon="tabler-printer" start />
        {{ t('admin::admin.buttons.print') }}
      </VBtn>
      <VBtn color="secondary" size="small" style="width: 50%" @click="download(invoice)">
        <VIcon icon="tabler-download" start />
        {{ t('admin::admin.buttons.download') }}
      </VBtn>
    </div>
  </div>

</template>

<style lang="scss" scoped>
.invoice-card {
  border: 1px dashed #ededed;
  border-radius: 8px;
  padding: 0.7rem 0.8rem;
}
</style>
