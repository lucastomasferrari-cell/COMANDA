<script lang="ts" setup>
  import { useI18n } from 'vue-i18n'
  import TableIndex from '@/modules/seatingPlan/pages/admin/table/Index.vue'
  import ZoneIndex from '@/modules/seatingPlan/pages/admin/zone/Index.vue'
  import FloorIndex from '@/modules/seatingPlan/pages/admin/floor/Index.vue'

  const { t } = useI18n()
</script>

<template>
  <div class="d-flex flex-column ga-8">
    <section>
      <h3 class="text-h6 font-weight-medium mb-4">
        {{ t('admin::sidebar.tables') }}
      </h3>
      <TableIndex />
    </section>

    <section>
      <h3 class="text-h6 font-weight-medium mb-4">
        {{ t('admin::sidebar.zones') }}
      </h3>
      <ZoneIndex />
    </section>

    <section>
      <h3 class="text-h6 font-weight-medium mb-4">
        {{ t('admin::sidebar.floors') }}
      </h3>
      <FloorIndex />
    </section>
  </div>
</template>
