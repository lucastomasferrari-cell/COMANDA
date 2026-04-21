<script lang="ts" setup>
import {useI18n} from 'vue-i18n'
import misc404 from '@/assets/images/svg/404.svg?url'

defineProps<{ loading: boolean, notFound?: boolean }>()

const {t} = useI18n()
</script>

<template>
  <div class="page-state-wrapper">
    <div v-if="loading" class="d-flex justify-center align-center py-16">
      <VProgressCircular color="primary" indeterminate size="50"/>
    </div>

    <div v-else-if="notFound" class="misc-wrapper">
      <div class="misc-avatar d-flex justify-center">
        <VImg
          :max-width="450"
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
    </div>

    <div v-else>
      <slot/>
    </div>
  </div>
</template>
