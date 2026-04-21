<script lang="ts" setup>

  import { ref } from 'vue'
  import type { TableAction, TableHeader } from '@/modules/core/contracts/Table.ts'
  import { useI18n } from 'vue-i18n'
  import { useAuth } from '@/modules/auth/composables/auth.ts'
  import {
    useCurrencyRate,
  } from '@/modules/currency/composables/currencyRate.ts'
  import Edit from './Edit.vue'

  const { t } = useI18n()
  const { can } = useAuth()
  const { refresh } = useCurrencyRate()

  const openFormDialog = ref(false)
  const item = ref<object | null>(null)
  const smartTableRef = ref()

  const headers: TableHeader[] = [
    { title: t('currency::currency_rates.table.currency'), value: 'currency_name', sortable: false },
    { title: t('currency::currency_rates.table.code'), value: 'currency', sortable: true },
    { title: t('currency::currency_rates.table.rate'), value: 'rate', sortable: true },
    { title: t('currency::currency_rates.table.last_updated'), value: 'updated_at', sortable: true },
  ]

  const headerActions = ref<TableAction[]>([
    {
      key: 'refresh_rates',
      label: t('currency::currency_rates.refresh_rates'),
      icon: 'ic-baseline-currency-exchange',
      tooltip: t('currency::currency_rates.tooltips.refresh_exchange_rates'),
      permission: 'admin.currency_rates.edit',
      onClick: refresh,
    },
  ])

  function onRowClick (event: MouseEvent, value: any) {
    if (can('admin.currency_rates.edit')) {
      item.value = value.item
      openFormDialog.value = true
    }
  }

  function onFormSaved () {
    smartTableRef.value.refresh()
  }
</script>

<template>
  <SmartDataTable
    ref="smartTableRef"
    api-uri="/v1/currency-rates"
    :header-actions="headerActions"
    :headers="headers"
    module="currency"
    name="currency_rate"
    :on-row-click="can('admin.currency_rates.edit')?onRowClick:null"
    resource="currency_rates"
  />
  <Edit
    v-if="openFormDialog && can('admin.currency_rates.edit')"
    v-model="openFormDialog"
    :item="item"
    @saved="onFormSaved"
  />
</template>
