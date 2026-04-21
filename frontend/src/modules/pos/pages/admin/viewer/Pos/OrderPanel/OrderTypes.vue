<script lang="ts" setup>
  import type { UseCart } from '@/modules/cart/composables/cart.ts'
  import type { PosForm, PosMeta } from '@/modules/pos/contracts/posViewer.ts'
  import { ref } from 'vue'
  import { useAuth } from '@/modules/auth/composables/auth.ts'

  const props = defineProps<{
    meta: PosMeta
    form: PosForm
    cart: UseCart
  }>()

  const emit = defineEmits<{
    (e: 'on-click-action', value: string): void
  }>()

  const { processing, data, storeOrderType } = props.cart
  const { can } = useAuth()
  const loading = ref(false)
  const targetId = ref<string | null>(null)
  const { form } = toRefs(props)

  const toggleOrderType = async (id: string) => {
    if (loading.value || processing.value) return
    if (id === 'dine_in' && can('admin.tables.viewer')) {
      emit('on-click-action', 'table_viewer')
    } else {
      if (id !== data.value.orderType?.id) {
        form.value.table = null
        loading.value = true
        targetId.value = id
        await storeOrderType(id)
        loading.value = false
        targetId.value = null
      }
    }
  }
</script>

<template>
  <div class="order-type-scroll">
    <div class="scroll-container">
      <div
        v-for="type in meta.orderTypes"
        :key="type.id"
        class="order-type-card"
        :class="{ active: data.orderType?.id === type.id,'cursor-not-allowed':loading||processing }"
        @click="toggleOrderType(type.id)"
      >
        <div class="type-info">
          <VIcon v-if="type.icon" :color="type.color" :icon="type.icon" />
          <span class="name">{{ type.name }}</span>
        </div>
        <div v-if="loading && type.id == targetId" class="order-type-card-loading">
          <VProgressCircular
            color="primary"
            indeterminate
            size="20"
            width="2"
          />
        </div>
      </div>
    </div>
  </div>
</template>

<style lang="scss" scoped>

.order-type-scroll {
  overflow-x: auto;
  white-space: nowrap;
  padding: 0.25rem;

  &::-webkit-scrollbar {
    display: none;
  }
}

.scroll-container {
  display: flex;
  gap: 0.5rem;
}

.order-type-card {
  border: 1px dashed #e0e0e0;
  border-radius: 8px;
  padding: 0.5rem 1rem;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: all 0.25s ease;
  text-align: center;
  position: relative;

  &:not(.cursor-not-allowed):hover {
    border-color: rgb(var(--v-theme-primary));
    background-color: rgba(var(--v-theme-primary), 0.03);
  }
}

.type-info {
  display: flex;
  align-items: center;
  gap: 8px;

  .name {
    font-size: 0.8rem;
    font-weight: 700;
  }
}

.order-type-card.active {
  border-color: rgb(var(--v-theme-primary));
  background-color: rgba(var(--v-theme-primary), 0.08);
}

.order-type-card-loading {
  position: absolute;
  width: 100%;
  height: 100%;
  top: 0;
  left: 0;
  bottom: 0;
  right: 0;
  display: flex;
  justify-content: center;
  align-items: center;
  border-radius: 8px;
  background: rgba(0, 0, 0, 0.04);
}
</style>
