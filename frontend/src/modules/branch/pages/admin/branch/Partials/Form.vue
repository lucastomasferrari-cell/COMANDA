<script lang="ts" setup>
  import type { RouteLocationRaw } from 'vue-router'
  import { onBeforeMount, ref, watch } from 'vue'
  import { useI18n } from 'vue-i18n'
  import { useRouter } from 'vue-router'
  import { useBranch } from '@/modules/branch/composables/branch.ts'
  import { useForm } from '@/modules/core/composables/form.ts'

  const props = defineProps<{
    item?: Record<string, any> | null
    action: 'update' | 'create'
  }>()

  const { t } = useI18n()
  const { getFormMeta, update, store } = useBranch()
  const router = useRouter()

  const meta = ref({
    timezones: [],
    countries: [],
    currencies: [],
    orderTypes: [],
    paymentMethods: [],
  })
  const form = useForm({
    name: props.item?.name || {},
    registration_number: props.item?.registration_number,
    phone: props.item?.phone,
    email: props.item?.email,
    is_active: props.item?.is_active || false,
    country_code: props.item?.country_code,
    timezone: props.item?.timezone,
    currency: props.item?.currency,
    latitude: props.item?.latitude,
    longitude: props.item?.longitude,
    order_types: props.item?.order_types || [],
    payment_methods: props.item?.payment_methods || [],
    quick_pay_amounts: props.item?.quick_pay_amounts || [],
    cash_difference_threshold: props.item?.cash_difference_threshold?.amount,
    legal_name: props.item?.legal_name,
    vat_tin: props.item?.vat_tin,
    address_line1: props.item?.address_line1,
    address_line2: props.item?.address_line2,
    city: props.item?.city,
    state: props.item?.state,
    postal_code: props.item?.postal_code,
  })

  async function submit () {
    if (
      !form.loading.value
      && await form.submit(() => props.action == 'create' ? store(form.state) : update(props.item?.id, form.state))
    ) {
      await router.push({ name: 'admin.branches.index' } as unknown as RouteLocationRaw)
    }
  }

  const quickPayAmounts = ref<(number | string | null)[]>(
    Array.from({ length: 6 }, (_, index) => props.item?.quick_pay_amounts?.[index] ?? null),
  )

  watch(
    quickPayAmounts,
    values => {
      form.state.quick_pay_amounts = values
        .map(value => (value === '' || value == null ? null : Number(value)))
        .filter((value): value is number => value != null && !Number.isNaN(value) && value > 0)
        .slice(0, 6)
    },
    { immediate: true, deep: true },
  )

  onBeforeMount(async () => {
    try {
      const response = (await getFormMeta()).data.body
      meta.value.timezones = response.timezones
      meta.value.countries = response.countries
      meta.value.currencies = response.currencies
      meta.value.orderTypes = response.order_types
      meta.value.paymentMethods = response.payment_methods
    } catch {
    /* Empty */
    }
  })
</script>

<template>
  <BaseForm
    v-slot="{ currentLanguage }"
    :action="action"
    has-multiple-language
    :loading="form.loading.value"
    resource="branches"
    @submit="submit"
  >
    <VRow>
      <VCol cols="12" md="7">
        <VRow>
          <VCol cols="12">
            <VCard>
              <VCardTitle class="d-flex justify-space-between align-center mb-2">
                <div class="d-flex align-center">
                  <VIcon class="me-2" icon="tabler-info-circle" size="20" />
                  <span>{{ t('branch::branches.form.cards.branch_information') }}</span>
                </div>
              </VCardTitle>
              <VCardText>
                <VRow>
                  <VCol cols="12" md="6">
                    <VTextField
                      v-model="form.state.name[currentLanguage.id]"
                      :error="!!form.errors.value?.[`name.${currentLanguage.id}`]"
                      :error-messages="form.errors.value?.[`name.${currentLanguage.id}`]"
                      :label="t('branch::attributes.branches.name') + ` ( ${currentLanguage.name} )`"
                    />
                  </VCol>
                  <VCol cols="12" md="6">
                    <VTextField
                      v-model="form.state.legal_name"
                      :error="!!form.errors.value?.legal_name"
                      :error-messages="form.errors.value?.legal_name"
                      :label="t('branch::attributes.branches.legal_name')"
                    />
                  </VCol>
                  <VCol cols="12" md="6">
                    <VTextField
                      v-model="form.state.phone"
                      :error="!!form.errors.value?.phone"
                      :error-messages="form.errors.value?.phone"
                      :label="t('branch::attributes.branches.phone')"
                    />
                  </VCol>
                  <VCol cols="12" md="6">
                    <VTextField
                      v-model="form.state.email"
                      :error="!!form.errors.value?.email"
                      :error-messages="form.errors.value?.email"
                      :label="t('branch::attributes.branches.email')"
                      type="email"
                    />
                  </VCol>
                  <VCol cols="12">
                    <VCheckbox
                      v-model="form.state.is_active"
                      :label="t('branch::attributes.branches.is_active')"
                    />
                  </VCol>
                </VRow>
              </VCardText>
            </VCard>
          </VCol>
          <VCol cols="12">
            <VCard>
              <VCardTitle class="mb-2">
                <div class="d-flex align-center">
                  <VIcon class="me-2" icon="tabler-map-2" size="20" />
                  {{ t('branch::branches.form.cards.address_information') }}
                </div>
              </VCardTitle>
              <VCardText>
                <VRow>
                  <VCol cols="12" md="6">
                    <VSelect
                      v-model="form.state.country_code"
                      :error="!!form.errors.value?.country_code"
                      :error-messages="form.errors.value?.country_code"
                      item-title="name"
                      item-value="id"
                      :items="meta.countries"
                      :label="t('branch::attributes.branches.country_code')"
                    />
                  </VCol>
                  <VCol cols="12" md="6">
                    <VTextField
                      v-model="form.state.postal_code"
                      clearable
                      :error="!!form.errors.value?.postal_code"
                      :error-messages="form.errors.value?.postal_code"
                      :label="t('branch::attributes.branches.postal_code')"
                    />
                  </VCol>
                  <VCol cols="12">
                    <VTextField
                      v-model="form.state.address_line1"
                      :error="!!form.errors.value?.address_line1"
                      :error-messages="form.errors.value?.address_line1"
                      :label="t('branch::attributes.branches.address_line1')"
                    />
                  </VCol>
                  <VCol cols="12">
                    <VTextField
                      v-model="form.state.address_line2"
                      clearable
                      :error="!!form.errors.value?.address_line2"
                      :error-messages="form.errors.value?.address_line2"
                      :label="t('branch::attributes.branches.address_line2')"
                    />
                  </VCol>
                  <VCol cols="12" md="6">
                    <VTextField
                      v-model="form.state.city"
                      clearable
                      :error="!!form.errors.value?.city"
                      :error-messages="form.errors.value?.city"
                      :label="t('branch::attributes.branches.city')"
                    />
                  </VCol>
                  <VCol cols="12" md="6">
                    <VTextField
                      v-model="form.state.state"
                      clearable
                      :error="!!form.errors.value?.state"
                      :error-messages="form.errors.value?.state"
                      :label="t('branch::attributes.branches.state')"
                    />
                  </VCol>
                  <VCol cols="12" md="6">
                    <VTextField
                      v-model="form.state.latitude"
                      clearable
                      :error="!!form.errors.value?.latitude"
                      :error-messages="form.errors.value?.latitude"
                      :label="t('branch::attributes.branches.latitude')"
                    />
                  </VCol>
                  <VCol cols="12" md="6">
                    <VTextField
                      v-model="form.state.longitude"
                      clearable
                      :error="!!form.errors.value?.longitude"
                      :error-messages="form.errors.value?.longitude"
                      :label="t('branch::attributes.branches.longitude')"
                    />
                  </VCol>
                </VRow>
              </VCardText>
            </VCard>
          </VCol>
        </VRow>
      </VCol>
      <VCol cols="12" md="5">
        <VRow>
          <VCol cols="12">
            <VCard>
              <VCardTitle class="mb-2">
                <div class="d-flex align-center">
                  <VIcon class="me-2" icon="tabler-certificate" size="20" />
                  {{ t('branch::branches.form.cards.business_registration') }}
                </div>
              </VCardTitle>
              <VCardText>
                <VRow>
                  <VCol cols="12">
                    <VTextField
                      v-model="form.state.registration_number"
                      :error="!!form.errors.value?.registration_number"
                      :error-messages="form.errors.value?.registration_number"
                      :label="t('branch::attributes.branches.registration_number')"
                    />
                  </VCol>
                  <VCol cols="12">
                    <VTextField
                      v-model="form.state.vat_tin"
                      :error="!!form.errors.value?.vat_tin"
                      :error-messages="form.errors.value?.vat_tin"
                      :label="t('branch::attributes.branches.vat_tin')"
                    />
                  </VCol>
                </VRow>
              </VCardText>
            </VCard>
          </VCol>
          <VCol cols="12">
            <VCard>
              <VCardTitle class="mb-2">
                <div class="d-flex align-center">
                  <VIcon class="me-2" icon="tabler-map-pin-cog" size="20" />
                  {{ t('branch::branches.form.cards.regional_settings') }}
                </div>
              </VCardTitle>
              <VCardText>
                <VRow>
                  <VCol cols="12" md="6">
                    <VSelect
                      v-model="form.state.currency"
                      :error="!!form.errors.value?.currency"
                      :error-messages="form.errors.value?.currency"
                      item-title="name"
                      item-value="id"
                      :items="meta.currencies"
                      :label="t('branch::attributes.branches.currency')"
                    />
                  </VCol>
                  <VCol cols="12" md="6">
                    <VSelect
                      v-model="form.state.timezone"
                      :error="!!form.errors.value?.timezone"
                      :error-messages="form.errors.value?.timezone"
                      item-title="name"
                      item-value="id"
                      :items="meta.timezones"
                      :label="t('branch::attributes.branches.timezone')"
                    />
                  </VCol>
                </VRow>
              </VCardText>
            </VCard>
          </VCol>
          <VCol cols="12">
            <VCard>
              <VCardTitle class="mb-2">
                <div class="d-flex align-center">
                  <VIcon class="me-2" icon="tabler-settings-dollar" size="20" />
                  {{ t('branch::branches.form.cards.pos_settings') }}
                </div>
              </VCardTitle>
              <VCardText>
                <VRow>
                  <VCol cols="12">
                    <VSelect
                      v-model="form.state.order_types"
                      chips
                      :error="!!form.errors.value?.order_types"
                      :error-messages="form.errors.value?.order_types"
                      item-title="name"
                      item-value="id"
                      :items="meta.orderTypes"
                      :label="t('branch::attributes.branches.order_types')"
                      multiple
                    />
                  </VCol>
                  <VCol cols="12">
                    <VSelect
                      v-model="form.state.payment_methods"
                      chips
                      :error="!!form.errors.value?.payment_methods"
                      :error-messages="form.errors.value?.payment_methods"
                      item-title="name"
                      item-value="id"
                      :items="meta.paymentMethods"
                      :label="t('branch::attributes.branches.payment_methods')"
                      multiple
                    />
                  </VCol>
                  <VCol cols="12">
                    <VTextField
                      v-model="form.state.cash_difference_threshold"
                      :error="!!form.errors.value?.cash_difference_threshold"
                      :error-messages="form.errors.value?.cash_difference_threshold"
                      :label="t('branch::attributes.branches.cash_difference_threshold')"
                      type="number"
                    />
                  </VCol>
                  <VCol cols="12">
                    <h5 class="mb-2">{{ t('branch::attributes.branches.quick_pay_amounts') }}</h5>
                    <VRow dense>
                      <VCol
                        v-for="(_, index) in 6"
                        :key="index"
                        cols="12"
                        md="4"
                        sm="6"
                      >
                        <VTextField
                          v-model="quickPayAmounts[index]"
                          v-decimal-en
                          clearable
                          :error="!!form.errors.value?.[`quick_pay_amounts.${index}`]"
                          :error-messages="form.errors.value?.[`quick_pay_amounts.${index}`]"
                          :label="`${t('branch::attributes.branches.quick_pay_amounts.*')} ${index + 1}`"
                        />
                      </VCol>
                    </VRow>
                  </VCol>
                </VRow>
              </VCardText>
            </VCard>
          </VCol>
        </VRow>
      </VCol>
    </VRow>
  </BaseForm>
</template>
