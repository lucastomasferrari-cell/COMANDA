<script lang="ts" setup>
  import type { RouteLocationRaw } from 'vue-router'
  import { useI18n } from 'vue-i18n'
  import { useTable } from '@/modules/seatingPlan/composables/table.ts'
  import { useForm } from '@/modules/core/composables/form.ts'
  import { useAuth } from '@/modules/auth/composables/auth.ts'

  const props = defineProps<{
    item?: Record<string, any> | null
    action: 'update' | 'create'
  }>()

  const { t } = useI18n()
  const { user } = useAuth()
  const { getFormMeta, update, store } = useTable()
  const router = useRouter()

  const form = useForm({
    name: props.item?.name || {},
    branch_id: user?.assigned_to_branch ? user.branch_id : props.item?.branch?.id,
    floor_id: props.item?.floor.id,
    zone_id: props.item?.zone.id,
    shape: props.item?.shape?.id,
    capacity: props.item?.capacity,
    is_active: props.item?.is_active || false,
  })

  const meta = ref({ branches: [], shapes: [], floors: [], zones: [] })

  const submit = async () => {
    if (
      !form.loading.value
      && await form.submit(() => props.action == 'create' ? store(form.state) : update(props.item?.id, form.state))
    ) {
      await router.push({ name: 'admin.tables.index' } as unknown as RouteLocationRaw)
    }
  }

  onBeforeMount(() => {
    loadFormData()
    if (form.state.branch_id) {
      loadFormData(form.state.branch_id)
    }
  })

  const loadFormData = async (branchId?: number, zoneId?: number) => {
    try {
      const response = (await getFormMeta(branchId, zoneId)).data.body
      meta.value.branches = response.branches || meta.value.branches
      meta.value.shapes = response.shapes || meta.value.shapes
      if (branchId && zoneId) {
        meta.value.zones = response.zones
      } else if (branchId) {
        meta.value.floors = response.floors
      }
    } catch {
    /* Empty */
    }
  }

  watch(() => form.state.branch_id, newValue => {
    form.state.floor_id = null
    form.state.zone_id = null
    meta.value.floors = []
    meta.value.zones = []
    loadFormData(newValue)
  })

  watch(() => form.state.floor_id, newValue => {
    form.state.zone_id = null
    meta.value.zones = []
    loadFormData(form.state.branch_id, newValue)
  })
</script>

<template>
  <BaseForm
    v-slot="{ currentLanguage }"
    :action="action"
    has-multiple-language
    :loading="form.loading.value"
    resource="tables"
    @submit="submit"
  >
    <VRow>
      <VCol cols="12" md="8">
        <VCard>
          <VCardTitle class="d-flex justify-space-between align-center mb-2">
            <div class="d-flex align-center">
              <VIcon class="me-2" icon="tabler-info-circle" size="20" />
              <span>{{ t('seatingplan::tables.form.cards.table_information') }}</span>
            </div>
          </VCardTitle>
          <VCardText>
            <VRow>
              <VCol cols="12" md="6">
                <VTextField
                  v-model="form.state.name[currentLanguage.id]"
                  :error="!!form.errors.value?.[`name.${currentLanguage.id}`]"
                  :error-messages="form.errors.value?.[`name.${currentLanguage.id}`]"
                  :label="t('seatingplan::attributes.tables.name') + ` ( ${currentLanguage.name} )`"
                />
              </VCol>
              <VCol cols="12" md="6">
                <VSelect
                  v-model="form.state.shape"
                  :error="!!form.errors.value?.shape"
                  :error-messages="form.errors.value?.shape"
                  item-title="name"
                  item-value="id"
                  :items="meta.shapes"
                  :label="t('seatingplan::attributes.tables.shape')"
                />
              </VCol>
              <VCol cols="12" md="6">
                <VTextField
                  v-model="form.state.capacity"
                  :error="!!form.errors.value?.capacity"
                  :error-messages="form.errors.value?.capacity"
                  :label="t('seatingplan::attributes.tables.capacity')"
                />
              </VCol>
              <VCol cols="12">
                <VCheckbox
                  v-model="form.state.is_active"
                  :label="t('seatingplan::attributes.tables.is_active')"
                />
              </VCol>
            </VRow>
          </VCardText>
        </VCard>
      </VCol>
      <VCol cols="12" md="4">
        <VCard>
          <VCardTitle class="d-flex justify-space-between align-center mb-2">
            <div class="d-flex align-center">
              <VIcon class="me-2" icon="tabler-building" size="20" />
              <span>{{ t('seatingplan::tables.form.cards.location') }}</span>
            </div>
          </VCardTitle>
          <VCardText>
            <VRow>
              <VCol v-if="action=='create' && !user?.assigned_to_branch" cols="12" md="12">
                <VSelect
                  v-model="form.state.branch_id"
                  :error="!!form.errors.value?.branch_id"
                  :error-messages="form.errors.value?.branch_id"
                  item-title="name"
                  item-value="id"
                  :items="meta.branches"
                  :label="t('seatingplan::attributes.tables.branch_id')"
                />
              </VCol>
              <VCol cols="12" md="6">
                <VSelect
                  v-model="form.state.floor_id"
                  :error="!!form.errors.value?.floor_id"
                  :error-messages="form.errors.value?.floor_id"
                  item-title="name"
                  item-value="id"
                  :items="meta.floors"
                  :label="t('seatingplan::attributes.tables.floor_id')"
                />
              </VCol>
              <VCol cols="12" md="6">
                <VSelect
                  v-model="form.state.zone_id"
                  :error="!!form.errors.value?.zone_id"
                  :error-messages="form.errors.value?.zone_id"
                  item-title="name"
                  item-value="id"
                  :items="meta.zones"
                  :label="t('seatingplan::attributes.tables.zone_id')"
                />
              </VCol>
            </VRow>
          </VCardText>
        </VCard>
      </VCol>
    </VRow>
  </BaseForm>
</template>
