<script lang="ts" setup>
  import { useI18n } from 'vue-i18n'
  import RoleIndex from '@/modules/user/pages/admin/role/Index.vue'
  import UserIndex from '@/modules/user/pages/admin/user/Index.vue'

  const { t } = useI18n()
</script>

<template>
  <div class="d-flex flex-column ga-8">
    <section>
      <h3 class="text-h6 font-weight-medium mb-4">
        {{ t('admin::sidebar.users') }}
      </h3>
      <UserIndex />
    </section>

    <section>
      <h3 class="text-h6 font-weight-medium mb-4">
        {{ t('admin::sidebar.roles') }}
      </h3>
      <RoleIndex />
    </section>
  </div>
</template>
