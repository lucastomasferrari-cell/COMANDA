<script lang="ts" setup>
  import type { RouteLocationRaw } from 'vue-router'
  import { onBeforeMount, ref, watch } from 'vue'
  import { useI18n } from 'vue-i18n'
  import { useRouter } from 'vue-router'
  import { useForm } from '@/modules/core/composables/form.ts'
  import { usePaymentMethod } from '@/modules/payment/composables/paymentMethod.ts'

  const props = defineProps<{
    item?: Record<string, any> | null
    action: 'update' | 'create'
  }>()

  const { t } = useI18n()
  const { getFormMeta, update, store } = usePaymentMethod()
  const router = useRouter()

  const form = useForm({
    name: props.item?.name ?? '',
    type: props.item?.type?.id ?? 'cash',
    impacts_cash: props.item?.impacts_cash ?? false,
    is_active: props.item?.is_active ?? true,
    order: props.item?.order ?? 0,
  })

  const meta = ref({ types: [] as Record<string, any>[] })

  // Al cambiar el tipo a "cash" auto-marcamos impacts_cash. Si el user
  // después lo destilda manualmente, respetamos su decisión (no se
  // re-fuerza). El watch fue agregado post-Bloque 3 porque el usecase
  // típico es: el cajero elige "Efectivo" esperando que el default
  // cuadre — no querés que tenga que pensar en el checkbox.
  watch(() => form.state.type, (newType, oldType) => {
    if (oldType === undefined) return
    if (newType === 'cash') {
      form.state.impacts_cash = true
    } else if (oldType === 'cash') {
      form.state.impacts_cash = false
    }
  })

  const submit = async () => {
    if (
      !form.loading.value
      && await form.submit(() => props.action == 'create' ? store(form.state) : update(props.item?.id, form.state))
    ) {
      await router.push({ name: 'admin.configuracion.operacion.formas' } as unknown as RouteLocationRaw)
    }
  }

  const goBack = async () => {
    await router.push({ name: 'admin.configuracion.operacion.formas' } as unknown as RouteLocationRaw)
  }

  onBeforeMount(async () => {
    try {
      const response = (await getFormMeta()).data.body
      meta.value.types = response.types
    } catch {
      /* Empty */
    }
  })
</script>

<template>
  <BaseForm
    :action="action"
    :loading="form.loading.value"
    :on-click-cancel="goBack"
    resource="payment_methods"
    @submit="submit"
  >
    <VRow>
      <VCol cols="12" md="8">
        <VCard>
          <VCardTitle class="d-flex justify-space-between align-center mb-2">
            <div class="d-flex align-center">
              <VIcon class="me-2" icon="tabler-credit-card" size="20" />
              <span>{{ t('payment::payment_methods.form.title') }}</span>
            </div>
          </VCardTitle>
          <VCardText>
            <VRow>
              <VCol cols="12">
                <VTextField
                  v-model="form.state.name"
                  :error="!!form.errors.value?.name"
                  :error-messages="form.errors.value?.name"
                  :label="t('payment::attributes.payment_methods.name')"
                />
              </VCol>
              <VCol cols="12">
                <VSelect
                  v-model="form.state.type"
                  :error="!!form.errors.value?.type"
                  :error-messages="form.errors.value?.type"
                  item-title="name"
                  item-value="id"
                  :items="meta.types"
                  :label="t('payment::attributes.payment_methods.type')"
                />
              </VCol>
              <VCol cols="12">
                <VCheckbox
                  v-model="form.state.impacts_cash"
                  :hint="t('payment::payment_methods.form.impacts_cash_hint')"
                  :label="t('payment::attributes.payment_methods.impacts_cash')"
                  persistent-hint
                />
              </VCol>
              <VCol v-if="action !== 'create'" cols="12">
                <VCheckbox
                  v-model="form.state.is_active"
                  :label="t('payment::attributes.payment_methods.is_active')"
                />
              </VCol>
            </VRow>
          </VCardText>
        </VCard>
      </VCol>
    </VRow>
  </BaseForm>
</template>
