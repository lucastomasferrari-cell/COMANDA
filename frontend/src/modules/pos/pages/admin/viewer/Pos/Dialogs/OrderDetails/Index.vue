<script lang="ts" setup>
  import { computed, ref } from 'vue'
  import { useI18n } from 'vue-i18n'
  import { useOrder } from '@/modules/sale/composables/order.ts'
  import Customer from '@/modules/sale/pages/admin/order/Partials/Show/Customer.vue'
  import Details from '@/modules/sale/pages/admin/order/Partials/Show/Details.vue'
  import Information from '@/modules/sale/pages/admin/order/Partials/Show/Information.vue'
  import Notes from '@/modules/sale/pages/admin/order/Partials/Show/Notes.vue'
  import Payments from '@/modules/sale/pages/admin/order/Partials/Show/Payments/Index.vue'
  import Products from '@/modules/sale/pages/admin/order/Partials/Show/Products/Index.vue'
  import Summary from '@/modules/sale/pages/admin/order/Partials/Show/Summary/Index.vue'

  const props = defineProps<{
    modelValue: boolean
    orderId: number | null | string
  }>()

  const emit = defineEmits<{
    (e: 'update:modelValue', value: boolean): void
  }>()

  const open = computed({
    get: () => props.modelValue,
    set: val => emit('update:modelValue', val),
  })

  const { getShowData } = useOrder()
  const { t } = useI18n()

  const loading = ref(false)
  const order = ref<Record<string, any> | null>(null)

  const close = () => emit('update:modelValue', false)

  onBeforeMount(async () => {
    await loadData()
  })

  const loadData = async () => {
    if (props.orderId) {
      loading.value = true
      const response = await getShowData(props.orderId)
      if (response.status === 200) {
        order.value = response.data
      }
      loading.value = false
    }
  }

</script>

<template>
  <VDialog
    v-model="open"
    height="80vh"
    max-width="90%"
    scrollable
  >
    <VCard>
      <VCardTitle
        class="d-flex align-center justify-space-between font-weight-bold text-h6"
      >
        <div class="d-flex align-center gap-1">
          {{ t('pos::pos.order_details') }}
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
      <VCardText class="order-details pa-3">
        <PageStateWrapper :loading="loading" :not-found="false">
          <VRow v-if="order" dense>
            <VCol cols="12" md="9">
              <VRow dense>
                <VCol cols="12">
                  <Information :order="order" />
                </VCol>
                <VCol v-if="order.customer" cols="12">
                  <Customer :customer="order.customer" />
                </VCol>
                <VCol cols="12">
                  <Products :products="order.products" />
                </VCol>
              </VRow>
            </VCol>
            <VCol cols="12" md="3">
              <VRow dense>
                <VCol cols="12">
                  <Summary :order="order" />
                </VCol>

                <Details :details="order.details" />

                <VCol cols="12">
                  <Notes :notes="order.notes" />
                </VCol>
                <VCol cols="12">
                  <Payments :payments="order.payments" />
                </VCol>
              </VRow>
            </VCol>
          </VRow>
        </PageStateWrapper>
      </VCardText>
    </vcard>
  </VDialog>
</template>

<style lang="scss" scoped>
.order-details {
  background-color: rgb(var(--v-theme-background)) !important;
}
</style>
