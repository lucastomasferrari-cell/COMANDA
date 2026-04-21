<script lang="ts" setup>
  import { computed, ref } from 'vue'
  import { useI18n } from 'vue-i18n'

  const props = defineProps<{
    permissions: {
      title: string
      actions: {
        id: string
        name: string
      }[]
    }[]
    modelValue: string[]
  }>()

  const emit = defineEmits<{
    (e: 'update:modelValue', value: string[]): void
  }>()

  const { t } = useI18n()

  const selectedPermissions = ref<string[]>([...props.modelValue])
  const search = ref('')
  const expanded = ref<number[]>([])

  const allIds = computed(() =>
    props.permissions.flatMap(p => p.actions.map(a => a.id)),
  )

  const normalizedSearch = computed(() => search.value.trim().toLowerCase())

  const filteredPermissions = computed(() =>
    props.permissions
      .map((permission, index) => {
        const actions = permission.actions.filter(action =>
          !normalizedSearch.value
          || permission.title.toLowerCase().includes(normalizedSearch.value)
          || action.name.toLowerCase().includes(normalizedSearch.value)
          || action.id.toLowerCase().includes(normalizedSearch.value),
        )

        return {
          ...permission,
          index,
          actions,
          totalActions: permission.actions.length,
        }
      })
      .filter(permission => permission.actions.length > 0),
  )

  const isSelected = (id: string) => selectedPermissions.value.includes(id)

  function togglePermission (id: string, checked: boolean | null) {
    const set = new Set(selectedPermissions.value)
    checked ? set.add(id) : set.delete(id)
    selectedPermissions.value = Array.from(set)
  }

  function isCardFullySelected (actions: { id: string }[]) {
    return actions.every(a => isSelected(a.id))
  }

  function toggleCard (actions: { id: string }[], checked: boolean | null) {
    const current = new Set(selectedPermissions.value)
    for (const { id } of actions) {
      checked ? current.add(id) : current.delete(id)
    }
    selectedPermissions.value = Array.from(current)
  }

  const globalSwitch = computed({
    get: () => selectedPermissions.value.length === allIds.value.length,
    set: (checked: boolean) => {
      selectedPermissions.value = checked ? [...allIds.value] : []
    },
  })

  const selectedCount = computed(() => selectedPermissions.value.length)

  function selectedCountForGroup (actions: { id: string }[]) {
    return actions.filter(action => isSelected(action.id)).length
  }

  function expandAll () {
    expanded.value = filteredPermissions.value.map(permission => permission.index)
  }

  function collapseAll () {
    expanded.value = []
  }

  // Emit model update
  watch(selectedPermissions, val => {
    emit('update:modelValue', val)
  })

  watch(filteredPermissions, permissions => {
    if (!normalizedSearch.value) {
      return
    }

    expanded.value = permissions.map(permission => permission.index)
  }, { immediate: true })
</script>

<template>
  <VCol cols="12">
    <VCard class="permissions-card">
      <VCardTitle class="mb-2 d-flex flex-wrap align-center justify-space-between ga-3">
        <div class="d-flex align-center">
          <VIcon class="me-2" icon="tabler-shield-cog" size="20" />
          {{ t('user::roles.form.cards.permissions') }}
        </div>
        <div class="d-flex flex-wrap align-center ga-2">
          <VChip color="secondary" size="small" variant="tonal">
            {{ selectedCount }} / {{ allIds.length }}
          </VChip>
          <VSwitch v-model="globalSwitch" hide-details inset />
        </div>
      </VCardTitle>
      <VCardText>
        <div class="d-flex flex-wrap align-center justify-space-between ga-3 mb-4">
          <VTextField
            v-model="search"
            class="permissions-search"
            clearable
            density="comfortable"
            hide-details
            :label="t('admin::admin.search')"
            prepend-inner-icon="tabler-search"
          />

          <div class="d-flex flex-wrap align-center ga-2">
            <VBtn color="secondary" variant="tonal" @click="expandAll">
              <VIcon icon="tabler-chevrons-down" start />
              {{ t('admin::admin.expand_all') }}
            </VBtn>
            <VBtn color="secondary" variant="text" @click="collapseAll">
              <VIcon icon="tabler-chevrons-up" start />
              {{ t('admin::admin.collapse_all') }}
            </VBtn>
          </div>
        </div>

        <VAlert
          v-if="filteredPermissions.length === 0"
          border="start"
          color="secondary"
          variant="tonal"
        >
          {{ t('core::errors.no_data_found') }}
        </VAlert>

        <VRow v-else>
          <VCol
            v-for="permission in filteredPermissions"
            :key="permission.title"
            cols="12"
            lg="4"
            md="6"
          >
            <VExpansionPanels
              v-model="expanded"
              class="permissions-accordion"
              elevation="0"
              multiple
              variant="accordion"
            >
              <VExpansionPanel :value="permission.index">
                <VExpansionPanelTitle class="permission-panel-title">
                  <div class="d-flex align-center justify-space-between w-100 pe-2 ga-3">
                    <div class="d-flex flex-column">
                      <span class="text-high-emphasis font-weight-medium">{{
                        permission.title
                      }}</span>
                      <span class="text-body-2 text-medium-emphasis">
                        {{ selectedCountForGroup(permission.actions) }} / {{
                          permission.totalActions
                        }}
                      </span>
                    </div>

                    <div class="d-flex align-center ga-2" @click.stop>
                      <VChip size="small" variant="tonal">
                        {{ permission.actions.length }}
                      </VChip>
                      <VSwitch
                        hide-details
                        inset
                        :model-value="isCardFullySelected(permission.actions)"
                        @update:model-value="val => toggleCard(permission.actions, val)"
                      />
                    </div>
                  </div>
                </VExpansionPanelTitle>

                <VExpansionPanelText>
                  <div class="permission-action mt-3">
                    <label
                      v-for="action in permission.actions"
                      :key="action.id"
                      class="permission-action-row mb-2"
                    >
                      <div class="permission-action-copy">
                        <span class="text-body-1 font-weight-medium">{{ action.name }}</span>
                        <span class="text-caption text-medium-emphasis">{{ action.id }}</span>
                      </div>

                      <VSwitch
                        hide-details
                        inset
                        :model-value="isSelected(action.id)"
                        @update:model-value="val => togglePermission(action.id, val)"
                      />
                    </label>
                  </div>
                </VExpansionPanelText>
              </VExpansionPanel>
            </VExpansionPanels>
          </VCol>
        </VRow>
      </VCardText>
    </VCard>
  </VCol>
</template>

<style lang="scss" scoped>
.permissions-card {
  border: 1px solid rgba(var(--v-theme-on-surface), 0.08);
}

.permissions-search {
  max-width: 360px;
  min-width: 280px;
}

.permissions-accordion :deep(.v-expansion-panel) {
  border: 1px solid rgba(var(--v-theme-on-surface), 0.08);
  border-radius: 16px !important;
}

.permission-panel-title {
  background: linear-gradient(135deg, rgba(var(--v-theme-primary), 0.08), rgba(var(--v-theme-secondary), 0.04));
}

.permission-actions {
  display: grid;
  gap: 10px;
}

.permission-action-row {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 16px;
  padding: 12px 14px;
  border-radius: 14px;
  border: 1.5px dashed rgba(var(--v-theme-on-surface), 0.08);
  background: rgba(var(--v-theme-surface), 0.75);
}

.permission-action-copy {
  display: flex;
  flex-direction: column;
  min-width: 0;
}

.permission-action-copy span:last-child {
  word-break: break-word;
}

@media (max-width: 959px) {
  .permissions-search {
    min-width: 100%;
  }
}
</style>
