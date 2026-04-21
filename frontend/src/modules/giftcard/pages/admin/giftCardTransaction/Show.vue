<script lang="ts" setup>
  import PageStateWrapper from '@/modules/core/components/PageStateWrapper.vue'
  import TableEnum from '@/modules/core/components/Table/Partials/TableEnum.vue'
  import TableMoney from '@/modules/core/components/Table/Partials/TableMoney.vue'
  import { useGiftCardTransaction } from '@/modules/giftcard/composables/giftCardTransaction.ts'
  import { useI18n } from 'vue-i18n'

  const { getShowData } = useGiftCardTransaction()
  const route = useRoute()
  const router = useRouter()
  const { t } = useI18n()

  const loading = ref(false)
  const isNotFound = ref(false)
  const item = ref<Record<string, any> | null>(null)

  function openGiftCard () {
    if (!item.value?.gift_card?.id) return

    router.push({
      name: 'admin.gift_cards.show',
      params: { id: item.value.gift_card.id },
    })
  }

  onBeforeMount(async () => {
    loading.value = true
    const response = await getShowData((route.params as Record<string, any>).id)
    if (response.status === 200) {
      item.value = response.data
    } else if (response.status === 404) {
      isNotFound.value = true
    }
    loading.value = false
  })
</script>

<template>
  <PageStateWrapper :loading="loading" :not-found="isNotFound">
    <VRow v-if="item">
      <VCol cols="12" lg="8">
        <VCard class="mb-4">
          <VCardTitle class="border-b pb-2 mb-4 d-flex align-center gap-2 font-weight-bold text-h6">
            <VIcon icon="tabler-arrows-exchange" />
            {{ t('giftcard::gift_card_transactions.gift_card_transaction') }}
          </VCardTitle>
          <VCardText>
            <VRow>
              <VCol cols="12" md="4">
                <div class="text-caption text-medium-emphasis mb-1">UUID</div>
                <div class="font-weight-medium">{{ item.uuid }}</div>
              </VCol>
              <VCol cols="12" md="4">
                <div class="text-caption text-medium-emphasis mb-1">
                  {{ t('giftcard::gift_card_transactions.table.gift_card') }}
                </div>
                <VBtn
                  v-if="item.gift_card?.code"
                  class="px-0 text-none"
                  color="primary"
                  variant="text"
                  @click="openGiftCard"
                >
                  {{ item.gift_card.code }}
                </VBtn>
                <div v-else class="font-weight-medium">-</div>
              </VCol>
              <VCol cols="12" md="4">
                <div class="text-caption text-medium-emphasis mb-1">
                  {{ t('giftcard::gift_card_transactions.table.order') }}
                </div>
                <div class="font-weight-medium">{{ item.order?.reference_no || '-' }}</div>
              </VCol>
              <VCol cols="12" md="4">
                <div class="text-caption text-medium-emphasis mb-1">
                  {{ t('giftcard::gift_card_transactions.table.type') }}
                </div>
                <TableEnum :item="item" column="type" />
              </VCol>
              <VCol cols="12" md="4">
                <div class="text-caption text-medium-emphasis mb-1">
                  {{ t('giftcard::gift_card_transactions.table.amount') }}
                </div>
                <TableMoney :item="item" column="amount" />
              </VCol>
              <VCol cols="12" md="4">
                <div class="text-caption text-medium-emphasis mb-1">
                  {{ t('giftcard::gift_card_transactions.table.balance_before') }}
                </div>
                <TableMoney :item="item" column="balance_before" />
              </VCol>
              <VCol cols="12" md="4">
                <div class="text-caption text-medium-emphasis mb-1">
                  {{ t('giftcard::gift_card_transactions.table.balance_after') }}
                </div>
                <TableMoney :item="item" column="balance_after" />
              </VCol>
              <VCol cols="12" md="4">
                <div class="text-caption text-medium-emphasis mb-1">
                  {{ t('giftcard::attributes.gift_card_transactions.branch_id') }}
                </div>
                <div class="font-weight-medium">{{ item.branch?.name || '-' }}</div>
              </VCol>
              <VCol cols="12" md="4">
                <div class="text-caption text-medium-emphasis mb-1">
                  {{ t('giftcard::gift_card_transactions.table.user') }}
                </div>
                <div class="font-weight-medium">{{ item.createdBy?.name || '-' }}</div>
              </VCol>
              <VCol cols="12" md="4">
                <div class="text-caption text-medium-emphasis mb-1">
                  {{ t('giftcard::attributes.gift_card_transactions.exchange_rate') }}
                </div>
                <div class="font-weight-medium">{{ item.exchange_rate || '-' }}</div>
              </VCol>
              <VCol cols="12" md="4">
                <div class="text-caption text-medium-emphasis mb-1">
                  {{ t('giftcard::attributes.gift_card_transactions.amount_in_order_currency') }}
                </div>
                <div class="font-weight-medium">
                  {{ item.amount_in_order_currency?.formatted || item.amount_in_order_currency || '-' }}
                </div>
              </VCol>
              <VCol cols="12" md="4">
                <div class="text-caption text-medium-emphasis mb-1">
                  {{ t('giftcard::gift_card_transactions.table.transaction_at') }}
                </div>
                <div class="font-weight-medium">{{ item.transaction_at }}</div>
              </VCol>
              <VCol cols="12" md="4">
                <div class="text-caption text-medium-emphasis mb-1">
                  {{ t('admin::admin.table.created_at') }}
                </div>
                <div class="font-weight-medium">{{ item.created_at }}</div>
              </VCol>
              <VCol cols="12">
                <div class="text-caption text-medium-emphasis mb-1">
                  {{ t('giftcard::attributes.gift_card_transactions.notes') }}
                </div>
                <div class="font-weight-medium">{{ item.notes || '-' }}</div>
              </VCol>
            </VRow>
          </VCardText>
        </VCard>
      </VCol>
    </VRow>
  </PageStateWrapper>
</template>
