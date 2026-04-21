<script lang="ts" setup>
  import type { UseCart } from '@/modules/cart/composables/cart.ts'
  import type { PosForm, PosMeta } from '@/modules/pos/contracts/posViewer.ts'
  import { computed, ref } from 'vue'
  import { useI18n } from 'vue-i18n'
  import { useAuth } from '@/modules/auth/composables/auth.ts'
  import AvailableGiftsDialog
    from '@/modules/pos/pages/admin/viewer/Pos/Dialogs/AvailableGifts/Index.vue'
  import RewardsDialog from '@/modules/pos/pages/admin/viewer/Pos/Dialogs/Rewards/Index.vue'
  import AddCustomerDialog
    from '@/modules/pos/pages/admin/viewer/Pos/OrderPanel/AddCustomerDialog.vue'

  const props = defineProps<{
    form: PosForm
    meta: PosMeta
    cart: UseCart
  }>()

  const { t } = useI18n()
  const { can, hasRole } = useAuth()

  const { processing, data, addCustomer, removeCustomer } = props.cart

  const showRewardsDialog = ref(false)
  const showGiftDialog = ref(false)
  const showAddCustomerDialog = ref(false)
  const createdCustomers = ref<Record<string, any>[]>([])
  const { form } = toRefs(props)

  const customerItems = computed(() => [
    ...createdCustomers.value,
    ...props.meta.customers.filter(customer => !createdCustomers.value.some(item => item.id === customer.id)),
  ])

  watch(
    () => data.value.customer,
    async (newValue, oldValue) => {
      if (form.value.mode === 'edit' && form.value.skipFirst) {
        form.value.skipFirst = false
        return
      }
      if (newValue?.id !== oldValue?.id) {
        await (newValue ? addCustomer(newValue.id) : removeCustomer())
      }
    })

  function showAvailableGifts () {
    showGiftDialog.value = true
  }

  function showRewards () {
    if (can('admin.loyalty_gifts.index_rewards')) {
      showRewardsDialog.value = true
    }
  }

  function onCustomerCreated (customer: Record<string, any>) {
    if (!createdCustomers.value.some(item => item.id === customer.id)) {
      createdCustomers.value.unshift(customer)
    }
    data.value.customer = customer
  }

</script>

<template>
  <div class="mt-2 d-flex align-center ga-2">
    <VAutocomplete
      v-if="!hasRole('waiter')"
      v-model="form.waiter"
      autocomplete="off"
      class=" flex-grow-1"
      clearable
      item-title="name"
      item-value="id"
      :items="meta.waiters"
      :menu-props="{ maxHeight: 250 }"
      :placeholder="t('pos::pos_viewer.select_waiter')"
      prepend-inner-icon="tabler-user-pentagon"
      return-object
      width="100px"
    />
    <VAutocomplete
      v-model="data.customer"
      autocomplete="off"
      class=" flex-grow-1"
      :clearable="!processing"
      item-title="name"
      item-value="id"
      :items="customerItems"
      :menu-props="{ maxHeight: 250 }"
      :placeholder="t('pos::pos_viewer.select_customer')"
      prepend-inner-icon="tabler-users"
      :readonly="processing"
      return-object
      width="100px"
    />
    <VBtn
      v-if="can('admin.customers.create')"
      color="primary"
      :disabled="processing"
      icon="tabler-user-plus"
      variant="tonal"
      @click="showAddCustomerDialog = true"
    />
    <VMenu>
      <template #activator="{ props:menuProps }">
        <VBtn
          color="error"
          :disabled="processing || !data.customer"
          style="width: 130px"
          v-bind="menuProps"
        >
          <VIcon icon="tabler-gift" start />
          {{ t('admin::admin.buttons.actions') }}
          <VIcon end icon="tabler-chevron-down" />
        </VBtn>
      </template>

      <VList>
        <VListItem v-if="can('admin.loyalty_gifts.index_rewards')" @click="showRewards">
          <VListItemTitle>
            <VIcon class="mr-2" icon="tabler-coins" start />
            {{ t('pos::pos_viewer.redeem_with_points') }}
          </VListItemTitle>
        </VListItem>

        <VListItem @click="showAvailableGifts">
          <VListItemTitle>
            <VIcon class="mr-2" icon="tabler-gift-card" start />
            {{ t('pos::pos_viewer.view_available_gifts') }}
          </VListItemTitle>
        </VListItem>
      </VList>
    </VMenu>
  </div>

  <AvailableGiftsDialog
    v-if="data.customer?.id && showGiftDialog"
    v-model="showGiftDialog"
    :cart="cart"
    :customer-id="data.customer?.id"
  />

  <RewardsDialog
    v-if="data.customer?.id && can('admin.loyalty_gifts.index_rewards') && showRewardsDialog"
    v-model="showRewardsDialog"
    :customer-id="data.customer?.id"
  />

  <AddCustomerDialog
    v-if="can('admin.customers.create') && showAddCustomerDialog"
    v-model="showAddCustomerDialog"
    @created="onCustomerCreated"
  />
</template>
