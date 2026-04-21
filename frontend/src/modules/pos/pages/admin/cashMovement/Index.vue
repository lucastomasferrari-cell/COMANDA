<script lang="ts" setup>

  import type { TableHeader } from '@/modules/core/contracts/Table.ts'
  import { useI18n } from 'vue-i18n'
  import TableEnum from '@/modules/core/components/Table/Partials/TableEnum.vue'
  import TableMoney from '@/modules/core/components/Table/Partials/TableMoney.vue'
  import { usePosSession } from '@/modules/pos/composables/posSession.ts'
  import { useAuth } from '@/modules/auth/composables/auth.ts'
  import ShowDialog from './Dialogs/ShowDialog.vue'

  const { t } = useI18n()
  const { user, can } = useAuth()
  const { getShowData } = usePosSession()
  const route = useRoute()

  const sessionId = (route.params as Record<string, any>).id
  const loading = ref(false)
  const isNotFound = ref(false)
  const showDialog = ref(false)
  const showCashMovement = ref<Record<string, any> | null>(null)
  const item = ref<Record<string, any> | null>(null)

  const headers: TableHeader[] = [
    {
      title: t('pos::pos_cash_movements.table.pos_register'),
      value: 'pos_register.name',
      sortable_key: 'pos_register_id',
      sortable: true,
      hidden: sessionId,
    },
    {
      title: t('branch::branches.table.branch'),
      value: 'branch.name',
      sortable_key: 'branch_id',
      sortable: true,
      hidden: sessionId || user?.assigned_to_branch,
    },
    {
      title: t('pos::pos_cash_movements.table.direction'),
      value: 'direction',
      sortable_key: 'direction',
      sortable: true,
    },
    {
      title: t('pos::pos_cash_movements.table.reason'),
      value: 'reason',
      sortable_key: 'reason',
      sortable: true,
    },
    {
      title: t('pos::pos_cash_movements.table.balance_before'),
      value: 'balance_before',
      sortable_key: 'balance_before',
      sortable: true,
    },
    {
      title: t('pos::pos_cash_movements.table.amount'),
      value: 'amount',
      sortable_key: 'amount',
      sortable: true,
    },
    {
      title: t('pos::pos_cash_movements.table.balance_after'),
      value: 'balance_after',
      sortable_key: 'balance_after',
      sortable: true,
    },
    {
      title: t('pos::pos_cash_movements.table.occurred_at'),
      value: 'occurred_at',
      sortable: true,
    },
  ]

  const onRowClick = (event: MouseEvent, value: any) => {
    if (can('admin.pos_cash_movements.show')) {
      showDialog.value = true
      showCashMovement.value = value.item
    }
  }

  const cellComponents: Record<string, any> = {
    balance_before: {
      component: TableMoney,
      props: { column: 'balance_before' },
    },
    amount: {
      component: TableMoney,
      props: { column: 'amount' },
    },
    balance_after: {
      component: TableMoney,
      props: { column: 'balance_after' },
    },
    direction: {
      component: TableEnum,
      props: { column: 'direction' },
    },
    reason: {
      component: TableEnum,
      props: { column: 'reason' },
    },
  }

  if (sessionId) {
    onBeforeMount(async () => {
      loading.value = true
      const response = await getShowData(sessionId)
      if (response.status === 200) {
        item.value = response.data
      } else if (response.status === 404) {
        isNotFound.value = true
      }
      loading.value = false
    })
  }

</script>

<template>
  <PageStateWrapper v-if="sessionId && (loading || isNotFound)" :loading="loading" :not-found="isNotFound" />
  <div v-else>
    <SmartDataTable
      :api-uri="`/v1/pos/cash-movements${sessionId?`?session_id=${sessionId}`:''}`"
      :cell-components="cellComponents"
      :has-search="false"
      :headers="headers"
      module="pos"
      name="pos_cash_movement"
      :on-row-click="can('admin.pos_cash_movements.show')?onRowClick:null"
      resource="pos_cash_movements"
    />
    <ShowDialog
      v-if="showDialog && showCashMovement && can('admin.pos_cash_movements.show')"
      :id="showCashMovement.id"
      v-model="showDialog"
    />
  </div>
</template>
