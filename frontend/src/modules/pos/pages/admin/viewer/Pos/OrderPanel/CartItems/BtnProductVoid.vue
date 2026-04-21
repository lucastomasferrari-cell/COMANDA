<script lang="ts" setup>
  import type { CartItem } from '@/modules/cart/composables/cart.ts'
  import { useI18n } from 'vue-i18n'

  const props = withDefaults(defineProps<{
    cartItem: CartItem
    label: string
    icon: string
    color: string
    action: 'cancel' | 'refund'
    loading?: boolean
  }>(), {
    loading: false,
  })

  const emit = defineEmits(['on-submit'])

  const { t } = useI18n()
  const menu = ref(false)
  const quantity = ref<null | number>(1)

  const onQuantityInput = (event: Event) => {
    const input = event.target as HTMLInputElement
    input.value = input.value.replace(/[^0-9]/g, '')
    quantity.value = Math.min(Number(input.value), props.cartItem.qty)
  }

  const disabledSubmit = computed(() => !quantity.value || quantity.value == 0 || quantity.value > props.cartItem.qty)

  const submit = () => {
    if (!disabledSubmit.value) {
      emit('on-submit', quantity.value)
      menu.value = false
    }
  }
</script>

<template>
  <VMenu
    v-model="menu"
    :close-on-content-click="false"
    location="top"
    offset="8"
  >
    <template #activator="{ props:propsMenu }">
      <VTooltip
        :text="label"
      >
        <template #activator="{ props:tooltipProps }">
          <VBtn
            :color="color"
            :icon="icon"
            :loading="loading"
            v-bind="{...propsMenu,...tooltipProps}"
            variant="text"
          />
        </template>
      </VTooltip>
    </template>

    <VCard class="pa-4" max-width="400" width="350">
      <VRow>
        <VCol cols="12">
          <VTextField
            v-model="quantity"
            clearable
            inputmode="numeric"
            :label="t('pos::pos_viewer.quantity')"
            :max="cartItem.qty"
            min="1"
            pattern="[0-9]*"
            :placeholder="t(`pos::pos_viewer.enter_the_quantity_to_be_${action === 'cancel' ? 'cancelled' : 'refunded'}`)"
            type="number"
            @input="onQuantityInput"
          />
        </VCol>
        <VCol class="text-end" cols="12">
          <VBtn
            color="default"
            :disabled="disabledSubmit"
            size="small"
            variant="tonal"
            @click="submit"
          >
            <VIcon class="mr-2" :color="color" :icon="icon" />
            {{ label }}
          </VBtn>
        </VCol>
      </VRow>
    </VCard>
  </VMenu>
</template>
