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
  parentId: number | null
  action: 'update' | 'create'
}>()
const emit = defineEmits(['on-success'])

const { t } = useI18n()
const { update, store, destroy } = useCategory()
const deleteLoading = ref(false)
const toast = useToast()
const { can } = useAuth()

const form = useForm({
  name: props.item?.name || {},
  slug: props.item?.slug,
  menu_id: props.menuId,
  parent_id: props.parentId || props.item?.parent_id,
  files: {
    logo: props.item?.logo?.id || null,
  },
  is_active: props.item?.is_active || false,
})

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
                <span v-if="parentId">
                  {{ t('category::categories.form.create_sub_category') }}
                </span>
                <span v-else>
                  {{ t('category::categories.form.create_root_category') }}
                </span>
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
                    :label="t('category::attributes.categories.name') + ` ( ${currentLanguage.name} )`" />
                </VCol>
                <VCol cols="12">
                  <VTextField v-model="form.state.slug" :error="!!form.errors.value?.slug"
                    :error-messages="form.errors.value?.slug" :label="t('category::attributes.categories.slug')" />
                </VCol>
                <VCol cols="12">
                  <SinglePicker v-model="form.state.files.logo" :label="t('category::attributes.categories.files.logo')"
                    :media="item?.logo" mime="image" />
                </VCol>
                <VCol cols="12">
                  <VCheckbox v-model="form.state.is_active" :label="t('category::attributes.categories.is_active')" />
                </VCol>
              </VRow>
            </VCardText>
          </VCard>
        </VCol>
      </VRow>
    </template>
  </BaseForm>
</template>
