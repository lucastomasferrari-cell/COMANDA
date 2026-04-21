<script lang="ts" setup>
  import type { UseCart } from '@/modules/cart/composables/cart.ts'
  import type { PosForm, PosMeta } from '@/modules/pos/contracts/posViewer.ts'

  const props = defineProps<{
    form: PosForm
    meta: PosMeta
    cart: UseCart
  }>()

  const loading = ref<boolean>(false)
  const { form } = toRefs(props)

  const removeTable = async () => {
    loading.value = true
    await (props.meta.orderTypes[0].id ? props.cart.storeOrderType(props.meta.orderTypes[0].id) : props.cart.removeOrderType())
    form.value.table = null
    loading.value = false
  }

</script>

<template>
  <div v-if="form.table" class="d-flex justify-space-between align-center mb-3">
    <div>
      <div class="text-h6 font-weight-bold">{{ form.table.name }}</div>
      <div class="text-caption text-grey">
        {{ form.table.zone }} • {{ form.table.floor }}
      </div>
    </div>
    <VBtn
      :disabled="loading"
      icon
      :loading="loading"
      text
      variant="text"
      @click="removeTable"
    >
      <VIcon icon="tabler-trash" />
    </VBtn>
  </div>
</template>
