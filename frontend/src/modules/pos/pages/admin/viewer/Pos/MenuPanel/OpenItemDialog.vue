<script lang="ts" setup>
  import { computed, nextTick, ref, watch } from 'vue'
  import { useI18n } from 'vue-i18n'
  import { useToast } from 'vue-toastification'
  import { storeCustomProduct } from '@/modules/sale/api/order.api.ts'

  const props = defineProps<{
    modelValue: boolean
    orderId: number | string | null
  }>()

  const emit = defineEmits<{
    (e: 'update:modelValue', value: boolean): void
    (e: 'saved', response: Record<string, any>): void
  }>()

  const { t } = useI18n()
  const toast = useToast()

  const open = computed({
    get: () => props.modelValue,
    set: v => emit('update:modelValue', v),
  })

  const form = ref({
    custom_name: '',
    custom_price: 0,
    custom_description: '',
    quantity: 1,
  })
  const saving = ref(false)
  const nameInputRef = ref<any>(null)

  watch(open, async val => {
    if (val) {
      form.value = { custom_name: '', custom_price: 0, custom_description: '', quantity: 1 }
      await nextTick()
      nameInputRef.value?.focus?.()
    }
  })

  const canSubmit = computed(() =>
    form.value.custom_name.trim().length > 0
    && Number(form.value.custom_price) >= 0
    && Number(form.value.quantity) >= 1,
  )

  async function submit () {
    if (!canSubmit.value || saving.value || props.orderId == null) return
    saving.value = true
    try {
      const res = await storeCustomProduct(props.orderId, {
        custom_name: form.value.custom_name.trim(),
        custom_price: Number(form.value.custom_price),
        custom_description: form.value.custom_description.trim() || undefined,
        quantity: Math.max(1, Math.round(Number(form.value.quantity) || 1)),
      })
      toast.success(res.data?.message ?? t('pos::pos_viewer.open_item.saved'))
      emit('saved', res.data.body)
      open.value = false
    } catch (err: any) {
      const message = err?.response?.data?.message
      const errors = err?.response?.data?.errors
      if (errors && typeof errors === 'object') {
        const first = Object.values(errors as Record<string, string[]>)[0]?.[0]
        toast.error(first || message || t('core::errors.an_unexpected_error_occurred'))
      } else {
        toast.error(message || t('core::errors.an_unexpected_error_occurred'))
      }
    } finally {
      saving.value = false
    }
  }
</script>

<template>
  <VDialog v-model="open" max-width="480" persistent>
    <VCard class="open-item-card pa-2">
      <VCardText>
        <div class="d-flex align-center mb-3">
          <VIcon class="me-2" color="primary" icon="tabler-edit" size="22" />
          <h3 class="text-h6">{{ t('pos::pos_viewer.open_item.title') }}</h3>
          <VSpacer />
          <VBtn density="compact" icon="tabler-x" variant="text" @click="open = false" />
        </div>

        <VTextField
          ref="nameInputRef"
          v-model="form.custom_name"
          autofocus
          class="mb-2"
          :label="t('pos::pos_viewer.open_item.name')"
          required
        />

        <div class="d-flex ga-2">
          <VTextField
            v-model.number="form.custom_price"
            class="flex-grow-1"
            :label="t('pos::pos_viewer.open_item.price')"
            min="0"
            step="0.01"
            type="number"
          />
          <VTextField
            v-model.number="form.quantity"
            :label="t('pos::pos_viewer.open_item.qty')"
            min="1"
            style="max-width: 120px;"
            type="number"
          />
        </div>

        <VTextarea
          v-model="form.custom_description"
          class="mt-1"
          :label="t('pos::pos_viewer.open_item.description')"
          rows="2"
        />

        <div class="d-flex ga-2 mt-4">
          <VBtn block color="grey-500" variant="tonal" @click="open = false">
            {{ t('pos::pos_viewer.open_item.cancel') }}
          </VBtn>
          <VBtn
            block
            color="primary"
            :disabled="!canSubmit"
            :loading="saving"
            @click="submit"
          >
            {{ t('pos::pos_viewer.open_item.add') }}
          </VBtn>
        </div>
      </VCardText>
    </VCard>
  </VDialog>
</template>

<style lang="scss" scoped>
.open-item-card {
  border-radius: 16px;
}
</style>
