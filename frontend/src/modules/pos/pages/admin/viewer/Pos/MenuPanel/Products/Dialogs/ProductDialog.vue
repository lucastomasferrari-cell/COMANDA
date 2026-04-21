<script lang="ts" setup>
  import type { UseCart } from '@/modules/cart/composables/cart.ts'
  import { useI18n } from 'vue-i18n'
  import { useToast } from 'vue-toastification'
  import Options from './Options.vue'

  const props = defineProps<{
    modelValue: boolean
    product: any
    cart: UseCart
  }>()
  const emit = defineEmits(['update:modelValue', 'saved'])

  const { t } = useI18n()
  const toast = useToast()

  const { data, storeItem, processing } = props.cart
  const formOptions = ref<Record<string, any>>({})
  const selectedOptions = ref<Record<string, any>>({})
  const errors = ref<Record<string, string>>({})
  const loading = ref(false)

  watch(() => formOptions.value, newValue => {
    selectedOptions.value = {}
    if (newValue) {
      for (const key of Object.keys(newValue)) {
        const optionData = props.product.options.find((option: Record<string, any>) => option.id === Number.parseInt(key))
        selectedOptions.value[optionData.id] = newValue[key][key]
      }
    }
  }, { deep: true })

  const close = () => {
    emit('update:modelValue', false)
  }

  const addProductToCart = async () => {
    if (processing.value) {
      return
    }
    try {
      loading.value = true
      processing.value = true
      const response = await storeItem({
        product_id: props.product.id,
        options: selectedOptions.value,
        qty: 1,
      })
      data.value = response.data.body
      close()
    } catch (error: any) {
      if (error?.response?.status === 422 && error.response?.data?.errors) {
        const apiErrors = error.response.data.errors
        for (const field in apiErrors) {
          errors.value[field] = apiErrors[field][0]
        }
      } else if (error.response?.data?.message) {
        toast.error(error.response?.data?.message)
      } else {
        toast.error(t('core::errors.an_unexpected_error_occurred'))
      }
    } finally {
      loading.value = false
      processing.value = false
    }
  }
</script>

<template>
  <VDialog
    max-width="450"
    :model-value="modelValue"
    :persistent="loading"
    scrollable
    @update:model-value="emit('update:modelValue', $event)"
  >
    <VCard class="product-details">
      <VCardText>
        <VRow>
          <VCol cols="12">
            <VRow>
              <VCol cols="12" md="4">
                <div class="image-wrapper">
                  <VImg
                    v-if="product.thumbnail"
                    class="product-image"
                    cover
                    height="100"
                    rounded
                    :src="product.thumbnail"
                  />
                  <VIcon v-else class="icon" icon="tabler-soup" size="80" />
                </div>
              </VCol>
              <VCol cols="12" md="8">
                <div class="product-name">{{ product.name }}</div>
                <div class="product-price">
                  <span v-if="product.selling_price.amount < product.price.amount" class="selling-price">
                    {{ product.selling_price.formatted }}
                  </span>
                  <span :class="{ 'original-price': product.selling_price.amount < product.price.amount }">
                    {{ product.price.formatted }}
                  </span>
                </div>
              </VCol>
            </VRow>
          </VCol>
          <VCol cols="12">
            <Options
              v-for="option in product.options"
              :key="option.id"
              v-model="formOptions[option.id]"
              :errors="errors"
              :option="option"
            />
          </VCol>
        </VRow>
      </VCardText>
      <VCardActions>
        <VSpacer />
        <VBtn color="default" :disabled="loading" @click="close">
          {{ t('admin::admin.buttons.cancel') }}
        </VBtn>
        <VBtn
          color="primary"
          :disabled="loading || processing"
          :loading="loading"
          @click="addProductToCart"
        >
          <VIcon icon="tabler-shopping-cart-plus" start />
          {{ t('pos::pos_viewer.add_to_cart') }}
        </VBtn>
      </VCardActions>
    </VCard>
  </VDialog>
</template>
<style lang="scss" scoped>
.product-details {
  .product-name {
    font-weight: bold;
    font-size: 1rem;
    margin-bottom: 5px;
  }

  .image-wrapper {
    display: flex;
    align-items: center;
    justify-content: center;
  }

  .product-price {
    display: flex;
    gap: 0.5rem;
    align-items: center;

    .original-price {
      text-decoration: line-through;
      color: gray;
      font-size: 0.9rem;
    }

    .selling-price {
      color: #e53935;
      font-weight: 600;
    }
  }
}
</style>
