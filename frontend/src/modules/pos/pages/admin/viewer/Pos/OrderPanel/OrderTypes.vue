<script lang="ts" setup>
  import type { UseCart } from '@/modules/cart/composables/cart.ts'
  import type { PosForm, PosMeta } from '@/modules/pos/contracts/posViewer.ts'
  import { computed, ref, toRefs, watch } from 'vue'
  import { useI18n } from 'vue-i18n'

  const { t } = useI18n()

  const props = defineProps<{
    meta: PosMeta
    form: PosForm
    cart: UseCart
  }>()

  defineEmits<{
    (e: 'on-click-action', value: string): void
  }>()

  const { processing, data, storeOrderType } = props.cart
  const loading = ref(false)
  const targetId = ref<string | null>(null)
  const { form } = toRefs(props)

  // Senal real: la orden tiene mesa o no. Si tiene mesa, el canal es dine_in
  // fijo (el cashier no puede cambiar el tipo sin soltar la mesa). Si no
  // tiene mesa, solo ofrecemos los tipos no-dine_in como opciones editables.
  const hasTable = computed(() => form.value.table != null)
  const nonDineInTypes = computed(() => (props.meta.orderTypes ?? []).filter(type => type.id !== 'dine_in'))

  const toggleOrderType = async (id: string) => {
    if (loading.value || processing.value) return
    if (id === data.value.orderType?.id) return
    loading.value = true
    targetId.value = id
    try {
      await storeOrderType(id)
    } finally {
      loading.value = false
      targetId.value = null
    }
  }

  // Si no hay mesa y el canal actual es dine_in (o no hay ninguno), auto-set
  // al primer tipo no-dine_in disponible. Evita que la orden quede sin canal.
  watch(
    [hasTable, nonDineInTypes, () => data.value.orderType?.id],
    async ([tableOn, types, currentId]) => {
      if (tableOn || loading.value || processing.value) return
      if (currentId && currentId !== 'dine_in') return
      const fallback = types?.[0]
      if (!fallback || fallback.id === currentId) return
      loading.value = true
      targetId.value = fallback.id
      try {
        await storeOrderType(fallback.id)
      } finally {
        loading.value = false
        targetId.value = null
      }
    },
    { immediate: true },
  )
</script>

<template>
  <!-- Con mesa asignada: canal fijo en dine_in. Renderizamos una chip
       visual bloqueada para que el cajero vea que la orden es de Salon
       y que ese canal no es editable (pistas visuales > texto explicativo).
       TableInfo.vue muestra piso / zona / nombre de la mesa debajo. -->
  <div v-if="hasTable" class="order-type-scroll">
    <div class="scroll-container">
      <div class="order-type-card active locked">
        <div class="type-info">
          <VIcon color="primary" icon="tabler-brand-airtable" />
          <span class="name">{{ t('pos::pos_viewer.order_types.dine_in_locked') }}</span>
          <VIcon class="lock-icon" icon="tabler-lock" size="14" />
        </div>
      </div>
    </div>
  </div>
  <div v-else-if="nonDineInTypes.length > 0" class="order-type-scroll">
    <div class="scroll-container">
      <div
        v-for="type in nonDineInTypes"
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
  border: 1px dashed rgba(var(--v-theme-on-surface), 0.12);
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

.order-type-card.locked {
  cursor: not-allowed;

  &:hover {
    border-color: rgb(var(--v-theme-primary));
    background-color: rgba(var(--v-theme-primary), 0.08);
  }

  .lock-icon {
    margin-inline-start: 4px;
    color: rgba(var(--v-theme-on-surface), 0.45);
  }
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
