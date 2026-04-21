<script lang="ts" setup>
  import type { AxiosError } from 'axios'
  import { useI18n } from 'vue-i18n'
  import { useToast } from 'vue-toastification'
  import { useKitchenViewer } from '@/modules/pos/composables/kitchenViewer.ts'

  const props = defineProps<{
    orderId: string | number
    item: Record<string, any>
  }>()
  const emit = defineEmits(['refresh'])

  const { t } = useI18n()
  const { moveToNextStatus } = useKitchenViewer()
  const toast = useToast()

  const processing = ref(false)

  async function updateStatus () {
    try {
      processing.value = true
      const response = await moveToNextStatus(props.orderId, [props.item.id])
      emit('refresh')
      toast.success(response.data.message)
    } catch (error) {
      toast.error((error as AxiosError<{
        message?: string
      }>).response?.data?.message || t('core::errors.an_unexpected_error_occurred'))
    } finally {
      processing.value = false
    }
  }
</script>

<template>
  <div class="order-product-container">
    <div class="font-weight-medium">
      {{ item.quantity }}x {{ item.product.name }}
    </div>
    <ul v-if="item.options && item.options.length > 0" class="ml-4 text-caption ">
      <li
        v-for="opt in item.options"
        :key="opt.id"
      >
        {{ opt.name }}:
        <span v-for="val in opt.values" :key="val.id">
          {{ val.label || opt.value }}
        </span>
      </li>
    </ul>
    <div v-if="item.next_status" class="d-flex justify-end mt-2">
      <VBtn
        :color="item.next_status.color"
        :disabled="processing"
        :loading="processing"
        size="small"
        variant="tonal"
        @click="updateStatus"
      >
        <VIcon :icon="item.next_status.icon" start />
        {{ t(`pos::pos_viewer.kitchen_next_status.${item.status.id}`) }}
      </VBtn>
    </div>

  </div>
</template>

<style lang="scss" scoped>
.order-product-container {
  border: 1px dashed #ededed;
  margin-bottom: 0.5rem;
  padding: 0.5rem;
  border-radius: 5px;
}
</style>
