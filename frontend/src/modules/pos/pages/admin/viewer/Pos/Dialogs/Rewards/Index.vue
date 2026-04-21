<script lang="ts" setup>
  import { computed } from 'vue'
  import { useI18n } from 'vue-i18n'
  import { useLoyaltyGift } from '@/modules/loyalty/composables/loyaltyGift.ts'
  import CustomerInfo from './Customer.vue'
  import Empty from './Empty.vue'
  import Rewards from './Rewards.vue'

  const props = defineProps<{
    modelValue: boolean
    customerId: number
  }>()

  const emit = defineEmits<{
    (e: 'update:modelValue', value: boolean): void
    (e: 'redeemed'): void
  }>()

  const { t } = useI18n()
  const { getAllRewards } = useLoyaltyGift()

  const loading = ref(false)
  const isNotFound = ref(false)
  const customer = ref<Record<string, any> | null>(null)
  const rewards = ref<Record<string, any>[] | null>(null)

  const dialogModel = computed({
    get: () => props.modelValue,
    set: (val: boolean) => {
      emit('update:modelValue', val)
    },
  })

  async function close () {
    emit('update:modelValue', false)
  }

  onBeforeMount(async () => {
    loading.value = true
    const response = await getAllRewards(props.customerId)
    if (response.status === 200) {
      customer.value = response.data.customer
      rewards.value = response.data.eligible
    } else if (response.status === 404) {
      isNotFound.value = true
    }
    loading.value = false
  })

  async function redeemed () {
    const response = await getAllRewards(props.customerId)
    if (response.status === 200) {
      customer.value = response.data.customer
      rewards.value = response.data.eligible
      emit('redeemed')
    }
  }
</script>

<template>
  <VDialog
    v-model="dialogModel"
    height="80vh"
    max-width="75%"
    scrollable
  >
    <VCard>
      <VCardTitle
        class=" pb-2  d-flex align-center justify-space-between font-weight-bold text-h6"
      >
        <div class="d-flex align-center gap-1">
          <VIcon icon="tabler-gift" size="20" />
          {{ t('pos::pos_viewer.rewards_and_points_summary') }}
        </div>

        <VBtn
          color="default"
          icon
          size="small"
          variant="text"
          @click="close"
        >
          <VIcon icon="tabler-x" />
        </VBtn>
      </VCardTitle>
      <VCardText class="pt-2 container">
        <PageStateWrapper :loading="loading" :not-found="isNotFound">
          <template v-if="customer ||rewards">
            <CustomerInfo v-if="customer" :customer="customer" />
            <Rewards
              v-if="rewards && rewards.length>0"
              :customer-id="customerId"
              :rewards="rewards"
              @redeemed="redeemed"
            />
          </template>
          <template v-else>
            <Empty />
          </template>
        </PageStateWrapper>
      </VCardText>
    </VCard>
  </VDialog>
</template>
