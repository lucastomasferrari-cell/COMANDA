<script lang="ts" setup>
  import { useI18n } from 'vue-i18n'
  import ComingSoonPlaceholder from '@/modules/core/components/ComingSoonPlaceholder.vue'
  import PrinterIndex from '@/modules/printer/pages/admin/printer/Index.vue'

  const { t } = useI18n()
</script>

<template>
  <div class="d-flex flex-column ga-8">
    <section>
      <h3 class="text-h6 font-weight-medium mb-4">
        {{ t('admin::sidebar.printers') }}
      </h3>
      <PrinterIndex />
    </section>

    <section>
      <h3 class="text-h6 font-weight-medium mb-4">
        {{ t('admin::sidebar.print_rules') }}
      </h3>
      <ComingSoonPlaceholder />
    </section>
  </div>
</template>
