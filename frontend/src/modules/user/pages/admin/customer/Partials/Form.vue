<script lang="ts" setup>
  import { computed, onBeforeMount, ref, watch } from 'vue'
  import { useI18n } from 'vue-i18n'
  import { type RouteLocationRaw, useRouter } from 'vue-router'
  import BaseForm from '@/modules/core/components/Form/BaseForm.vue'
  import ProfilePhotoPicker from '@/modules/core/components/Form/ProfilePhotoPicker.vue'
  import { useForm } from '@/modules/core/composables/form.ts'
  import { useCustomer } from '@/modules/user/composables/customer.ts'

  const props = defineProps<{
    item?: Record<string, any> | null
    action: 'update' | 'create'
  }>()

  const { t } = useI18n()
  const { getFormMeta, update, store } = useCustomer()
  const router = useRouter()

  const meta = ref<{
    genders: Record<string, any>[]
    countries: Record<string, any>[]
    customerTypes: Record<string, any>[]
  }>({
    genders: [],
    countries: [],
    customerTypes: [],
  })

  const form = useForm({
    name: props.item?.name,
    username: props.item?.username,
    phone_country_iso_code: props.item?.phone_country_iso_code,
    phone: props.item?.national_phone,
    email: props.item?.email,
    gender: props.item?.gender?.id,
    customer_type: props.item?.customer_type?.id || 'regular',
    birthdate: props.item?.birthdate || null,
    note: props.item?.note || null,
    registration_number: props.item?.registration_number || null,
    vat_tin: props.item?.vat_tin || null,
    is_active: props.item?.is_active || false,
    password: null,
    password_confirmation: null,
    profile_photo: null as File | null,
    remove_profile_photo: false,
  })

  const initialPhotoUrl = computed(() => {
    if (form.state.remove_profile_photo) {
      return null
    }
    return props.item?.profile_photo_url || null
  })

  async function submit () {
    if (
      !form.loading.value
      && await form.submit(() => props.action == 'create' ? store(form.state) : update(props.item?.id, form.state))
    ) {
      await router.push({ name: 'admin.customers.index' } as unknown as RouteLocationRaw)
    }
  }

  function clearProfilePhoto () {
    form.state.profile_photo = null
    form.state.remove_profile_photo = true
  }

  watch(
    () => form.state.profile_photo,
    value => {
      if (value) {
        form.state.remove_profile_photo = false
      }
    },
  )

  onBeforeMount(async () => {
    try {
      const response = (await getFormMeta()).data.body
      meta.value.genders = response.genders
      meta.value.countries = response.countries
      meta.value.customerTypes = response.customer_types
    } catch {
    /* Empty */
    }
  })
</script>

<template>
  <BaseForm
    :action="action"
    :loading="form.loading.value"
    resource="customers"
    @submit="submit"
  >
    <VRow>
      <VCol cols="12" md="8">
        <VCard>
          <VCardTitle class="d-flex align-center">
            <VIcon class="me-2" icon="tabler-id" size="20" />
            {{ t('user::customers.form.cards.user_information') }}
          </VCardTitle>
          <VCardText>
            <VRow>
              <VCol cols="12" md="6">
                <VTextField
                  v-model="form.state.name"
                  :error="!!form.errors.value?.name"
                  :error-messages="form.errors.value?.name"
                  :label="t('user::attributes.users.name')"
                />
              </VCol>
              <VCol cols="12" md="6">
                <VSelect
                  v-model="form.state.customer_type"
                  :error="!!form.errors.value?.customer_type"
                  :error-messages="form.errors.value?.customer_type"
                  item-title="name"
                  item-value="id"
                  :items="meta.customerTypes"
                  :label="t('user::attributes.users.customer_type')"
                />
              </VCol>
              <VCol cols="12" md="6">
                <VSelect
                  v-model="form.state.phone_country_iso_code"
                  :error="!!form.errors.value?.phone_country_iso_code"
                  :error-messages="form.errors.value?.phone_country_iso_code"
                  item-title="name"
                  item-value="id"
                  :items="meta.countries"
                  :label="t('user::attributes.users.phone_country_iso_code')"
                />
              </VCol>
              <VCol cols="12" md="6">
                <VTextField
                  v-model="form.state.phone"
                  :error="!!form.errors.value?.phone"
                  :error-messages="form.errors.value?.phone"
                  :label="t('user::attributes.users.phone')"
                />
              </VCol>
              <VCol cols="12" md="6">
                <DatePicker
                  v-model="form.state.birthdate"
                  clearable
                  :error="!!form.errors.value?.birthdate"
                  :error-messages="form.errors.value?.birthdate"
                  :label="t('user::attributes.users.birthdate')"
                  :max="new Date().toLocaleDateString('en-CA')"
                />
              </VCol>
              <VCol cols="12" md="6">
                <VSelect
                  v-model="form.state.gender"
                  :error="!!form.errors.value?.gender"
                  :error-messages="form.errors.value?.gender"
                  item-title="name"
                  item-value="id"
                  :items="meta.genders"
                  :label="t('user::attributes.users.gender')"
                />
              </VCol>
              <VCol cols="12">
                <VCheckbox
                  v-model="form.state.is_active"
                  :label="t('user::attributes.users.is_active')"
                />
              </VCol>
            </VRow>
          </VCardText>
        </VCard>
        <VCard class="mt-3">
          <VCardTitle class="d-flex align-center">
            <VIcon class="me-2" icon="tabler-user-edit" size="20" />
            {{ t('user::customers.form.cards.user_details') }}
          </VCardTitle>
          <VCardText>
            <VRow>
              <VCol cols="12" md="6">
                <VTextField
                  v-model="form.state.username"
                  :error="!!form.errors.value?.username"
                  :error-messages="form.errors.value?.username"
                  :label="t('user::attributes.users.username')"
                />
              </VCol>
              <VCol cols="12" md="6">
                <VTextField
                  v-model="form.state.email"
                  :error="!!form.errors.value?.email"
                  :error-messages="form.errors.value?.email"
                  :label="t('user::attributes.users.email')"
                />
              </VCol>
              <VCol cols="12">
                <VTextarea
                  v-model="form.state.note"
                  auto-grow
                  :error="!!form.errors.value?.note"
                  :error-messages="form.errors.value?.note"
                  :label="t('user::attributes.users.note')"
                  rows="3"
                />
              </VCol>
            </VRow>
          </VCardText>
        </VCard>
      </VCol>
      <VCol cols="12" md="4">
        <VCard>
          <VCardTitle class="d-flex align-center justify-space-between">
            <div class="d-flex align-center">
              <VIcon class="me-2" icon="tabler-user-circle" size="20" />
              {{ t('user::customers.form.cards.avatar') }}
            </div>
            <VBtn
              v-if="props.item?.profile_photo_url || form.state.profile_photo"
              color="error"
              size="small"
              variant="text"
              @click="clearProfilePhoto"
            >
              <VIcon icon="tabler-trash" start />
              {{ t('user::attributes.users.remove_profile_photo') }}
            </VBtn>
          </VCardTitle>
          <VCardText>
            <ProfilePhotoPicker
              v-model="form.state.profile_photo"
              :error="!!form.errors.value?.profile_photo"
              :error-messages="form.errors.value?.profile_photo"
              :initial-url="initialPhotoUrl"
              :label="t('user::attributes.users.profile_photo')"
            />
          </VCardText>
        </VCard>
        <VCard class="mt-3">
          <VCardTitle class="d-flex align-center">
            <VIcon class="me-2" icon="tabler-key" size="20" />
            {{ t('user::customers.form.cards.security') }}
          </VCardTitle>
          <VCardText>
            <VTextField
              v-model="form.state.password"
              class="mb-3"
              :error="!!form.errors.value?.password"
              :error-messages="form.errors.value?.password"
              :label="t('user::attributes.users.password')"
              type="password"
            />
            <VTextField
              v-model="form.state.password_confirmation"
              :error="!!form.errors.value?.password_confirmation"
              :error-messages="form.errors.value?.password_confirmation"
              :label="t('user::attributes.users.password_confirmation')"
              type="password"
            />
          </VCardText>
        </VCard>
        <VCard class="mt-3">
          <VCardTitle class="d-flex align-center">
            <VIcon class="me-2" icon="tabler-building-bank" size="20" />
            {{ t('user::customers.form.cards.business_details') }}
          </VCardTitle>
          <VCardText>
            <VTextField
              v-model="form.state.registration_number"
              class="mb-3"
              :error="!!form.errors.value?.registration_number"
              :error-messages="form.errors.value?.registration_number"
              :label="t('user::attributes.users.registration_number')"
            />
            <VTextField
              v-model="form.state.vat_tin"
              :error="!!form.errors.value?.vat_tin"
              :error-messages="form.errors.value?.vat_tin"
              :label="t('user::attributes.users.vat_tin')"
            />
          </VCardText>
        </VCard>
      </VCol>
    </VRow>
  </BaseForm>
</template>
