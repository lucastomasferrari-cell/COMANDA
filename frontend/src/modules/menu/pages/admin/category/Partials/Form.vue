<script lang="ts" setup>
import type { AxiosError } from 'axios'
import { computed, ref } from 'vue'
import { useI18n } from 'vue-i18n'
import { useToast } from 'vue-toastification'
import { useAuth } from '@/modules/auth/composables/auth.ts'
import { useConfirmDialog } from '@/modules/core/composables/confirmDialog.ts'
import { useForm } from '@/modules/core/composables/form.ts'
import SinglePicker from '@/modules/media/components/SinglePicker.vue'
import { useCategory } from '@/modules/menu/composables/category.ts'

const props = defineProps<{
  item?: Record<string, any> | null
  menuId: number
  action: 'update' | 'create'
}>()
const emit = defineEmits(['on-success'])

const { t } = useI18n()
const { update, store, destroy } = useCategory()
const deleteLoading = ref(false)
const toast = useToast()
const { can } = useAuth()

// Slug ya no lo pide la UI — el backend auto-genera desde name en el
// creating hook de Category si viene null. Queda en la tabla por compat
// con Discount/Voucher/Loyalty que lo referencian por valor.
const form = useForm({
  name: props.item?.name || {},
  sku: props.item?.sku,
  sku_locked: props.item?.sku_locked || false,
  menu_id: props.menuId,
  // parent_id siempre null post-Fix 3 parte C. Las categorías existentes
  // con parent_id se conservan en DB (y en Discount/Voucher condiciones)
  // pero la UI solo crea root. En edit, mantenemos el parent_id original
  // para no nullear accidentalmente jerarquías pre-existentes.
  parent_id: props.item?.parent_id ?? null,
  files: {
    logo: props.item?.logo?.id || null,
  },
  is_active: props.item?.is_active ?? true,
  color: props.item?.color || null,
  // Sprint 1.B: hue 0-360 para los placeholders de producto del POS
  // viewer + chip activo. Default null → frontend usa coral marca (12).
  color_hue: props.item?.color_hue ?? null,
})

// Preview del color_hue con saturación/luminosidad fijas (mismas que usa
// el frontend del POS para consistencia visual: 55%/50%).
const hueValue = computed(() => form.state.color_hue ?? 12)
const huePreviewColor = computed(() => `hsl(${hueValue.value} 55% 50%)`)
const huePlaceholderBg = computed(() => `hsl(${hueValue.value} 40% 85%)`)
const huePlaceholderFg = computed(() => `hsl(${hueValue.value} 50% 25%)`)

// Paleta Toast. Coincide con HasCategoryColor trait del backend —
// el auto-asigna uno de estos si el user no elige nada.
const PALETTE_COLORS = [
  '#1D9E75', // teal
  '#EF9F27', // amber
  '#378ADD', // blue
  '#D4537E', // pink
  '#7F77DD', // purple
  '#639922', // green
  '#5F5E5A', // gray
  '#D85A30', // coral
]

const hiddenActionButton = computed(() => {
  if (props.action == 'create' && !can('admin.categories.create')) {
    return true
  } else if (props.action == 'update' && !can('admin.categories.edit')) {
    return true
  }
  return false
})

async function submit() {
  if (
    !hiddenActionButton.value
    && !form.loading.value
    && await form.submit(() => props.action == 'create' ? store(form.state) : update(props.item?.id, form.state))
  ) {
    emit('on-success')
  }
}

async function deleteCategory() {
  const confirmed = await useConfirmDialog({
    message: t('admin::admin.delete.confirmation_message'),
    confirmButtonText: t('admin::admin.delete.confirm_button_text'),
  })
  if (!confirmed) {
    return
  }
  try {
    deleteLoading.value = true
    const response = await destroy(props.item?.id)
    toast.success(response.data.message)
    emit('on-success')
  } catch (error) {
    toast.error((error as AxiosError<{
      message?: string
    }>).response?.data?.message || t('core::errors.an_unexpected_error_occurred'))
  } finally {
    deleteLoading.value = false
  }
}
</script>

<template>

  <BaseForm :action="action" has-multiple-language :hidden-action-button="hiddenActionButton" hidden-cancel-button
    :loading="form.loading.value" resource="categories" @submit="submit">
    <template #header-buttons>
      <VBtn v-if="can('admin.categories.destroy') && item" color="error" :disabled="deleteLoading || form.loading.value"
        :loading="deleteLoading" type="button" @click="deleteCategory">
        <VIcon icon="tabler-trash" start />
        {{ t('admin::admin.buttons.delete') }}
      </VBtn>
    </template>
    <template #default="{ currentLanguage }">
      <VRow>
        <VCol cols="12">
          <VCard>
            <VCardTitle class="mb-2">
              <span v-if="action == 'create'">
                {{ t('category::categories.form.create_category') }}
              </span>
              <span v-else-if="action == 'update'">
                {{ t('admin::resource.edit', { resource: t('category::categories.category') }) }}
              </span>
            </VCardTitle>
            <VCardText>
              <VRow>
                <VCol cols="12">
                  <VTextField v-model="form.state.name[currentLanguage.id]"
                    :error="!!form.errors.value?.[`name.${currentLanguage.id}`]"
                    :error-messages="form.errors.value?.[`name.${currentLanguage.id}`]"
                    :label="t('category::attributes.categories.name')" />
                </VCol>
                <VCol cols="12">
                  <VTextField v-model="form.state.sku"
                    clearable
                    :error="!!form.errors.value?.sku"
                    :error-messages="form.errors.value?.sku"
                    :hint="t('category::attributes.categories.sku_hint')"
                    :label="t('category::attributes.categories.sku')"
                    persistent-hint
                    :readonly="form.state.sku_locked" />
                </VCol>
                <VCol cols="12">
                  <SinglePicker v-model="form.state.files.logo" :label="t('category::attributes.categories.files.logo')"
                    :media="item?.logo" mime="image" />
                </VCol>
                <VCol cols="12">
                  <label class="text-caption text-medium-emphasis d-block mb-2">
                    {{ t('category::attributes.categories.color') }}
                  </label>
                  <div class="d-flex flex-wrap gap-2 mb-2">
                    <button
                      v-for="c in PALETTE_COLORS"
                      :key="c"
                      type="button"
                      class="color-swatch"
                      :class="{ selected: form.state.color === c }"
                      :style="{ background: c }"
                      :aria-label="c"
                      @click="form.state.color = c"
                    />
                    <input
                      v-model="form.state.color"
                      type="color"
                      class="color-custom"
                      :aria-label="t('category::attributes.categories.color_custom')"
                    >
                  </div>
                  <div v-if="form.errors.value?.color" class="text-caption text-error">
                    {{ form.errors.value.color }}
                  </div>
                </VCol>
                <!-- Sprint 1.B: hue slider + preview. El slider es intencionalmente
                     más simple que un color picker full — el dueño elige un hue
                     (tinte) y el sistema fija sat/lum para que todos los placeholders
                     del POS se vean coherentes. No hay full-RGB que se vaya de paleta. -->
                <VCol cols="12">
                  <label class="text-caption text-medium-emphasis d-block mb-2">
                    {{ t('category::attributes.categories.color_hue') }}
                  </label>
                  <div class="d-flex align-center ga-3 mb-2">
                    <div class="hue-preview" :style="{ backgroundColor: huePreviewColor }" />
                    <VSlider
                      v-model="form.state.color_hue"
                      class="flex-grow-1"
                      :color="huePreviewColor"
                      hide-details
                      :max="360"
                      :min="0"
                      :step="5"
                      thumb-label
                      track-color="hue-track"
                    />
                    <VBtn
                      v-if="form.state.color_hue !== null"
                      icon="tabler-rotate-clockwise"
                      size="small"
                      variant="text"
                      :aria-label="t('admin::admin.buttons.reset')"
                      @click="form.state.color_hue = null"
                    />
                  </div>
                  <div class="hue-tile-preview" :style="{ borderTopColor: huePreviewColor }">
                    <div class="hue-tile-placeholder" :style="{ background: huePlaceholderBg, color: huePlaceholderFg }">
                      AA
                    </div>
                    <div class="hue-tile-label">{{ t('category::attributes.categories.color_hue_preview') }}</div>
                  </div>
                  <div v-if="form.errors.value?.color_hue" class="text-caption text-error mt-1">
                    {{ form.errors.value.color_hue }}
                  </div>
                </VCol>
                <VCol cols="12">
                  <VCheckbox v-if="action !== 'create'" v-model="form.state.is_active" :label="t('category::attributes.categories.is_active')" />
                </VCol>
              </VRow>
            </VCardText>
          </VCard>
        </VCol>
      </VRow>
    </template>
  </BaseForm>
</template>

<style scoped>
.color-swatch {
  width: 36px;
  height: 36px;
  border-radius: 50%;
  border: 2px solid transparent;
  cursor: pointer;
  transition: transform 120ms ease, border-color 120ms ease;
}
.color-swatch:hover {
  transform: scale(1.08);
}
.color-swatch.selected {
  border-color: rgb(var(--v-theme-on-surface));
  box-shadow: 0 0 0 2px rgb(var(--v-theme-surface));
}
.color-custom {
  width: 36px;
  height: 36px;
  padding: 0;
  border: 2px dashed rgba(var(--v-theme-on-surface), 0.3);
  border-radius: 50%;
  cursor: pointer;
  background: transparent;
}

/* Hue picker Sprint 1.B — preview del hue + tile de producto dummy
   para que el admin vea el impacto real antes de guardar. */
.hue-preview {
  width: 36px;
  height: 36px;
  border-radius: 8px;
  border: 1px solid rgba(var(--v-theme-on-surface), 0.12);
  flex-shrink: 0;
}
.hue-tile-preview {
  max-width: 200px;
  border: 1px solid rgba(var(--v-theme-on-surface), 0.12);
  border-top: 4px solid rgb(var(--v-theme-primary));
  border-radius: 8px;
  overflow: hidden;
  margin-top: 8px;
}
.hue-tile-placeholder {
  aspect-ratio: 1.4;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 2rem;
  font-weight: 700;
  letter-spacing: 0.05em;
  user-select: none;
}
.hue-tile-label {
  padding: 8px 12px;
  font-size: 0.875rem;
  color: rgba(var(--v-theme-on-surface), 0.7);
}
</style>
