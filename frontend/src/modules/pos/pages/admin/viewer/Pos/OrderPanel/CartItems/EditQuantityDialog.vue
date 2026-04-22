<script lang="ts" setup>
  import { computed, nextTick, ref, watch } from 'vue'
  import { useI18n } from 'vue-i18n'

  const props = defineProps<{
    modelValue: boolean
    initial?: number
  }>()

  const emit = defineEmits<{
    (e: 'update:modelValue', value: boolean): void
    (e: 'confirm', qty: number): void
  }>()

  const { t } = useI18n()

  const open = computed({
    get: () => props.modelValue,
    set: v => emit('update:modelValue', v),
  })

  const qty = ref<number>(props.initial ?? 1)
  const inputRef = ref<any>(null)

  watch(open, async val => {
    if (val) {
      qty.value = props.initial ?? 1
      await nextTick()
      inputRef.value?.focus?.()
    }
  })

  const onConfirm = () => {
    const n = Math.max(1, Math.round(Number(qty.value) || 1))
    emit('confirm', n)
    open.value = false
  }
</script>

<template>
  <VDialog v-model="open" max-width="320" persistent>
    <VCard class="pa-2">
      <VCardText>
        <div class="text-center mb-3">
          <h3 class="text-subtitle-1 font-weight-medium">
            {{ t('pos::pos_viewer.item_menu.edit_qty_title') }}
          </h3>
        </div>
        <VTextField
          ref="inputRef"
          v-model.number="qty"
          autofocus
          hide-details
          min="1"
          type="number"
          @keydown.enter="onConfirm"
        />
        <div class="d-flex ga-2 mt-4">
          <VBtn block color="grey-500" variant="tonal" @click="open = false">
            {{ t('pos::pos_viewer.item_menu.cancel') }}
          </VBtn>
          <VBtn block color="primary" @click="onConfirm">
            {{ t('pos::pos_viewer.item_menu.confirm') }}
          </VBtn>
        </div>
      </VCardText>
    </VCard>
  </VDialog>
</template>
