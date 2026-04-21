<script lang="ts" setup>
  import { useI18n } from 'vue-i18n'

  const props = defineProps<{
    modelValue: string
    label?: string
    disabled?: boolean
    error?: boolean
    errorMessages?: string | string[] | null
  }>()

  const emit = defineEmits<{
    'update:modelValue': [value: string]
  }>()

  const { t } = useI18n()

  const colorValue = computed({
    get: () => props.modelValue,
    set: value => emit('update:modelValue', value),
  })
</script>

<template>
  <label
    class="color-picker-field"
    :class="{
      'color-picker-field--disabled': disabled,
      'color-picker-field--error': error,
    }"
  >
    <input
      v-model="colorValue"
      class="color-picker-field__native-input"
      :disabled="disabled"
      type="color"
    >
    <div class="color-picker-field__content">
      <span class="color-picker-field__caption">{{ label || 'Color' }}</span>
      <p class="color-picker-field__hint mb-0">
        {{ disabled ? t('admin::admin.inactive') : t('admin::admin.pick_a_color') }}
      </p>
    </div>

    <div class="color-picker-field__side">
      <code class="color-picker-field__value">{{ colorValue }}</code>
      <span class="color-picker-field__preview">
        <span class="color-picker-field__swatch-bg" />
        <span class="color-picker-field__swatch-fill" :style="{ backgroundColor: colorValue }" />
      </span>
      <VIcon class="color-picker-field__icon" icon="tabler-chevron-down" size="16" />
    </div>
  </label>

  <div v-if="errorMessages" class="text-error text-caption mt-2">
    {{ Array.isArray(errorMessages) ? errorMessages[0] : errorMessages }}
  </div>
</template>

<style lang="scss" scoped>
.color-picker-field {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 14px;
  min-height: 58px;
  padding: 10px 14px;
  border: 1px solid rgba(var(--v-border-color), var(--v-border-opacity));
  border-radius: 10px;
  background: rgba(var(--v-theme-surface), 1);
  cursor: pointer;
  transition: border-color 0.2s ease, box-shadow 0.2s ease, opacity 0.2s ease, background-color 0.2s ease;
}

.color-picker-field:hover:not(.color-picker-field--disabled) {
  border-color: rgba(var(--v-theme-primary), 0.28);
  background: rgba(var(--v-theme-primary), 0.025);
}

.color-picker-field--error {
  border-color: rgb(var(--v-theme-error));
}

.color-picker-field--disabled {
  opacity: 0.65;
  cursor: not-allowed;
}

.color-picker-field__preview {
  position: relative;
  flex: 0 0 30px;
  width: 30px;
  height: 30px;
}

.color-picker-field__swatch-bg,
.color-picker-field__swatch-fill {
  position: absolute;
  inset: 0;
  border-radius: 999px;
}

.color-picker-field__native-input {
  position: absolute;
  width: 1px;
  height: 1px;
  opacity: 0;
  pointer-events: none;
}

.color-picker-field__swatch-bg {
  background-image: linear-gradient(45deg, rgba(148, 163, 184, 0.16) 25%, transparent 25%),
  linear-gradient(-45deg, rgba(148, 163, 184, 0.16) 25%, transparent 25%),
  linear-gradient(45deg, transparent 75%, rgba(148, 163, 184, 0.16) 75%),
  linear-gradient(-45deg, transparent 75%, rgba(148, 163, 184, 0.16) 75%);
  background-position: 0 0, 0 6px, 6px -6px, -6px 0;
  background-size: 12px 12px;
  box-shadow: inset 0 0 0 1px rgba(15, 23, 42, 0.08);
}

.color-picker-field__swatch-fill {
  inset: 3px;
  box-shadow: inset 0 0 0 1px rgba(15, 23, 42, 0.06);
}

.color-picker-field__content {
  min-width: 0;
  flex: 1 1 auto;
  display: flex;
  flex-direction: column;
  gap: 2px;
}

.color-picker-field__caption {
  font-size: 0.84rem;
  font-weight: 700;
  color: rgba(var(--v-theme-on-surface), 0.9);
}

.color-picker-field__value {
  display: inline-flex;
  align-items: center;
  min-height: 24px;
  padding: 0 8px;
  border: 1px solid rgba(var(--v-border-color), var(--v-border-opacity));
  border-radius: 6px;
  background: rgba(var(--v-theme-on-surface), 0.03);
  color: rgb(var(--v-theme-on-surface));
  font-size: 0.72rem;
  font-weight: 700;
}

.color-picker-field__hint {
  font-size: 0.72rem;
  color: rgba(var(--v-theme-on-surface), 0.58);
}

.color-picker-field__side {
  display: flex;
  align-items: center;
  gap: 10px;
  flex-shrink: 0;
}

.color-picker-field__icon {
  color: rgba(var(--v-theme-on-surface), 0.45);
}
</style>
