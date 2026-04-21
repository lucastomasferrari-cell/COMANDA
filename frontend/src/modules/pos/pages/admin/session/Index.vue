<script lang="ts" setup>

  import type { RouteLocationRaw } from 'vue-router'
  import type { TableAction, TableHeader } from '@/modules/core/contracts/Table.ts'
  import { useI18n } from 'vue-i18n'
  import { useAuth } from '@/modules/auth/composables/auth.ts'
  import CloseDialog from './Dialogs/CloseDialog.vue'
  import OpenDialog from './Dialogs/OpenDialog.vue'
  import ShowDialog from './Dialogs/ShowDialog.vue'

  const { t } = useI18n()
  const { user, can } = useAuth()
  const router = useRouter()

  const openDialog = ref(false)
  const showDialog = ref(false)
  const closeDialog = ref(false)
  const closeSession = ref(null)
  const showSession = ref<Record<string, any> | null>(null)
  const smartTableRef = ref<any>(null)

  const onClickShow = (item: any) => {
    if (can('admin.pos_sessions.show')) {
      showSession.value = item
      showDialog.value = true
    }
  }

  const headers: TableHeader[] = [
    {
      title: t('pos::pos_sessions.table.pos_register'),
      value: 'pos_register.name',
      sortable_key: 'pos_register_id',
      sortable: true,
    },
    {
      title: t('branch::branches.table.branch'),
      value: 'branch.name',
      sortable_key: 'branch_id',
      sortable: true,
      hidden: user?.assigned_to_branch,
    },
    {
      title: t('pos::pos_sessions.table.opened_by'),
      value: 'opened_by.name',
      sortable_key: 'opened_by',
      sortable: true,
    },
    {
      title: t('pos::pos_sessions.table.closed_by'),
      value: 'closed_by.name',
      sortable_key: 'closed_by',
      sortable: true,
    },
    { title: t('pos::pos_sessions.table.status'), value: 'status', sortable: true },
    { title: t('pos::pos_sessions.table.opened_at'), value: 'opened_at', sortable: true },
    { title: t('pos::pos_sessions.table.closed_at'), value: 'closed_at', sortable: true },
  ]

  const onClickClose = (item: any) => {
    if (can('admin.pos_sessions.close')) {
      closeDialog.value = true
      closeSession.value = item
    }
  }

  const onClickCashMovements = (item: any) => {
    if (can('admin.pos_cash_movements.index')) {
      router.push({
        name: 'admin.pos_sessions.pos_cash_movements.index',
        params: { id: item.id },
      } as unknown as RouteLocationRaw)
    }
  }
  const actions: TableAction[] = [
    { key: 'show', onClick: onClickShow },
    {
      key: 'pos_cash_movements',
      icon: 'tabler-cash',
      permission: 'admin.pos_cash_movements.index',
      label: t('pos::pos_cash_movements.pos_cash_movements'),
      onClick: onClickCashMovements,
    },
    {
      key: 'close',
      icon: 'tabler-logout',
      label: t('pos::pos_sessions.close_session'),
      onClick: onClickClose,
      hidden: item => item.status.id == 'closed',
    },
  ]

  const onClosedSession = () => {
    smartTableRef.value?.refresh()
    closeSession.value = null
  }

  const onClickOpen = () => {
    if (can('admin.pos_sessions.open')) {
      openDialog.value = true
    }
  }

  const onOpenedSession = () => {
    smartTableRef.value?.refresh()
  }

</script>

<template>
  <SmartDataTable
    ref="smartTableRef"
    :actions="actions"
    api-uri="/v1/pos/sessions"
    :has-search="false"
    :header-actions="[{
      key: 'open',
      icon:'tabler-login',
      label:t('pos::pos_sessions.open_session'),
      onClick: onClickOpen
    }]"
    :headers="headers"
    module="pos"
    name="pos_session"
    resource="pos_sessions"
  />
  <OpenDialog
    v-if="openDialog && can('admin.pos_sessions.open')"
    v-model="openDialog"
    @saved="onOpenedSession"
  />
  <CloseDialog
    v-if="closeDialog && closeSession && can('admin.pos_sessions.close')"
    v-model="closeDialog"
    :item="closeSession"
    @saved="onClosedSession"
  />
  <ShowDialog
    v-if="showDialog && showSession && can('admin.pos_sessions.show')"
    :id="showSession.id"
    v-model="showDialog"
  />
</template>
