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

  // Post-Fix 8 bloque 1: form simplificado. Capacity y floor_id se ocultan
  // de la UI (siempre requeridos por SaveTableRequest — se auto-llenan con
  // defaults / primer floor disponible). Shape queda en Opciones avanzadas
  // colapsable con default 'circle'. Lo único visible en uso común:
  // Nombre + Zona + toggle Activo (edit).
  const form = useForm({
    name: props.item?.name || {},
    branch_id: user?.assigned_to_branch ? user.branch_id : props.item?.branch?.id,
    floor_id: props.item?.floor?.id ?? null,
    zone_id: props.item?.zone?.id ?? null,
    shape: props.item?.shape?.id ?? 'circle',
    capacity: props.item?.capacity ?? 4,
    is_active: props.item?.is_active ?? true,
  })

  const meta = ref({ branches: [], shapes: [], floors: [] as Record<string, any>[], zones: [] })

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
        // Single-floor setup (mayoría de restaurantes AR): auto-asignar
        // al primero para que el form no pida input que el user no sabe.
        if (!form.state.floor_id && meta.value.floors.length > 0) {
          form.state.floor_id = meta.value.floors[0]?.id
        }
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
    if (newValue) {
      loadFormData(form.state.branch_id, newValue)
    }
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
                  :label="t('seatingplan::attributes.tables.name')"
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
              <VCol cols="12">
                <VCheckbox
                  v-if="action !== 'create'"
                  v-model="form.state.is_active"
                  :label="t('seatingplan::attributes.tables.is_active')"
                />
              </VCol>
            </VRow>

            <!-- Opciones avanzadas: Shape colapsable. Capacity queda
                 oculto con default 4 (valor típico restaurante AR),
                 Floor también hidden con auto-asignación. -->
            <VExpansionPanels class="mt-3" flat variant="accordion">
              <VExpansionPanel>
                <VExpansionPanelTitle>
                  <VIcon class="me-2" icon="tabler-adjustments" size="18" />
                  {{ t('seatingplan::tables.form.advanced_options') }}
                </VExpansionPanelTitle>
                <VExpansionPanelText>
                  <VRow>
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
                  </VRow>
                </VExpansionPanelText>
              </VExpansionPanel>
            </VExpansionPanels>
          </VCardText>
        </VCard>
      </VCol>
    </VRow>
  </BaseForm>
</template>
