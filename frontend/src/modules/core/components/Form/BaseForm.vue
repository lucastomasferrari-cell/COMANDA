<script lang="ts" setup>
  import type { RouteLocationRaw } from 'vue-router'
  import type { SupportedLanguages } from '@/modules/core/contracts/AppState.ts'
  import { useI18n } from 'vue-i18n'
  import FormLanguageSwitcher from '@/modules/core/components/Form/FormLanguageSwitcher.vue'
  import { useAppStore } from '@/modules/core/stores/appStore.ts'

  const props = withDefaults(
    defineProps<{
      loading: boolean
      disabled?: boolean
      hasMultipleLanguage?: boolean
      action: 'update' | 'create'
      resource: string
      hiddenCancelButton?: boolean
      hiddenActionButton?: boolean
      onClickCancel?: () => void
    }>(),
    { disabled: false })

  defineEmits<{
    (e: 'submit', value: Event): void
  }>()

  const { t } = useI18n()
  const { defaultLanguage } = useAppStore()
  const router = useRouter()

  const currentLanguage = ref<SupportedLanguages>(defaultLanguage)

  function goToBack () {
    if (props.onClickCancel) {
      props.onClickCancel()
    } else {
      router.push({ name: `admin.${props.resource.replace('-', '_')}.index` } as unknown as RouteLocationRaw)
    }
  }

</script>

<template>
  <VForm @submit.prevent="$emit('submit', $event)">
    <VRow>
      <VCol class="d-flex align-center justify-start gap-2" cols="12" md="4">
        <FormLanguageSwitcher v-if="hasMultipleLanguage" v-model="currentLanguage" />
      </VCol>
      <VCol class="d-flex justify-end gap-2" cols="12" md="8">
        <slot name="header-buttons" />
        <VBtn
          v-if="!hiddenCancelButton"
          color="default"
          :disabled="loading"
          type="button"
          @click="goToBack"
        >
          <VIcon icon="tabler-x" start />
          {{ t('admin::admin.buttons.cancel') }}
        </VBtn>
        <VBtn
          v-if="!hiddenActionButton"
          color="primary"
          :disabled="disabled||loading"
          :loading="loading"
          type="submit"
        >
          <VIcon :icon="action === 'create' ? 'tabler-plus' : 'tabler-pencil'" start />
          {{ t(`admin::admin.buttons.${action}`) }}
        </VBtn>
      </VCol>
    </VRow>
    <slot :current-language="currentLanguage" name="default" />
  </VForm>
</template>
