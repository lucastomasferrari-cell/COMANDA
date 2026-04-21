<script lang="ts" setup>
  import type { AxiosError } from 'axios'
  import type { useQintrix } from '@/modules/printer/composables/qintrix.ts'
  import { useI18n } from 'vue-i18n'
  import { useToast } from 'vue-toastification'
  import { useOrder } from '@/modules/sale/composables/order.ts'

  const props = defineProps<{
    orderId: string | number
    content: Record<string, any>
    registerId: number | null
    qintrix: ReturnType<typeof useQintrix>
  }>()
  const { t } = useI18n()
  const { printPreview } = useOrder()
  const toast = useToast()

  const printLoading = ref(false)
  const previewLoading = ref(false)

  async function directPrint () {
    try {
      printLoading.value = true
      const contentType = 'pdf'
      const response = (await printPreview(
        props.orderId,
        props.content.type.id,
        props.content?.id,
        props.registerId,
        contentType,
      )).data.body
      if (props.qintrix.isSetup.value) {
        await props.qintrix.createJob(
          response.content,
          response.printer.qintrix_id,
          response.printer.paper_size,
          `${props.orderId}`,
          `order-${props.content.type.id}`,
          contentType,
        )
      }
    } catch (error) {
      toast.error((error as AxiosError<{
        message?: string
      }>).response?.data?.message || t('core::errors.an_unexpected_error_occurred'))
    } finally {
      printLoading.value = false
    }
  }

  async function previewPrint () {
    let printWindow: Window | null = null

    try {
      previewLoading.value = true

      const response = await printPreview(
        props.orderId,
        props.content.type.id,
        props.content?.id,
        props.registerId,
        'html',
      )

      const html = response.data.body.content

      printWindow = window.open('', '_blank', 'width=800,height=600')
      if (!printWindow) return

      const doc = printWindow.document

      doc.open()
      doc.append(doc.createElement('html'))
      doc.documentElement.innerHTML = html
      doc.close()

      const closeAfterPrint = () => {
        printWindow?.close()
      }

      printWindow.addEventListener('afterprint', closeAfterPrint)

      printWindow.addEventListener('load', () => {
        printWindow!.focus()
        printWindow!.print()
      })
    } catch (error) {
      toast.error(
        (error as AxiosError<{
          message?: string
        }>).response?.data?.message || t('core::errors.an_unexpected_error_occurred'),
      )

      printWindow?.close()
    } finally {
      previewLoading.value = false
    }
  }

</script>

<template>
  <VListItem class="pl-0 pr-0">
    <VListItemTitle class="font-weight-bold">
      {{ content.label }}
    </VListItemTitle>

    <VListItemSubtitle class="text-medium-emphasis">
      {{ content.type.name }}
    </VListItemSubtitle>
    <template #append>
      <div class="d-flex ga-2">
        <VTooltip :text="t('order::orders.preview_print')">
          <template #activator="{ props:tooltipProps }">
            <VBtn
              color="secondary"
              :disabled="previewLoading"
              icon
              :loading="previewLoading"
              size="large"
              v-bind="tooltipProps"
              variant="tonal"
              @click="previewPrint"
            >
              <VIcon icon="tabler-eye" />
            </VBtn>
          </template>
        </VTooltip>
        <VTooltip :text="t('order::orders.direct_print')">
          <template #activator="{ props:tooltipProps }">
            <VBtn
              :disabled="(!registerId && content.type.id != 'kitchen') || printLoading || !qintrix.isSetup.value"
              icon
              :loading="printLoading"
              size="large"
              v-bind="tooltipProps"
              variant="tonal"
              @click="directPrint"
            >
              <VIcon icon="tabler-printer" />
            </VBtn>
          </template>
        </VTooltip>
      </div>
    </template>
  </VListItem>
</template>
