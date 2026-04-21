<script lang="ts" setup>

  import { useI18n } from 'vue-i18n'
  import { useAuth } from '@/modules/auth/composables/auth.ts'
  import NavbarAction from '@/app/layouts/components/NavbarAction.vue'

  const props = defineProps<{
    refreshing: boolean
    loading: boolean
    reloadBranchData: boolean
    meta: Record<string, any>
    filters: Record<string, any>
  }>()

  defineEmits(['refresh'])
  const { user } = useAuth()

  const { t } = useI18n()
  const { filters } = toRefs(props)

  const globalDisabled = computed(() => props.reloadBranchData || props.refreshing || props.loading)
</script>

<template>
  <teleport to="#main-header-left-content">
    <div class="pos-header-selects d-flex align-center ga-2">
      <v-tooltip :text="t('pos::pos.tooltips.refresh_orders')">
        <template #activator="{ props:tooltipProps }">
          <NavbarAction v-bind="tooltipProps">
            <VBtn
              color="default"
              :disabled="!filters.branchId||globalDisabled"
              icon="tabler-refresh"
              :loading="refreshing"
              @click="$emit('refresh')"
            />
          </NavbarAction>
        </template>
      </v-tooltip>

      <VTextField
        v-model="filters.searchQuery"
        class="pos-select"
        clearable
        density="compact"
        :disabled="globalDisabled"
        hide-details
        :placeholder="t('pos::pos_viewer.search_by_order_number')"
        prepend-inner-icon="tabler-search"
        variant="solo-filled"
      />

      <VSelect
        v-if="!user?.assigned_to_branch"
        v-model="filters.branchId"
        class="pos-select"
        density="compact"
        :disabled="globalDisabled"
        item-title="name"
        item-value="id"
        :items="meta.branches"
        :loading="reloadBranchData"
        :placeholder="t('pos::pos_viewer.select_branch')"
        prepend-inner-icon="tabler-git-branch"
        variant="solo-filled"
      />

      <VSelect
        v-model="filters.type"
        class="pos-select"
        density="compact"
        :disabled="!filters.branchId||globalDisabled"
        hide-details
        item-title="name"
        item-value="id"
        :items="meta.orderTypes"
        :placeholder="t('pos::pos_viewer.select_order_type')"
        prepend-inner-icon="tabler-paper-bag"
        variant="solo-filled"
      />
    </div>
  </teleport>
</template>

<style lang="scss" scoped>
.pos-header-selects {
  padding: 8px 12px;
}

.pos-select {
  width: 190px;
  max-width: 200px;
}

.v-input--density-compact .v-field__input {
  font-size: 14px;
}
</style>
