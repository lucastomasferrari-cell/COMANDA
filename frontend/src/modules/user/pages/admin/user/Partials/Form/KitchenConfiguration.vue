<script lang="ts" setup>
  import { useI18n } from 'vue-i18n'

  defineProps<{
    meta: Record<string, any>
    form: Record<string, any>
  }>()

  const { t } = useI18n()
</script>

<template>
  <VCol cols="12" md="8">
    <VCard>
      <VCardTitle class="d-flex justify-space-between align-center mb-2">
        <div class="d-flex align-center">
          <VIcon class="me-2" icon="tabler-device-ipad-horizontal-cog" size="20" />
          <span>{{ t('user::users.form.cards.kitchen_configuration') }}</span>
        </div>
      </VCardTitle>
      <VCardText>
        <VRow>
          <VCol cols="12" md="6">
            <VSelect
              v-model="form.state.printer_id"
              :error="!!form.errors.value?.printer_id"
              :error-messages="form.errors.value?.printer_id"
              item-title="name"
              item-value="id"
              :items="meta.printers"
              :label="t('user::attributes.users.printer_id')"
            />
          </VCol>
          <VCol cols="12" md="6">
            <VSelect
              v-model="form.state.category_slugs"
              chips
              clearable
              :error="!!form.errors.value?.category_slugs"
              :error-messages="form.errors.value?.category_slugs"
              item-title="name"
              item-value="slug"
              :items="meta.categories"
              :label="t('user::attributes.users.category_slugs')"
              multiple
            />
          </VCol>

        </VRow>
      </VCardText>
    </VCard>
  </VCol>
</template>

<style lang="scss" scoped>

</style>
