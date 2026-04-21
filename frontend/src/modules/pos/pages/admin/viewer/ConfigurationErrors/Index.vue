<script lang="ts" setup>
  import type { RouteLocationRaw } from 'vue-router'
  import type { PosForm, PosMeta } from '@/modules/pos/contracts/posViewer.ts'
  import { useI18n } from 'vue-i18n'
  import { useAuth } from '@/modules/auth/composables/auth.ts'
  import OpenDialog from '@/modules/pos/pages/admin/session/Dialogs/OpenDialog.vue'
  import Alert from './Alert.vue'

  const props = defineProps<{
    form: PosForm
    meta: PosMeta
  }>()

  const emits = defineEmits<{
    (e: 'on-opened-session'): void
  }>()

  const { can } = useAuth()
  const { t } = useI18n()
  const router = useRouter()

  const openSessionDialog = ref(false)
  const { form } = toRefs(props)

  async function goToCreateBranch () {
    await router.push({ name: 'admin.branches.create' } as unknown as RouteLocationRaw)
  }

  async function goToCreateMenu () {
    await router.push({ name: 'admin.menus.create' } as unknown as RouteLocationRaw)
  }

  async function goToCreatePosRegister () {
    await router.push({ name: 'admin.pos_registers.create' } as unknown as RouteLocationRaw)
  }

  function openSession () {
    if (can('admin.pos_sessions.open')) {
      openSessionDialog.value = true
    }
  }

  function onOpenedSession (id: number) {
    form.value.sessionId = id
    emits('on-opened-session')
  }
</script>

<template>
  <v-container class="d-flex justify-center align-center" style="height: 75vh;">
    <Alert
      v-if="meta.branches.length ===0"
      :action-text="t('pos::pos_viewer.configuration_errors.no_branches_found.action_button')"
      :has-action-button="can('admin.branches.index') && can('admin.branches.create')"
      :message="t('pos::pos_viewer.configuration_errors.no_branches_found.message')"
      :on-click-action="goToCreateBranch"
      :title="t('pos::pos_viewer.configuration_errors.no_branches_found.title')"
    />
    <Alert
      v-else-if="meta.menus.length === 0"
      :action-text="t('pos::pos_viewer.configuration_errors.no_menus_found.action_button')"
      :has-action-button="can('admin.menus.index') && can('admin.menus.create')"
      :message="t('pos::pos_viewer.configuration_errors.no_menus_found.message')"
      :on-click-action="goToCreateMenu"
      :title="t('pos::pos_viewer.configuration_errors.no_menus_found.title')"
    />
    <Alert
      v-else-if="meta.registers.length === 0"
      :action-text="t('pos::pos_viewer.configuration_errors.no_registers_found.action_button')"
      :has-action-button="can('admin.pos_registers.index') && can('admin.pos_registers.create')"
      :message="t('pos::pos_viewer.configuration_errors.no_registers_found.message')"
      :on-click-action="goToCreatePosRegister"
      :title="t('pos::pos_viewer.configuration_errors.no_registers_found.title')"
    />
    <Alert
      v-else-if="!form.sessionId"
      :action-text="t('pos::pos_viewer.configuration_errors.no_open_session.action_button')"
      :has-action-button=" can('admin.pos_sessions.open')"
      :message="t('pos::pos_viewer.configuration_errors.no_open_session.message')"
      :on-click-action="openSession"
      :title="t('pos::pos_viewer.configuration_errors.no_open_session.title')"
    />
  </v-container>

  <OpenDialog
    v-if="!form.sessionId && openSessionDialog && can('admin.pos_sessions.open')"
    v-model="openSessionDialog"
    :branch-id="form.branchId"
    :pos-register-id="form.registerId"
    @saved="onOpenedSession"
  />

</template>
