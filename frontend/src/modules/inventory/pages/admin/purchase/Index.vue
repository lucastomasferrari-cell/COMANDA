<script lang="ts" setup>

  import { ref } from 'vue'
  import type { RouteLocationRaw } from 'vue-router'
  import { useRouter } from 'vue-router'
  import type { TableAction, TableHeader } from '@/modules/core/contracts/Table.ts'
  import { useI18n } from 'vue-i18n'
  import { useAuth } from '@/modules/auth/composables/auth.ts'
  import MarkAsReceivedDialog from './Partials/MarkAsReceivedDialog.vue'

  const { t } = useI18n()
  const { user, can } = useAuth()
  const router = useRouter()

  const showMarkAsReceivedDialog = ref(false)
  const itemId = ref<number | null>(null)
  const smartTableRef = ref()

  const markAsReceived = (item: any) => {
    if (can('admin.purchases.mark_as_received')) {
      itemId.value = item.id
      showMarkAsReceivedDialog.value = true
    }
  }

  const headers: TableHeader[] = [
    { title: t('inventory::purchases.table.reference_number'), value: 'reference_number', sortable: true },
    {
      title: t('branch::branches.table.branch'),
      value: 'branch.name',
      sortable: true,
      sortable_key: 'branch_id',
      hidden: user?.assigned_to_branch,
    },
    {
      title: t('inventory::purchases.table.supplier'),
      value: 'supplier.name',
      sortable: true,
      sortable_key: 'supplier_id',
    },
    { title: t('inventory::purchases.table.total'), value: 'total', sortable: true },
    { title: t('inventory::purchases.table.status'), value: 'status', sortable: true },
    { title: t('inventory::purchases.table.expected_at'), value: 'expected_at', sortable: true },
    { title: t('admin::admin.table.created_at'), value: 'created_at', sortable: true },
  ]

  const actions: TableAction[] = [
    { key: 'show' },
    {
      key: 'edit',
      hidden: item => !item.allow_edit,
    },
    {
      key: 'mark_as_received',
      icon: 'tabler-bookmarks',
      color: 'info',
      hidden: item => !['pending', 'partially_received'].includes(item.status.id),
      onClick: markAsReceived,
      label: t('inventory::purchases.buttons.mark_as_received'),
    },
    {
      key: 'destroy',
      hidden: item => !['pending', 'draft'].includes(item.status.id),
      confirm: {
        message: t('admin::admin.delete.confirmation_message'),
        confirmButtonText: t('admin::admin.delete.confirm_button_text'),
      },
    },
  ]

  const bulkActions: TableAction[] = [
    {
      key: 'destroy',
      confirm: {
        message: t('admin::admin.delete.confirmation_message'),
        confirmButtonText: t('admin::admin.delete.confirm_button_text'),
      },
    },
  ]

  const refreshTable = () => {
    smartTableRef.value.refresh()
  }

  const onRowClick = (event: MouseEvent, value: any) => {
    if (can('admin.purchases.show')) {
      router.push({ name: 'admin.purchases.show', params: { id: value.item.id } } as unknown as RouteLocationRaw)
    }
  }

</script>

<template>
  <SmartDataTable
    ref="smartTableRef"
    :actions="actions"
    api-uri="/v1/purchases"
    :bulk-actions="bulkActions"
    :header-actions="[{ key: 'create' }]"
    :headers="headers"
    module="inventory"
    name="purchase"
    :on-row-click="can('admin.purchases.show')?onRowClick:null"
    resource="purchases"
  />

  <MarkAsReceivedDialog
    v-if="can('admin.purchases.mark_as_received') && showMarkAsReceivedDialog && itemId"
    :id="itemId"
    v-model="showMarkAsReceivedDialog"
    @confirm="refreshTable"
  />
</template>
