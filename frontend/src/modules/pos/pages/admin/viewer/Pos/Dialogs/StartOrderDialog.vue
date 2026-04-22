<script lang="ts" setup>
  import { computed } from 'vue'
  import { useI18n } from 'vue-i18n'

  const props = defineProps<{
    modelValue: boolean
  }>()

  const emit = defineEmits<{
    (e: 'update:modelValue', value: boolean): void
    (e: 'quick'): void
    (e: 'open-table-viewer'): void
  }>()

  const { t } = useI18n()

  const open = computed({
    get: () => props.modelValue,
    set: v => emit('update:modelValue', v),
  })

  const pickQuick = () => {
    open.value = false
    emit('quick')
  }

  const pickTable = () => {
    open.value = false
    emit('open-table-viewer')
  }
</script>

<template>
  <VDialog v-model="open" max-width="440" persistent>
    <VCard class="start-order-card pa-2">
      <VCardText>
        <div class="d-flex align-center justify-space-between mb-4">
          <h3 class="text-h6">{{ t('pos::pos_viewer.start_order_dialog.title') }}</h3>
          <VBtn
            density="compact"
            icon="tabler-x"
            variant="text"
            @click="open = false"
          />
        </div>

        <div class="d-flex flex-column ga-3">
          <button type="button" class="start-cta" @click="pickTable">
            <div class="cta-icon">
              <VIcon color="primary" icon="tabler-brand-airtable" size="32" />
            </div>
            <div class="cta-body">
              <div class="cta-title">{{ t('pos::pos_viewer.start_order_dialog.table.title') }}</div>
              <div class="cta-subtitle">{{ t('pos::pos_viewer.start_order_dialog.table.subtitle') }}</div>
            </div>
            <VIcon icon="tabler-chevron-right" size="20" />
          </button>

          <button type="button" class="start-cta" @click="pickQuick">
            <div class="cta-icon cta-icon-amber">
              <VIcon color="warning" icon="tabler-bolt" size="32" />
            </div>
            <div class="cta-body">
              <div class="cta-title">{{ t('pos::pos_viewer.start_order_dialog.quick.title') }}</div>
              <div class="cta-subtitle">{{ t('pos::pos_viewer.start_order_dialog.quick.subtitle') }}</div>
            </div>
            <VIcon icon="tabler-chevron-right" size="20" />
          </button>
        </div>
      </VCardText>
    </VCard>
  </VDialog>
</template>

<style lang="scss" scoped>
.start-order-card {
  border-radius: 16px;
}

.start-cta {
  all: unset;
  box-sizing: border-box;
  cursor: pointer;
  display: flex;
  align-items: center;
  gap: 0.9rem;
  padding: 0.9rem;
  border: 1px solid rgba(var(--v-theme-on-surface), 0.12);
  border-radius: 12px;
  transition: border-color 0.15s ease, background-color 0.15s ease, transform 0.1s ease;

  &:hover {
    border-color: rgb(var(--v-theme-primary));
    background: rgba(var(--v-theme-primary), 0.04);
  }

  &:active {
    transform: scale(0.99);
  }

  &:focus-visible {
    outline: 2px solid rgba(var(--v-theme-primary), 0.6);
    outline-offset: 2px;
  }
}

.cta-icon {
  flex-shrink: 0;
  width: 56px;
  height: 56px;
  border-radius: 12px;
  background: rgba(var(--v-theme-primary), 0.08);
  display: flex;
  align-items: center;
  justify-content: center;
}

.cta-icon-amber {
  background: rgba(var(--v-theme-warning), 0.12);
}

.cta-body {
  flex: 1;
  min-width: 0;
}

.cta-title {
  font-weight: 600;
  font-size: 1rem;
  line-height: 1.2;
}

.cta-subtitle {
  font-size: 0.825rem;
  color: rgba(var(--v-theme-on-surface), 0.65);
  margin-top: 0.25rem;
}
</style>
