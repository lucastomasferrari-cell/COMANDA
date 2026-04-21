<script lang="ts" setup>
  import { useI18n } from 'vue-i18n'
  import { usePosSession } from '@/modules/pos/composables/posSession.ts'
  import CashSummary from '../Partials/Show/CashSummary.vue'
  import PaymentBreakdown from '../Partials/Show/PaymentBreakdown.vue'
  import SalesOrdersSummary from '../Partials/Show/SalesOrdersSummary.vue'
  import SessionInformation from '../Partials/Show/SessionInformation.vue'

  const props = defineProps<{ modelValue: boolean, id: number }>()
  const emit = defineEmits(['update:modelValue', 'saved'])

  const { getShowData } = usePosSession()
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

  const close = () => {
    emit('update:modelValue', false)
  }
</script>

<template>
  <VDialog
    max-width="1200"
    :model-value="modelValue"
    @update:model-value="emit('update:modelValue', $event)"
  >
    <VCard>
      <PageStateWrapper :loading="loading" :not-found="isNotFound">

        <VCardTitle class="border-b pb-2 mb-4 d-flex align-center justify-space-between font-weight-bold text-h6">
          {{ t('pos::pos_sessions.show.session_details') }}
          <VBtn
            color="primary"
            icon="tabler-x"
            size="small"
            variant="text"
            @click="close"
          />
        </VCardTitle>
        <VCardText>
          <VRow>
            <VCol cols="12">
              <SessionInformation :item="item" />
            </VCol>
            <VCol cols="12">
              <CashSummary :item="item" />
            </VCol>
            <VCol cols="12">
              <PaymentBreakdown :item="item" />
            </VCol>
            <VCol cols="12">
              <SalesOrdersSummary :item="item" />
            </VCol>
          </VRow>
        </VCardText>
      </PageStateWrapper>
    </VCard>
  </VDialog>
</template>
