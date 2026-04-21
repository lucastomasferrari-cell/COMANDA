<script lang="ts" setup>
  import { useI18n } from 'vue-i18n'

  const props = defineProps<{
    gift: Record<string, any>
    processing: Record<string, any>
  }>()
  const emit = defineEmits(['apply'])

  const { t } = useI18n()

  const applyGift = () => {
    emit('apply', props.gift, 1)
  }
</script>

<template>
  <VCol
    :key="gift.id"
    cols="12"
    lg="3"
    md="4"
    sm="6"
  >
    <VCard class="pa-3 gift-item hover:shadow-md transition-all">
      <div class="d-flex align-center mb-3">
        <VIcon
          class="mr-2"
          :color="gift.type.color"
          :icon="gift.type.icon"
          size="28"
        />
        <span class="font-weight-medium">{{ gift.type.name }}</span>
      </div>

      <div class="mb-3 d-flex align-center justify-center" style="height: 100px;">
        <VImg
          v-if="gift.reward.icon"
          contain
          height="100"
          :src="gift.reward.icon.preview_image_url"
        />
        <VIcon
          v-else
          color="gray"
          icon="tabler-photo"
          size="80"
        />
      </div>

      <div class="mb-2">
        <span class="text-subtitle-1 font-weight-bold">
          {{ gift.reward.name }}
        </span>
      </div>

      <VBtn
        block
        class="font-weight-bold"
        color="primary"
        :disabled=" processing.loading"
        :loading="processing.loading && processing.id==gift.id"
        variant="elevated"
        @click="applyGift"
      >
        <VIcon icon="tabler-shopping-cart-plus" start />
        {{ t('pos::pos_viewer.apply_to_cart') }}
      </VBtn>
    </VCard>
  </VCol>
</template>

<style lang="scss" scoped>
.gift-item {
  height: 100%;
  min-height: 250px;
  background-color: rgba(var(--v-theme-surface), 1);
  transition: all 0.25s ease;
  border: 2px dashed #ededed;
  padding: 0.5rem;

  &:hover {
    transform: translateY(-5px);
  }
}
</style>
