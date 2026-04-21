<script lang="ts" setup>
  import QRCodeVue3 from 'qrcode-vue3'
  import { useI18n } from 'vue-i18n'
  import { useAppStore } from '@/modules/core/stores/appStore.ts'

  defineProps<{ item: any }>()
  const { t } = useI18n()
  const appStore = useAppStore()

</script>

<template>
  <VMenu
    location="top"
    offset="8"
  >
    <template #activator="{ props }">
      <VChip
        class="cursor-pointer"
        color="default"
        size="small"
        v-bind="props"
      >
        <VIcon
          class="cursor-pointer text-medium-emphasis"
          icon="tabler-qrcode"
        />
        &nbsp;
        {{ t('seatingplan::tables.view_qrcode') }}
      </VChip>
    </template>

    <VCard>
      <VCardText class="text-center pa-2">
        <QRCodeVue3
          :background-options="{
            color: '#ffffff',
            round: 0
          }"
          :button-name="t('admin::admin.buttons.download')"
          :corners-dot-options="{
            type: '',
            color: '#4d4d4d'
          }"
          :corners-square-options="{
            type: 'extra-rounded',
            color: '#4d4d4d'
          }"
          :dots-options="{
            type: 'extra-rounded',
            color: '#f57c00',
            roundSize: true,
            gradient: null
          }"
          :download="true"
          download-button="v-btn v-theme--light bg-primary v-btn--density-default elevation-0 v-btn--size-small v-btn--variant-flat"
          :download-options="{ name: item.name, extension: 'png' }"
          file-ext="png"
          :height="250"
          :image="appStore.logoDataBase64||''"
          :image-options="{
            hideBackgroundDots: true,
            imageSize: 0.4,
            margin: 0
          }"
          :qr-options="{ typeNumber: 0, mode: 'Byte', errorCorrectionLevel: 'H' }"
          :value="item.qrcode"
          :width="250"
        />
      </VCardText>
    </VCard>
  </VMenu>
</template>
