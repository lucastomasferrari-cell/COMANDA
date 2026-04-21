<script lang="ts" setup>
  import type { UseCart } from '@/modules/cart/composables/cart.ts'
  import { computed } from 'vue'
  import { useI18n } from 'vue-i18n'
  import { useLoyaltyGift } from '@/modules/loyalty/composables/loyaltyGift.ts'
  import Empty from './Empty.vue'
  import Gifts from './Gifts.vue'

  const props = defineProps<{
    modelValue: boolean
    customerId: number
    cart: UseCart
  }>()

  const emit = defineEmits<{
    (e: 'update:modelValue', value: boolean): void
    (e: 'applied'): void
  }>()

  const { t } = useI18n()
  const { getAvailableGifts } = useLoyaltyGift()

  const loading = ref(false)
  const isNotFound = ref(false)
  const gifts = ref<Record<string, any>[] | null>(null)

  const dialogModel = computed({
    get: () => props.modelValue,
    set: (val: boolean) => {
      emit('update:modelValue', val)
    },
  })

  function close () {
    emit('update:modelValue', false)
  }

  onBeforeMount(async () => {
    loading.value = true
    const response = await getAvailableGifts(props.customerId)
    if (response.status === 200) {
      gifts.value = response.data
    } else if (response.status === 404) {
      isNotFound.value = true
    }
    loading.value = false
  })

  function applied () {
    emit('applied')
    close()
  }
</script>

<template>
  <VDialog
    v-model="dialogModel"
    height="80vh"
    max-width="70%"
    scrollable
  >
    <VCard>
      <VCardTitle
        class=" pb-2  d-flex align-center justify-space-between font-weight-bold text-h6"
      >
        <div class="d-flex align-center gap-1">
          <VIcon icon="tabler-gift" size="20" />
          {{ t('pos::pos_viewer.available_gifts') }}
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
          <Gifts
            v-if="gifts && gifts.length>0"
            :cart="cart"
            :customer-id="customerId"
            :gifts="gifts"
            @applied="applied"
          />
          <Empty v-else />
        </PageStateWrapper>
      </VCardText>
    </VCard>
  </VDialog>
</template>
