<script lang="ts" setup>
  import { useI18n } from 'vue-i18n'

  const props = defineProps<{
    product: Record<string, any>
  }>()

  const { t } = useI18n()

</script>

<template>
  <VCard class="product-card d-flex align-center">
    <VCardText class="d-flex align-center pt-2 pb-2">
      <div class="image-container">
        <VImg
          v-if="props.product.thumbnail"
          class="rounded"
          cover
          height="80"
          :src="props.product.thumbnail"
          width="80"
        />
        <VIcon v-else class="icon" icon="tabler-soup" size="80" />
        <div v-if="product.is_new" class="badge-new">
          {{ t('pos::pos.new').toUpperCase() }}
        </div>
      </div>

      <div class="info-area">
        <div class="product-name">
          {{ product.name }}
        </div>

        <div class="product-price">
          <span v-if="product.selling_price.amount < product.price.amount" class="special">
            {{ product.selling_price.formatted }}
          </span>
          <span :class="{ 'original': product.selling_price.amount < product.price.amount }">
            {{ product.price.formatted }}
          </span>
        </div>
      </div>
    </VCardText>
  </VCard>
</template>

<style lang="scss" scoped>
.product-card {
  border-radius: 16px;
  overflow: hidden;
  transition: all 0.2s ease;
  background-color: white;

  .image-container {
    position: relative;
    display: flex;
    justify-content: center;
    align-items: center;
    min-width: 80px;
    height: 80px;

    .icon {
      color: rgb(var(--v-theme-grey-500));
    }

    .new-badge {
      position: absolute;
      top: 10px;
      right: 10px;
      background-color: rgb(var(--v-theme-error));
      color: white;
      padding: 4px 8px;
      font-size: 0.75rem;
      font-weight: bold;
      border-radius: 4px;
    }
  }

  .info-area {
    padding-left: 16px;
    padding-right: 8px;
    width: 100%;
  }

  .product-name {
    font-weight: 600;
    font-size: 1.05rem;
    margin-bottom: 6px;
    color: #333;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
  }

  .product-price {
    font-size: 0.95rem;
    color: #444;

    .original {
      text-decoration: line-through;
      color: #aaa;
      margin-left: 0.5rem;
    }

    .special {
      color: #e53935;
      font-weight: 600;
    }
  }
}
</style>
