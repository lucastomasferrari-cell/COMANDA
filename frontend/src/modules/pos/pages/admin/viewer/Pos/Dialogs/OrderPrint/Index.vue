<script lang="ts" setup>
  import { computed } from 'vue'
  import { useI18n } from 'vue-i18n'
  import { useAuth } from '@/modules/auth/composables/auth.ts'
  import { useQintrix } from '@/modules/printer/composables/qintrix.ts'
  import { useOrder } from '@/modules/sale/composables/order.ts'
  import PrintContent from './PrintContent.vue'

  const props = defineProps<{
    modelValue: boolean
    orderId: string | number
    registerId?: number | null
    branchId?: number | null
  }>()

  const emit = defineEmits<{
    (e: 'update:modelValue', value: boolean): void
  }>()

  const { t } = useI18n()
  const { getPrintMeta } = useOrder()
  const { user } = useAuth()

  const loading = ref(true)
  const qintrix = useQintrix()
  const meta = reactive<{
    registers: Record<string, any>[]
    branches: Record<string, any>[]
    contents: Record<string, any>[]
    branch_id: null | number
    register_id: null | number
  }>({
    registers: [],
    branches: [],
    contents: [],
    branch_id: props.branchId || null,
    register_id: props.registerId || null,
  })

  const dialogModel = computed({
    get: () => props.modelValue,
    set: (val: boolean) => emit('update:modelValue', val),
  })

  const close = () => emit('update:modelValue', false)

  onBeforeMount(() => {
    loadFormData(meta.branch_id, meta.register_id)
  })

  async function loadFormData (branchId?: number | null, registerId?: number | null) {
    try {
      const response = (await getPrintMeta(props.orderId, branchId, registerId)).data.body

      if (!branchId) {
        meta.branches = response.branches
      }
      meta.registers = response.registers || meta.registers
      meta.contents = response.contents || meta.contents
      meta.branch_id = response.branch_id || meta.branch_id
      if (response.agent) {
        qintrix.initClient(response.agent.base_url, response.agent.api_key)
      }
    } catch {
    /* Empty */
    } finally {
      loading.value = false
    }
  }

  watch(() => meta.branch_id, newValue => {
    meta.register_id = null
    meta.registers = []
    if (newValue) {
      loadFormData(newValue)
    }
  })
</script>

<template>
  <VDialog
    v-model="dialogModel"
    height="60vh"
    max-width="30%"
    scrollable
  >
    <VCard>
      <div v-if="loading" class="d-flex h-100 justify-center align-center py-16">
        <VProgressCircular color="primary" indeterminate size="50" />
      </div>
      <template v-else>
        <VCardText>
          <div class="text-h5 font-weight-medium mb-1">
            {{ t('order::orders.print_dialog.title') }}
          </div>
          <div class="text-body-2 text-medium-emphasis mb-4">
            {{ t('order::orders.print_dialog.description') }}
          </div>
          <VDivider class="mb-4 dashed-divider" />
          <VRow v-if="!registerId" class="mb-2">
            <VCol v-if="!user?.assigned_to_branch" cols="12" md="6">
              <VSelect
                v-model="meta.branch_id"
                item-title="name"
                item-value="id"
                :items="meta.branches"
                :label="t('order::attributes.print.branch_id')"
              />
            </VCol>
            <VCol cols="12" md="6">
              <VSelect
                v-model="meta.register_id"
                item-title="name"
                item-value="id"
                :items="meta.registers"
                :label="t('order::attributes.print.register_id')"
              />
            </VCol>
          </VRow>
          <VRow dense>
            <VCol v-for="(printContent,index) in meta.contents" :key="index" cols="12">
              <PrintContent
                :content="printContent"
                :order-id="orderId"
                :qintrix="qintrix"
                :register-id="meta.register_id"
              />
              <VDivider class="dashed-divider" />
            </VCol>
          </VRow>
        </VCardText>
      </template>
      <VCardActions>
        <VSpacer />
        <VBtn color="default" @click="close">
          {{ t('admin::admin.buttons.cancel') }}
        </VBtn>
      </VCardActions>
    </VCard>
  </VDialog>
</template>

<style lang="scss" scoped>
.dashed-divider {
  border-style: dashed;
}

</style>
