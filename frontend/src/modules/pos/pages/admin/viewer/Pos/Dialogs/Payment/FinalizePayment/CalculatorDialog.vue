<script lang="ts" setup>
  import { computed, ref, watch } from 'vue'
  import { useI18n } from 'vue-i18n'

  const props = defineProps<{
    modelValue: boolean
  }>()

  const emit = defineEmits<{
    (e: 'update:modelValue', value: boolean): void
    (e: 'apply', value: number): void
  }>()

  const { t } = useI18n()

  const dialogModel = computed({
    get: () => props.modelValue,
    set: (value: boolean) => emit('update:modelValue', value),
  })

  const expression = ref('0')
  const preview = ref<number | null>(0)
  const hasError = ref(false)

  const buttons = [
    { value: '7', label: '7' }, { value: '8', label: '8' }, { value: '9', label: '9' }, {
      value: '/',
      label: '÷',
    },
    { value: '4', label: '4' }, { value: '5', label: '5' }, { value: '6', label: '6' }, {
      value: '*',
      label: '×',
    },
    { value: '1', label: '1' }, { value: '2', label: '2' }, { value: '3', label: '3' }, {
      value: '-',
      label: '−',
    },
    { value: '0', label: '0' }, { value: '.', label: '.' }, { value: 'C', label: 'C' }, {
      value: '+',
      label: '+',
    },
  ]

  const sanitizedExpression = computed(() => expression.value.replace(/\s+/g, ''))
  const expressionLabel = computed(() => expression.value.replace(/\*/g, '×').replace(/\//g, '÷'))

  function evaluateExpression (): number | null {
    const expr = sanitizedExpression.value
    if (!/^[0-9+\-*/.()]+$/.test(expr)) {
      return null
    }

    try {
      const result = new Function(`"use strict"; return (${expr});`)()
      if (typeof result !== 'number' || Number.isNaN(result) || !Number.isFinite(result)) {
        return null
      }
      return result
    } catch {
      return null
    }
  }

  watch(expression, () => {
    const result = evaluateExpression()
    hasError.value = result == null
    preview.value = result
  })

  const isSubmitDisabled = computed(() => {
    const result = evaluateExpression()
    return result == null || result < 0
  })

  watch(
    () => props.modelValue,
    opened => {
      if (opened) {
        expression.value = '0'
        preview.value = 0
        hasError.value = false
      }
    },
  )

  function append (value: string) {
    if (value === 'C') {
      expression.value = '0'
      hasError.value = false
      preview.value = 0
      return
    }

    if (value === '.' && /(^|[+\-*/])[^+\-*/]*\./.test(expression.value)) {
      return
    }

    const next = expression.value === '0' && /[0-9.]/.test(value)
      ? (value === '.' ? '0.' : value)
      : `${expression.value}${value}`
    expression.value = next
  }

  function apply () {
    const result = evaluateExpression()
    if (result == null) {
      hasError.value = true
      return
    }

    emit('apply', result)
    dialogModel.value = false
  }
</script>

<template>
  <VDialog v-model="dialogModel" max-width="450">
    <VCard class="calculator-card">
      <VCardText class="calc-content">
        <div class="calc-display" :class="{ 'calc-error': hasError }">
          <div class="calc-expression">{{ expressionLabel }}</div>
          <div class="calc-preview" :class="{ 'calc-preview-error': hasError }">
            {{ hasError ? t('order::orders.invalid_expression') : preview }}
          </div>
        </div>

        <div class="calc-grid">
          <VBtn
            v-for="button in buttons"
            :key="button.value"
            class="calc-btn"
            :color="button.value==='C'?'error': (['+', '-', '*', '/'].includes(button.value)?'primary':'default')"
            variant="tonal"
            @click="append(button.value)"
          >
            {{ button.label }}
          </VBtn>
        </div>
      </VCardText>
      <VCardActions class="calc-actions">
        <VBtn
          color="default"
          size="large"
          @click="dialogModel = false"
        >
          {{ t('admin::admin.buttons.cancel') }}
        </VBtn>
        <VBtn :disabled="isSubmitDisabled" size="large" @click="apply">
          {{ t('order::orders.submit_result') }}
          <VIcon class="ms-1" icon="tabler-circle-check" size="18" />
        </VBtn>
      </VCardActions>
    </VCard>
  </VDialog>
</template>

<style lang="scss" scoped>
.calculator-card {
  --calc-surface: rgb(var(--v-theme-surface));
  --calc-surface-soft: rgba(var(--v-theme-on-surface), 0.025);
  --calc-outline: rgba(var(--v-theme-on-surface), 0.12);
  --calc-outline-strong: rgba(var(--v-theme-on-surface), 0.16);
  --calc-text: rgb(var(--v-theme-on-surface));
  --calc-muted: rgba(var(--v-theme-on-surface), 0.52);
  --calc-error: rgb(var(--v-theme-error));

  border: 1px solid var(--calc-outline);
  border-radius: 32px;
  overflow: hidden;
}

.calc-content {
  padding: 1.5rem 1.5rem 1rem;
}

.calc-display {
  border: 1px solid var(--calc-outline-strong);
  border-radius: 24px;
  padding: 1rem 1.2rem;
  min-height: 130px;
  display: flex;
  flex-direction: column;
  align-items: flex-end;
  justify-content: center;
  background: linear-gradient(
      180deg,
      rgba(var(--v-theme-surface), 0.95) 0%,
      rgba(var(--v-theme-surface), 1) 100%
  );
  margin-top: 1rem;
  box-shadow: inset 0 1px 0 rgba(var(--v-theme-on-surface), 0.04);
}

.calc-error {
  border-color: rgba(var(--v-theme-error), 0.45);
}

.calc-expression {
  color: var(--calc-muted);
  font-size: 1.25rem;
  font-weight: 700;
  min-height: 1.6rem;
  line-height: 1;
}

.calc-preview {
  color: var(--calc-text);
  font-size: 2.9rem;
  line-height: 1.05;
  font-weight: 800;
  margin-top: 0.2rem;
}

.calc-preview-error {
  color: var(--calc-error);
  font-size: 0.9rem;
  font-weight: 700;
}

.calc-grid {
  display: grid;
  grid-template-columns: repeat(4, minmax(0, 1fr));
  gap: 0.95rem;
  margin-top: 1.2rem;
}

.calc-btn {
  height: 69px;
  border-radius: 15px;
  font-size: 2rem;
  font-weight: 600;
  text-transform: none;
  letter-spacing: 0;
  min-width: 0;
  padding: 0;
  transition: none;
}

.calc-btn:hover {
  transform: none;
  box-shadow: 0 2px 6px rgba(var(--v-theme-on-surface), 0.12);
}

.calc-actions {
  padding: 0 1.5rem 1.5rem;
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 0.75rem;
}

@media (max-width: 700px) {
  .calc-display {
    min-height: 120px;
  }

  .calc-expression {
    font-size: 1.15rem;
    min-height: 1.4rem;
  }

  .calc-preview {
    font-size: 2.45rem;
  }

  .calc-btn {
    height: 68px;
    border-radius: 16px;
    font-size: 1.55rem;
  }

}
</style>
