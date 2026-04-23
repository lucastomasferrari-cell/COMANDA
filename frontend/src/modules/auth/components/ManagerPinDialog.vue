<script lang="ts" setup>
  import { computed, nextTick, ref, watch } from 'vue'
  import { useI18n } from 'vue-i18n'
  import { useToast } from 'vue-toastification'
  import { verifyManagerPin } from '@/modules/auth/api/managerPin.api.ts'
  import { useAuth } from '@/modules/auth/composables/auth.ts'

  const props = defineProps<{
    modelValue: boolean
    actionContext?: string
    subtitle?: string
    // El userId por default es el usuario logueado. Si luego agregamos
    // selección de "qué manager está aprobando" se pasa desde afuera.
    userId?: number | null
  }>()

  const emit = defineEmits<{
    (e: 'update:modelValue', value: boolean): void
    (e: 'approved', token: string): void
    (e: 'cancelled'): void
  }>()

  const { t } = useI18n()
  const toast = useToast()
  const { user: currentUser } = useAuth()

  const open = computed({
    get: () => props.modelValue,
    set: v => emit('update:modelValue', v),
  })

  const pin = ref<string>('')
  const verifying = ref(false)
  const errorMessage = ref<string>('')

  const resolvedUserId = computed(() => props.userId ?? currentUser?.id ?? null)

  watch(open, val => {
    if (val) {
      pin.value = ''
      errorMessage.value = ''
    }
  })

  function append (digit: string) {
    if (verifying.value) return
    if (pin.value.length >= 6) return
    pin.value += digit
    errorMessage.value = ''
    if (pin.value.length >= 4) {
      // Auto-verify a los 4 dígitos; si falla, seguís tipeando hasta 6.
      nextTick(() => trySubmit())
    }
  }

  function backspace () {
    if (verifying.value) return
    pin.value = pin.value.slice(0, -1)
    errorMessage.value = ''
  }

  async function trySubmit () {
    if (!resolvedUserId.value || pin.value.length < 4 || verifying.value) return
    verifying.value = true
    try {
      const res = await verifyManagerPin({
        user_id: resolvedUserId.value,
        pin: pin.value,
        action_context: props.actionContext,
      })
      const token = res.data.body?.token
      if (token) {
        emit('approved', token)
        open.value = false
      }
    } catch (err: any) {
      const status = err?.response?.status
      const msg = err?.response?.data?.message
      errorMessage.value = msg || (status === 423
        ? t('user::messages.manager_pin_locked_out')
        : t('user::messages.manager_pin_incorrect'))
      pin.value = ''
    } finally {
      verifying.value = false
    }
  }

  function cancel () {
    emit('cancelled')
    open.value = false
  }

  const digits = ['1', '2', '3', '4', '5', '6', '7', '8', '9']
</script>

<template>
  <VDialog v-model="open" max-width="360" persistent>
    <VCard class="pa-4 pin-card">
      <div class="d-flex align-center justify-space-between mb-3">
        <div class="d-flex align-center ga-2">
          <VIcon color="warning" icon="tabler-shield-lock" size="22" />
          <h3 class="text-h6">{{ t('user::auth.manager_pin_title') }}</h3>
        </div>
        <VBtn density="compact" icon="tabler-x" variant="text" @click="cancel" />
      </div>
      <p v-if="subtitle" class="text-body-2 text-medium-emphasis mb-3 text-center">
        {{ subtitle }}
      </p>

      <div class="pin-dots d-flex justify-center ga-2 mb-4">
        <div
          v-for="i in 6"
          :key="i"
          class="pin-dot"
          :class="{ filled: pin.length >= i }"
        />
      </div>

      <div v-if="errorMessage" class="text-error text-caption text-center mb-2">
        {{ errorMessage }}
      </div>

      <div class="numpad">
        <button
          v-for="d in digits"
          :key="d"
          class="num-key"
          type="button"
          :disabled="verifying"
          @click="append(d)"
        >{{ d }}</button>
        <button class="num-key placeholder" type="button" disabled />
        <button class="num-key" type="button" :disabled="verifying" @click="append('0')">0</button>
        <button
          class="num-key"
          type="button"
          aria-label="backspace"
          :disabled="verifying"
          @click="backspace"
        >
          <VIcon icon="tabler-backspace" />
        </button>
      </div>

      <div v-if="verifying" class="d-flex justify-center mt-3">
        <VProgressCircular color="primary" indeterminate size="24" />
      </div>
    </VCard>
  </VDialog>
</template>

<style lang="scss" scoped>
.pin-card {
  border-radius: 20px;
}

.pin-dots {
  padding: 0.5rem;
}

.pin-dot {
  width: 14px;
  height: 14px;
  border-radius: 50%;
  border: 2px solid rgba(var(--v-theme-on-surface), 0.25);
  background: transparent;
  transition: background 0.15s ease;
}

.pin-dot.filled {
  background: rgb(var(--v-theme-primary));
  border-color: rgb(var(--v-theme-primary));
}

.numpad {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 0.5rem;
}

.num-key {
  all: unset;
  box-sizing: border-box;
  cursor: pointer;
  text-align: center;
  font-size: 1.5rem;
  font-weight: 600;
  padding: 1rem 0;
  border-radius: 12px;
  background: rgba(var(--v-theme-on-surface), 0.04);
  transition: background 0.12s ease, transform 0.08s ease;
  color: rgb(var(--v-theme-on-surface));
}

.num-key:hover:not(:disabled) {
  background: rgba(var(--v-theme-on-surface), 0.08);
}

.num-key:active:not(:disabled) {
  transform: scale(0.97);
  background: rgba(var(--v-theme-primary), 0.12);
}

.num-key:disabled {
  cursor: not-allowed;
  opacity: 0.5;
}

.num-key.placeholder {
  visibility: hidden;
}
</style>
