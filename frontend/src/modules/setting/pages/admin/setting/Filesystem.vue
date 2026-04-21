<script lang="ts" setup>

  import type {
    FilesystemSettings,
    FilesystemSettingsMeta,
    FilesystemSettingsResponse,
  } from '@/modules/setting/contracts/Settings.ts'
  import { useI18n } from 'vue-i18n'
  import PageLoader from '@/modules/core/components/PageLoader.vue'
  import { useForm } from '@/modules/core/composables/form.ts'
  import { useSetting } from '@/modules/setting/composables/setting.ts'

  onMounted(async () => {
    const response: FilesystemSettingsResponse | false = await getSettings('filesystem')
    if (response !== false) {
      meta.value = response.meta
      Object.assign(form.state, response.settings)
    }
  })

  const { getSettings, store, update } = useSetting()
  const { t } = useI18n()

  const meta = ref<FilesystemSettingsMeta>({
    disks: [],
    private_disks: [],
  })

  const form = useForm<FilesystemSettings>({
    default_filesystem_disk: '',
    default_private_filesystem_disk: '',
    filesystem_s3_public_use_path_style_endpoint: false,
    filesystem_s3_public_url: null,
    filesystem_s3_public_endpoint: null,
    filesystem_s3_public_region: null,
    filesystem_s3_public_bucket: null,
    filesystem_s3_private_use_path_style_endpoint: false,
    filesystem_s3_private_url: null,
    filesystem_s3_private_endpoint: null,
    filesystem_s3_private_region: null,
    filesystem_s3_private_bucket: null,
    encryptable: {
      filesystem_s3_public_key: null,
      filesystem_s3_public_secret: null,
      filesystem_s3_private_key: null,
      filesystem_s3_private_secret: null,
    },
  })

  const usesPublicS3 = computed(() => form.state.default_filesystem_disk === 's3')
  const usesPrivateS3 = computed(() => form.state.default_private_filesystem_disk === 's3')

  function getDiskName(items: Record<string, any>[], value: string): string {
    return items.find(item => item.id === value)?.name || value
  }

  function submit () {
    form.submit(() => update('filesystem', form.state))
  }
</script>

<template>
  <PageLoader :loading="store.loading" />
  <VForm v-if="!store.loading" @submit.prevent="submit">
    <VAlert class="mb-6" color="info" variant="tonal">
      Choose where public and private files are stored. S3 credentials are configured separately for each side.
    </VAlert>

    <VRow class="mb-2">
      <VCol cols="12" md="6">
        <VCard variant="outlined">
          <VCardText class="pa-5">
            <div class="d-flex align-center justify-space-between gap-3 mb-4">
              <div>
                <div class="text-subtitle-1 font-weight-bold">
                  {{ t('setting::attributes.settings.default_filesystem_disk') }}
                </div>
                <div class="text-body-2 text-medium-emphasis">
                  Media and publicly accessible files
                </div>
              </div>
              <VChip color="primary" size="small" variant="tonal">
                {{ getDiskName(meta.disks, form.state.default_filesystem_disk) }}
              </VChip>
            </div>

            <VSelect
              v-model="form.state.default_filesystem_disk"
              :error="!!form.errors.value?.default_filesystem_disk"
              :error-messages="form.errors.value?.default_filesystem_disk"
              item-title="name"
              item-value="id"
              :items="meta.disks"
              :label="t('setting::attributes.settings.default_filesystem_disk')"
            />

            <template v-if="usesPublicS3">
              <div class="section-divider" />
              <div class="text-subtitle-2 font-weight-medium mb-4">
                Public S3 Configuration
              </div>

              <VRow>
                <VCol cols="12" md="6">
                  <VTextField
                    v-model="form.state.encryptable.filesystem_s3_public_key"
                    :error="!!form.errors.value?.['encryptable.filesystem_s3_public_key']"
                    :error-messages="form.errors.value?.['encryptable.filesystem_s3_public_key']"
                    :label="t('setting::attributes.settings.encryptable.filesystem_s3_public_key')"
                    type="password"
                  />
                </VCol>
                <VCol cols="12" md="6">
                  <VTextField
                    v-model="form.state.encryptable.filesystem_s3_public_secret"
                    :error="!!form.errors.value?.['encryptable.filesystem_s3_public_secret']"
                    :error-messages="form.errors.value?.['encryptable.filesystem_s3_public_secret']"
                    :label="t('setting::attributes.settings.encryptable.filesystem_s3_public_secret')"
                    type="password"
                  />
                </VCol>
                <VCol cols="12">
                  <VTextField
                    v-model="form.state.filesystem_s3_public_bucket"
                    :error="!!form.errors.value?.filesystem_s3_public_bucket"
                    :error-messages="form.errors.value?.filesystem_s3_public_bucket"
                    :label="t('setting::attributes.settings.filesystem_s3_public_bucket')"
                  />
                </VCol>
                <VCol cols="12" md="6">
                  <VTextField
                    v-model="form.state.filesystem_s3_public_region"
                    :error="!!form.errors.value?.filesystem_s3_public_region"
                    :error-messages="form.errors.value?.filesystem_s3_public_region"
                    :label="t('setting::attributes.settings.filesystem_s3_public_region')"
                  />
                </VCol>
                <VCol cols="12" md="6">
                  <VTextField
                    v-model="form.state.filesystem_s3_public_endpoint"
                    :error="!!form.errors.value?.filesystem_s3_public_endpoint"
                    :error-messages="form.errors.value?.filesystem_s3_public_endpoint"
                    :label="t('setting::attributes.settings.filesystem_s3_public_endpoint')"
                  />
                </VCol>
                <VCol cols="12">
                  <VTextField
                    v-model="form.state.filesystem_s3_public_url"
                    :error="!!form.errors.value?.filesystem_s3_public_url"
                    :error-messages="form.errors.value?.filesystem_s3_public_url"
                    :label="t('setting::attributes.settings.filesystem_s3_public_url')"
                  />
                </VCol>
                <VCol cols="12">
                  <VCheckbox
                    v-model="form.state.filesystem_s3_public_use_path_style_endpoint"
                    :label="t('setting::attributes.settings.filesystem_s3_public_use_path_style_endpoint')"
                  />
                </VCol>
              </VRow>
            </template>
          </VCardText>
        </VCard>
      </VCol>

      <VCol cols="12" md="6">
        <VCard variant="outlined">
          <VCardText class="pa-5">
            <div class="d-flex align-center justify-space-between gap-3 mb-4">
              <div>
                <div class="text-subtitle-1 font-weight-bold">
                  {{ t('setting::attributes.settings.default_private_filesystem_disk') }}
                </div>
                <div class="text-body-2 text-medium-emphasis">
                  Internal documents, generated assets, and private app files
                </div>
              </div>
              <VChip color="secondary" size="small" variant="tonal">
                {{ getDiskName(meta.private_disks, form.state.default_private_filesystem_disk) }}
              </VChip>
            </div>

            <VSelect
              v-model="form.state.default_private_filesystem_disk"
              :error="!!form.errors.value?.default_private_filesystem_disk"
              :error-messages="form.errors.value?.default_private_filesystem_disk"
              item-title="name"
              item-value="id"
              :items="meta.private_disks"
              :label="t('setting::attributes.settings.default_private_filesystem_disk')"
            />

            <template v-if="usesPrivateS3">
              <div class="section-divider" />
              <div class="text-subtitle-2 font-weight-medium mb-4">
                Private S3 Configuration
              </div>

              <VRow>
                <VCol cols="12" md="6">
                  <VTextField
                    v-model="form.state.encryptable.filesystem_s3_private_key"
                    :error="!!form.errors.value?.['encryptable.filesystem_s3_private_key']"
                    :error-messages="form.errors.value?.['encryptable.filesystem_s3_private_key']"
                    :label="t('setting::attributes.settings.encryptable.filesystem_s3_private_key')"
                    type="password"
                  />
                </VCol>
                <VCol cols="12" md="6">
                  <VTextField
                    v-model="form.state.encryptable.filesystem_s3_private_secret"
                    :error="!!form.errors.value?.['encryptable.filesystem_s3_private_secret']"
                    :error-messages="form.errors.value?.['encryptable.filesystem_s3_private_secret']"
                    :label="t('setting::attributes.settings.encryptable.filesystem_s3_private_secret')"
                    type="password"
                  />
                </VCol>
                <VCol cols="12">
                  <VTextField
                    v-model="form.state.filesystem_s3_private_bucket"
                    :error="!!form.errors.value?.filesystem_s3_private_bucket"
                    :error-messages="form.errors.value?.filesystem_s3_private_bucket"
                    :label="t('setting::attributes.settings.filesystem_s3_private_bucket')"
                  />
                </VCol>
                <VCol cols="12" md="6">
                  <VTextField
                    v-model="form.state.filesystem_s3_private_region"
                    :error="!!form.errors.value?.filesystem_s3_private_region"
                    :error-messages="form.errors.value?.filesystem_s3_private_region"
                    :label="t('setting::attributes.settings.filesystem_s3_private_region')"
                  />
                </VCol>
                <VCol cols="12" md="6">
                  <VTextField
                    v-model="form.state.filesystem_s3_private_endpoint"
                    :error="!!form.errors.value?.filesystem_s3_private_endpoint"
                    :error-messages="form.errors.value?.filesystem_s3_private_endpoint"
                    :label="t('setting::attributes.settings.filesystem_s3_private_endpoint')"
                  />
                </VCol>
                <VCol cols="12">
                  <VTextField
                    v-model="form.state.filesystem_s3_private_url"
                    :error="!!form.errors.value?.filesystem_s3_private_url"
                    :error-messages="form.errors.value?.filesystem_s3_private_url"
                    :label="t('setting::attributes.settings.filesystem_s3_private_url')"
                  />
                </VCol>
                <VCol cols="12">
                  <VCheckbox
                    v-model="form.state.filesystem_s3_private_use_path_style_endpoint"
                    :label="t('setting::attributes.settings.filesystem_s3_private_use_path_style_endpoint')"
                  />
                </VCol>
              </VRow>
            </template>
          </VCardText>
        </VCard>
      </VCol>
    </VRow>

    <div class="d-flex justify-end mt-4">
      <VBtn
        :disabled="form.loading.value"
        :loading="form.loading.value"
        type="submit"
      >
        <VIcon icon="tabler-database-cog" start />
        {{ t('admin::admin.buttons.save') }}
      </VBtn>
    </div>
  </VForm>
</template>

<style scoped>
.section-divider {
  border-block-end: 1px dashed rgba(var(--v-border-color), var(--v-border-opacity));
  margin-block: 20px;
}
</style>
