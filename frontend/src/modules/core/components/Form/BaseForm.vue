<script lang="ts" setup>
  import type { RouteLocationRaw } from 'vue-router'
  import type { SupportedLanguages } from '@/modules/core/contracts/AppState.ts'
  import { computed } from 'vue'
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
  const appStore = useAppStore()
  const { defaultLanguage } = appStore
  const router = useRouter()

  const currentLanguage = ref<SupportedLanguages>(defaultLanguage)

  // El switcher solo aparece si el backend expone >1 locale. COMANDA es
  // es_AR, así que en prod no se renderiza; dejamos el prop `hasMultipleLanguage`
  // para compat hacia adelante (si algún día volvemos multi-idioma).
  const showLanguageSwitcher = computed(
    () => props.hasMultipleLanguage && (appStore.supportedLanguages?.length ?? 0) > 1,
  )

  function goToBack () {
    if (props.onClickCancel) {
      props.onClickCancel()
    } else {
      router.push({ name: `admin.${props.resource.replace('-', '_')}.index` } as unknown as RouteLocationRaw)
    }
  }

</script>

<template>
  <!--
    Layout post-refactor Fix 3 parte B:
    - Header solo con language switcher (si aplica). Sin botones.
    - Contenido del form vía slot default.
    - Footer sticky al final de la vista con botones primarios
      (Cancelar / Crear|Guardar cambios). Se mantiene accesible aunque
      el form sea largo, sin forzar al user a scrollear hasta abajo.
    - `slot #header-buttons` legacy: si algún form pasa botones
      adicionales (ej Delete en Category), se renderizan en el footer
      junto a los principales. Semánticamente ya no son "header"
      pero el nombre se conserva por backward-compat con los forms
      existentes.
  -->
  <VForm class="base-form" @submit.prevent="$emit('submit', $event)">
    <div v-if="showLanguageSwitcher" class="base-form-header mb-3">
      <FormLanguageSwitcher v-model="currentLanguage" />
    </div>

    <div class="base-form-content">
      <slot :current-language="currentLanguage" name="default" />
    </div>

    <div class="base-form-footer">
      <div class="base-form-footer-inner d-flex align-center justify-end flex-wrap gap-2">
        <slot name="header-buttons" />
        <VBtn
          v-if="!hiddenCancelButton"
          color="default"
          :disabled="loading"
          type="button"
          variant="tonal"
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
          size="large"
          type="submit"
        >
          <VIcon :icon="action === 'create' ? 'tabler-plus' : 'tabler-device-floppy'" start />
          {{ t(`admin::admin.buttons.${action === 'create' ? 'create' : 'save_changes'}`) }}
        </VBtn>
      </div>
    </div>
  </VForm>
</template>

<style lang="scss" scoped>
.base-form {
  // Permitir que el contenido haga scroll interno y el footer quede
  // fijo al borde inferior del layout-page-content. padding-bottom
  // compensa el alto del footer sticky (evita que el último campo
  // quede tapado por él).
  padding-bottom: 5rem;
}

.base-form-footer {
  position: sticky;
  inset-block-end: 0;
  z-index: 4;
  background: rgb(var(--v-theme-surface));
  border-block-start: thin solid rgba(var(--v-theme-on-surface), 0.1);
  margin-block-start: 1rem;
  padding-block: 0.75rem;
  padding-inline: 1rem;
  margin-inline: -1rem;
  backdrop-filter: saturate(180%) blur(6px);
}
</style>
