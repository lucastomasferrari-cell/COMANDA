<script lang="ts" setup>
  import type { AxiosError } from 'axios'
  import { useI18n } from 'vue-i18n'
  import { useToast } from 'vue-toastification'
  import { useConfirmDialog } from '@/modules/core/composables/confirmDialog.ts'
  import { useLoyaltyGift } from '@/modules/loyalty/composables/loyaltyGift.ts'
  import RewardItem from './RewardItem.vue'

  const props = defineProps<{
    rewards: Record<string, any>[]
    customerId: number
  }>()
  const emit = defineEmits(['redeemed'])

  const { t } = useI18n()
  const { redeem } = useLoyaltyGift()
  const toast = useToast()
  const processing = ref({
    id: 0,
    loading: false,
  })

  async function redeemReward (reward: Record<string, any>, qty = 1) {
    const confirmed = await useConfirmDialog({
      title: t('pos::pos_viewer.redeem_confirmation.title'),
      message: t('pos::pos_viewer.redeem_confirmation.message', { points_cost: reward.points_cost }),
    })
    if (!confirmed) {
      return
    }

    try {
      processing.value.id = reward.id
      processing.value.loading = true
      const response = await redeem(props.customerId, reward.id, qty)
      toast.success(response.data.message)
      emit('redeemed')
    } catch (error) {
      console.error('error :', error)
      toast.error((error as AxiosError<{
        message?: string
      }>).response?.data?.message || t('core::errors.an_unexpected_error_occurred'))
    } finally {
      processing.value.id = 0
      processing.value.loading = false
    }
  }
</script>

<template>
  <v-container fluid>
    <h2 class="text-h6 font-weight-bold mb-6">🎁 {{ t('pos::pos_viewer.available_rewards') }}</h2>
    <v-row>
      <RewardItem
        v-for="reward in rewards"
        :key="reward.id"
        :processing="processing"
        :reward="reward"
        @redeem-reward="redeemReward"
      />
    </v-row>
  </v-container>
</template>
