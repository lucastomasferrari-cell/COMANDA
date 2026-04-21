<script lang="ts" setup>
  import type { UseCart } from '@/modules/cart/composables/cart.ts'
  import { useI18n } from 'vue-i18n'
  import { useToast } from 'vue-toastification'
  import { useConfirmDialog } from '@/modules/core/composables/confirmDialog.ts'
  import GiftItem from './GiftItem.vue'

  const props = defineProps<{
    gifts: Record<string, any>[]
    customerId: number
    cart: UseCart
  }>()
  const emit = defineEmits(['applied'])

  const { t } = useI18n()
  const { applyGift } = props.cart

  const toast = useToast()
  const processing = ref({
    id: 0,
    loading: false,
  })

  const applyGiftToCart = async (gift: Record<string, any>) => {
    const confirmed = await useConfirmDialog({
      title: t('pos::pos_viewer.apply_confirmation.title'),
      message: t('pos::pos_viewer.apply_confirmation.message', { points_cost: gift.points_cost }),
    })
    if (!confirmed) {
      return
    }

    processing.value.id = gift.id
    processing.value.loading = true
    const isSuccess = await applyGift(gift.id)
    processing.value.id = 0
    processing.value.loading = false

    if (isSuccess) {
      toast.success(t('cart::carts.gift_added_successfully'))
      emit('applied')
    }
  }
</script>

<template>
  <v-row>
    <GiftItem
      v-for="gift in gifts"
      :key="gift.id"
      :gift="gift"
      :processing="processing"
      @apply="applyGiftToCart"
    />
  </v-row>
</template>
