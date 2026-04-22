<script lang="ts" setup>
  import { useI18n } from 'vue-i18n'
  import DiscountIndex from '@/modules/promotion/pages/admin/discount/Index.vue'
  import LoyaltyPromotionIndex from '@/modules/loyalty/pages/admin/promotion/Index.vue'

  const { t } = useI18n()
</script>

<template>
  <div class="d-flex flex-column ga-8">
    <section>
      <h3 class="text-h6 font-weight-medium mb-4">
        {{ t('admin::sidebar.discounts') }}
      </h3>
      <DiscountIndex />
    </section>

    <section>
      <h3 class="text-h6 font-weight-medium mb-4">
        {{ t('admin::sidebar.loyalty_promotions') }}
      </h3>
      <LoyaltyPromotionIndex />
    </section>
  </div>
</template>
