<script lang="ts" setup>

  import type { PosForm, PosMeta } from '@/modules/pos/contracts/posViewer.ts'
  import { useI18n } from 'vue-i18n'
  import { useAuth } from '@/modules/auth/composables/auth.ts'

  const props = defineProps<{
    form: PosForm
    meta: PosMeta
  }>()

  defineEmits<{
    (e: 'on-click-action', value: string): void
  }>()

  const { t } = useI18n()
  const { can, canAny } = useAuth()

  const hasOrderTypeDineIn = computed(() => props.meta.orderTypes?.some(type => type.id == 'dine_in'))

  const actions = [
    {
      id: 'table_viewer',
      icon: 'tabler-brand-airtable',
      visible: hasOrderTypeDineIn.value && can('admin.tables.viewer'),
      color: '#8e44ad',
    },
    {
      id: 'orders',
      icon: 'tabler-salad',
      color: '#2980b9',
      visible: canAny(['admin.orders.active', 'admin.orders.upcoming']),
    },
    {
      id: 'manage_cash_movement',
      icon: 'tabler-cash',
      color: '#16a085',
      visible: can('admin.pos_cash_movements.create'),
    },
  ]

</script>

<template>
  <div class="action-container">
    <template
      v-for="action in actions"
      :key="action.id"
    >
      <div
        v-if="action.visible"
        class="action"
        @click="$emit('on-click-action', action.id)"
      >
        <v-icon :color="action.color" :icon="action.icon" size="30" start />
        <span class="action-name">
          {{ t(`pos::pos_viewer.actions.${action.id}`) }}
        </span>
      </div>
    </template>
  </div>

</template>

<style lang="scss" scoped>
.action-container {
  display: flex;
  flex-direction: row;
  flex-wrap: wrap;
  margin-bottom: 0.9rem;
  justify-content: space-between;

  .action {
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    width: 30%;
    border-radius: 10px;
    padding: 0.4rem 0;
    cursor: pointer;
    border: 2px dashed #ededed;

    .action-name {
      font-weight: 700;
      font-size: 0.75rem;
    }
  }
}
</style>
