<script lang="ts" setup>
  import { useI18n } from 'vue-i18n'
  import SinglePicker from '@/modules/media/components/SinglePicker.vue'

  defineProps<{
    form: any
    item?: Record<string, any> | null
  }>()
  const { t } = useI18n()

</script>

<template>
  <VCard>
    <VCardTitle class="d-flex justify-space-between align-center mb-2">
      <div class="d-flex align-center">
        <VIcon class="me-2" icon="tabler-library-photo" size="20" />
        <span>{{ t('product::products.form.cards.media') }}</span>
      </div>
    </VCardTitle>
    <VCardText>
      <VRow>
        <VCol cols="12">
          <SinglePicker v-model="form.state.files.thumbnail" :media="item?.thumbnail" mime="image" />
        </VCol>
      </VRow>
    </VCardText>
  </VCard>
</template>

<style lang="scss" scoped></style>
