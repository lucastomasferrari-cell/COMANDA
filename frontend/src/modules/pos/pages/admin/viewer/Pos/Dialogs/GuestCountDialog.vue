<script lang="ts" setup>
  import { computed, nextTick, ref, watch } from 'vue'
  import { useI18n } from 'vue-i18n'

  const props = defineProps<{
    modelValue: boolean
    initial?: number
  }>()

  const emit = defineEmits<{
    (e: 'update:modelValue', value: boolean): void
    (e: 'confirm', guestCount: number): void
    (e: 'cancel'): void
  }>()

  const { t } = useI18n()

  const open = computed({
    get: () => props.modelValue,
    set: v => emit('update:modelValue', v),
  })

  const guestCount = ref<number>(props.initial ?? 1)
  const inputRef = ref<any>(null)

  watch(open, async val => {
    if (val) {
      guestCount.value = props.initial ?? 1
      await nextTick()
      inputRef.value?.focus?.()
    }
  })

  const increment = () => { guestCount.value = Math.min(guestCount.value + 1, 99) }
  const decrement = () => { guestCount.value = Math.max(guestCount.value - 1, 1) }

  const onConfirm = () => {
    const n = Math.max(1, Math.round(Number(guestCount.value) || 1))
    emit('confirm', n)
    open.value = false
  }

  const onCancel = () => {
    emit('cancel')
    open.value = false
  }
</script>

<template>
  <VDialog v-model="open" max-width="360" persistent>
    <VCard class="guest-count-card">
      <VCardText class="pa-5">
        <div class="text-center mb-4">
          <div class="guest-icon-wrap mb-3">
            <VIcon color="primary" icon="tabler-users" size="32" />
          </div>
          <h3 class="text-h6">{{ t('pos::pos_viewer.guest_count_dialog.title') }}</h3>
          <p class="text-caption text-medium-emphasis mt-1">
            {{ t('pos::pos_viewer.guest_count_dialog.subtitle') }}
          </p>
        </div>

        <div class="d-flex align-center justify-center ga-3 mb-4">
          <VBtn
            aria-label="decrement"
            density="default"
            icon="tabler-minus"
            size="large"
            variant="tonal"
            @click="decrement"
          />
          <VTextField
            ref="inputRef"
            v-model.number="guestCount"
            class="guest-input text-h4 font-weight-bold"
            hide-details
            min="1"
            max="99"
            type="number"
            variant="plain"
            @keydown.enter="onConfirm"
          />
          <VBtn
            aria-label="increment"
            density="default"
            icon="tabler-plus"
            size="large"
            variant="tonal"
            @click="increment"
          />
        </div>
      </VCardText>

      <!-- Footer de 2 botones 50/50 — antes estaba dentro de VCardText con
           2 VBtn block side-by-side, cada uno width:100% → el confirmar
           se cortaba fuera del dialog (max-width 360 - pa-5 = 320 útil,
           2x100% + gap no entra). Ahora VCardActions con flex 1 a cada btn
           (sin block) los reparte equitativamente. -->
      <VCardActions class="px-5 pb-5 pt-0 ga-2">
        <VBtn
          class="flex-1"
          color="grey-500"
          size="large"
          variant="tonal"
          @click="onCancel"
        >
          {{ t('pos::pos_viewer.guest_count_dialog.cancel') }}
        </VBtn>
        <VBtn
          class="flex-1"
          color="primary"
          size="large"
          @click="onConfirm"
        >
          {{ t('pos::pos_viewer.guest_count_dialog.confirm') }}
        </VBtn>
      </VCardActions>
    </VCard>
  </VDialog>
</template>

<style lang="scss" scoped>
.guest-count-card {
  border-radius: 16px;
}

.guest-icon-wrap {
  width: 64px;
  height: 64px;
  border-radius: 50%;
  background: rgba(var(--v-theme-primary), 0.1);
  display: inline-flex;
  align-items: center;
  justify-content: center;
}

.guest-input :deep(input) {
  text-align: center;
  font-size: 2.25rem;
  font-weight: 700;
  width: 96px;
}

.guest-input :deep(input::-webkit-outer-spin-button),
.guest-input :deep(input::-webkit-inner-spin-button) {
  -webkit-appearance: none;
  margin: 0;
}

.flex-1 {
  flex: 1 1 0;
  min-width: 0;
}
</style>
