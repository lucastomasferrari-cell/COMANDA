<script lang="ts" setup>
  import type { TableHeader } from '@/modules/core/contracts/Table.ts'
  import { useI18n } from 'vue-i18n'
  import PageStateWrapper from '@/modules/core/components/PageStateWrapper.vue'
  import TableEnum from '@/modules/core/components/Table/Partials/TableEnum.vue'
  import TableMoney from '@/modules/core/components/Table/Partials/TableMoney.vue'
  import TableStatus from '@/modules/core/components/Table/Partials/TableStatus.vue'
  import SmartDataTable from '@/modules/core/components/Table/SmartDataTable.vue'
  import { useGiftCard } from '@/modules/giftcard/composables/giftCard.ts'

  const { getShowData } = useGiftCard()
  const route = useRoute()
  const { t, locale } = useI18n()

  const loading = ref(false)
  const isNotFound = ref(false)
  const item = ref<Record<string, any> | null>(null)

  const transactionsHeaders: TableHeader[] = [
    { title: t('giftcard::gift_card_transactions.table.type'), value: 'type', sortable: true },
    { title: t('giftcard::gift_card_transactions.table.amount'), value: 'amount', sortable: true },
    {
      title: t('giftcard::gift_card_transactions.table.balance_before'),
      value: 'balance_before',
      sortable: true,
    },
    {
      title: t('giftcard::gift_card_transactions.table.balance_after'),
      value: 'balance_after',
      sortable: true,
    },
    {
      title: t('giftcard::gift_card_transactions.table.order'),
      value: 'order.reference_no',
      sortable: false,
    },
    {
      title: t('giftcard::gift_card_transactions.table.user'),
      value: 'createdBy.name',
      sortable_key: 'created_by',
      sortable: true,
    },
    {
      title: t('giftcard::gift_card_transactions.table.transaction_at'),
      value: 'transaction_at',
      sortable: true,
    },
  ]

  const transactionCellComponents: Record<string, any> = {
    type: {
      component: TableEnum,
      props: { column: 'type' },
    },
    amount: {
      component: TableMoney,
      props: { column: 'amount' },
    },
    balance_before: {
      component: TableMoney,
      props: { column: 'balance_before' },
    },
    balance_after: {
      component: TableMoney,
      props: { column: 'balance_after' },
    },
  }

  const translatedBatchName = computed(() => {
    const name = item.value?.batch?.name
    if (!name) return null
    if (typeof name === 'string') return name
    return name[locale.value] || name.en || Object.values(name)[0]
  })

  onBeforeMount(async () => {
    loading.value = true
    const response = await getShowData((route.params as Record<string, any>).id, true)
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
      <VCol cols="12" lg="9">
        <VCard class="mb-4">
          <VCardTitle
            class="border-b pb-2 mb-4 d-flex align-center gap-2 font-weight-bold text-h6"
          >
            <VIcon icon="tabler-gift-card" />
            {{ t('giftcard::gift_cards.gift_card') }}
          </VCardTitle>
          <VCardText>
            <VRow>
              <VCol cols="12" md="4">
                <div class="text-caption text-medium-emphasis mb-1">
                  {{ t('giftcard::attributes.gift_cards.code') }}
                </div>
                <div class="font-weight-medium">{{ item.code }}</div>
              </VCol>
              <VCol cols="12" md="4">
                <div class="text-caption text-medium-emphasis mb-1">
                  {{ t('giftcard::attributes.gift_cards.branch_id') }}
                </div>
                <div class="font-weight-medium">{{ item.branch?.name || '-' }}</div>
              </VCol>
              <VCol cols="12" md="4">
                <div class="text-caption text-medium-emphasis mb-1">
                  {{ t('giftcard::attributes.gift_cards.customer_id') }}
                </div>
                <div class="font-weight-medium">{{ item.customer?.name || '-' }}</div>
              </VCol>
              <VCol cols="12" md="4">
                <div class="text-caption text-medium-emphasis mb-1">
                  {{ t('giftcard::attributes.gift_cards.gift_card_batch_id') }}
                </div>
                <div class="font-weight-medium">{{ translatedBatchName || '-' }}</div>
              </VCol>
              <VCol cols="12" md="4">
                <div class="text-caption text-medium-emphasis mb-1">
                  {{ t('giftcard::attributes.gift_cards.initial_balance') }}
                </div>
                <div class="font-weight-medium">{{ item.initial_balance?.formatted || '-' }}</div>
              </VCol>
              <VCol cols="12" md="4">
                <div class="text-caption text-medium-emphasis mb-1">
                  {{ t('giftcard::attributes.gift_cards.current_balance') }}
                </div>
                <div class="font-weight-medium">{{ item.current_balance?.formatted || '-' }}</div>
              </VCol>
              <VCol cols="12" md="4">
                <div class="text-caption text-medium-emphasis mb-1">
                  {{ t('giftcard::attributes.gift_cards.expiry_date') }}
                </div>
                <div class="font-weight-medium">{{ item.expiry_date || '-' }}</div>
              </VCol>
              <VCol cols="12" md="4">
                <div class="text-caption text-medium-emphasis mb-1">
                  {{ t('giftcard::attributes.gift_cards.currency') }}
                </div>
                <div class="font-weight-medium">{{ item.currency || '-' }}</div>
              </VCol>

              <VCol cols="12" md="4">
                <div class="text-caption text-medium-emphasis mb-1">
                  {{ t('giftcard::attributes.gift_cards.scope') }}
                </div>
                <TableEnum column="scope" :item="item" />
              </VCol>
              <VCol cols="12" md="4">

                <div class="text-caption text-medium-emphasis mb-1">
                  {{ t('giftcard::attributes.gift_cards.status') }}
                </div>
                <TableStatus :item="item" />
              </VCol>
              <VCol cols="12" md="4">
                <div class="text-caption text-medium-emphasis mb-1">
                  {{ t('admin::admin.table.created_at') }}
                </div>
                <div class="font-weight-medium">{{ item.created_at }}</div>
              </VCol>
              <VCol cols="12" md="4">
                <div class="text-caption text-medium-emphasis mb-1">
                  {{ t('admin::admin.table.updated_at') }}
                </div>
                <div class="font-weight-medium">{{ item.updated_at }}</div>
              </VCol>
              <VCol cols="12">
                <div class="text-caption text-medium-emphasis mb-1">
                  {{ t('giftcard::attributes.gift_cards.notes') }}
                </div>
                <div class="font-weight-medium">{{ item.notes || '-' }}</div>
              </VCol>
            </VRow>
          </VCardText>
        </VCard>
        <SmartDataTable
          api-uri="/v1/gift-card-transactions"
          :cell-components="transactionCellComponents"
          :default-filters="{ gift_card_id: item.id }"
          :except-filters="['branch_id']"
          :headers="transactionsHeaders"
          module="giftcard"
          name="gift_card_transaction"
          resource="gift_card_transactions"
          :title="t('giftcard::gift_card_transactions.gift_card_transactions')"
        />
      </VCol>
    </VRow>
  </PageStateWrapper>
</template>
