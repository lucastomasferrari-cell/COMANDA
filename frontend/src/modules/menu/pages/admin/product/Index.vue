<script lang="ts" setup>

  import { ref } from 'vue'
  import { useRoute, useRouter, type RouteLocationRaw } from 'vue-router'
  import type { TableAction, TableHeader } from '@/modules/core/contracts/Table.ts'
  import { useI18n } from 'vue-i18n'
  import TableName from './Partials/TableName.vue'
  import TablePrice from './Partials/TablePrice.vue'

  const { t } = useI18n()
  const route = useRoute()
  const router = useRouter()
  const smartTableRef = ref<any>(null)
  const menuId = ref((route.params as Record<string, any>)?.menuId)

  const edit = async (item: any) => {
    await (menuId.value
      ? router.push({
        name: 'admin.menus.products.edit',
        params: { id: item.id, menuId: smartTableRef.value?.defaultFilters.menu_id },
      } as unknown as RouteLocationRaw)
      : router.push({ name: 'admin.products.edit', params: { id: item.id } } as unknown as RouteLocationRaw))
  }

  const create = async () => {
    await (menuId.value
      ? router.push({
        name: 'admin.menus.products.create',
        params: { menuId: smartTableRef.value?.defaultFilters.menu_id },
      } as unknown as RouteLocationRaw)
      : router.push({ name: 'admin.products.create' } as unknown as RouteLocationRaw))
  }

  const headers: TableHeader[] = [
    { title: t('product::products.table.thumbnail'), value: 'thumbnail', sortable: false },
    { title: t('product::products.table.name'), value: 'name', sortable: true },
    { title: t('product::products.table.price'), value: 'price', sortable: true },
    { title: t('admin::admin.table.activation'), value: 'is_active', sortable: true },
    { title: t('admin::admin.table.created_at'), value: 'created_at', sortable: true },
    { title: t('admin::admin.table.updated_at'), value: 'updated_at', sortable: true },
  ]

  const actions: TableAction[] = [
    { key: 'edit', onClick: edit },
    {
      key: 'destroy',
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

  const cellComponents: Record<string, any> = {
    name: {
      component: TableName,
    },
    price: {
      component: TablePrice,
    },
  }

</script>

<template>
  <SmartDataTable
    ref="smartTableRef"
    :actions="actions"
    api-uri="/v1/products"
    :bulk-actions="bulkActions"
    :cell-components="cellComponents"
    :default-filters="{'menu_id': menuId}"
    :header-actions="[{ key: 'create',onClick: create }]"
    :headers="headers"
    module="product"
    name="product"
    resource="products"
  />
</template>
