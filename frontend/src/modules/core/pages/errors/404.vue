<script lang="ts" setup>
  import { useI18n } from 'vue-i18n'
  import misc404 from '@/assets/images/svg/404.svg?url'

  const { t } = useI18n()
</script>

<template>
  <div class="misc-wrapper">
    <div class="misc-avatar d-flex justify-center">
      <VImg
        :max-width="500"
        :src="misc404"
      />
    </div>
    <div class="text-center" style="margin-top: -80px">
      <h4 class="text-h4 font-weight-bold mb-2">
        {{ t('core::errors.404.title') }}
      </h4>
      <p class="text-body-1 mb-6">
        {{ t('core::errors.404.description') }}
      </p>
    </div>

    <div class="d-flex justify-center align-center">
      <VBtn
        class="mb-6"
        to="/"
      >
        {{ t('core::errors.404.button') }}
      </VBtn>
    </div>
  </div>
</template>

<style lang="scss">
@use "@/assets/scss/template/pages/misc.scss";

.header-title {
  font-size: clamp(3rem, 5vw, 6rem);
  line-height: clamp(3rem, 5vw, 6rem);
}
</style>
