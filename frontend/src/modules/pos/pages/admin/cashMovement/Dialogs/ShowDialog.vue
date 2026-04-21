<script lang="ts" setup>
  import { useI18n } from 'vue-i18n'
  import Money from '@/modules/core/components/Money.vue'
  import { usePosCashMovement } from '@/modules/pos/composables/posCashMovement.ts'
  import InfoItem from '../Partials/Show/InfoItem.vue'

  const props = defineProps<{ modelValue: boolean, id: number }>()
  const emit = defineEmits(['update:modelValue', 'saved'])

  const { getShowData } = usePosCashMovement()
  const { t } = useI18n()

  const loading = ref(false)
  const isNotFound = ref(false)
  const item = ref<Record<string, any> | null>(null)

  onBeforeMount(async () => {
    loading.value = true
    const response = await getShowData(props.id)
    if (response.status === 200) {
      item.value = response.data
    } else if (response.status === 404) {
      isNotFound.value = true
    }
    loading.value = false
  })

  function close () {
    emit('update:modelValue', false)
  }

</script>

<template>
  <VDialog
    max-width="950"
    :model-value="modelValue"
    @update:model-value="emit('update:modelValue', $event)"
  >
    <VCard>
      <PageStateWrapper :loading="loading" :not-found="isNotFound">
        <VCardTitle
          class="border-b pb-2 mb-4 d-flex align-center justify-space-between font-weight-bold text-h6"
        >
          {{ t('pos::pos_cash_movements.show.pos_cash_movement_details') }}
          <VBtn
            color="primary"
            icon="tabler-x"
            size="small"
            variant="text"
            @click="close"
          />
        </VCardTitle>
        <VCardText v-if="item">
          <div class="d-flex flex-wrap">
            <InfoItem :label="t('pos::pos_cash_movements.show.reference')" :value="item.reference" />
            <InfoItem
              :label="t('pos::pos_cash_movements.show.occurred_at')"
              :value="item.occurred_at"
            />
            <InfoItem
              :label="t('pos::pos_cash_movements.show.created_by')"
              :value="item.created_by.name"
            />
            <InfoItem
              :label="t('pos::pos_cash_movements.show.pos_register')"
              :value="item.pos_register.name"
            />
            <InfoItem :label="t('pos::pos_cash_movements.show.direction')">
              <TableEnum column="direction" :item="item" />
            </InfoItem>
            <InfoItem :label="t('pos::pos_cash_movements.show.reason')">
              <TableEnum column="reason" :item="item" />
            </InfoItem>
            <InfoItem :label="t('pos::pos_cash_movements.show.balance_before')">
              <Money :money="item.balance_before" />
            </InfoItem>
            <InfoItem :label="t('pos::pos_cash_movements.show.amount')">
              <Money :money="item.amount" />
            </InfoItem>
            <InfoItem :label="t('pos::pos_cash_movements.show.balance_after')">
              <Money :money="item.balance_after" />
            </InfoItem>
            <InfoItem :label="t('pos::pos_cash_movements.show.currency')" :value="item.currency" />
            <InfoItem
              :label="t('pos::pos_cash_movements.show.currency_rate')"
              :value="item.currency_rate"
            />
            <InfoItem :label="t('pos::pos_cash_movements.show.notes')" :value="item.notes" />
          </div>
        </VCardText>
      </PageStateWrapper>
    </VCard>
  </VDialog>
</template>
