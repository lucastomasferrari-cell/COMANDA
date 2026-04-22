<script lang="ts" setup>
  import { useI18n } from 'vue-i18n'
  import Application from '@/modules/setting/pages/admin/setting/Application.vue'
  import Currency from '@/modules/setting/pages/admin/setting/Currency.vue'
  import General from '@/modules/setting/pages/admin/setting/General.vue'
  import Logo from '@/modules/setting/pages/admin/setting/Logo.vue'

  const { t } = useI18n()

  // 4.1: 4 sub-pantallas de settings consolidadas en un solo scroll.
  // El user pidio "secciones claras, no tabs anidadas". Si el scroll se
  // vuelve molesto, evaluamos expansion panels colapsables.
</script>

<template>
  <div class="d-flex flex-column ga-8">
    <section>
      <h3 class="text-h6 font-weight-medium mb-4">
        {{ t('setting::settings.sections.general') }}
      </h3>
      <General />
    </section>

    <section>
      <h3 class="text-h6 font-weight-medium mb-4">
        {{ t('setting::settings.sections.application') }}
      </h3>
      <Application />
    </section>

    <section>
      <h3 class="text-h6 font-weight-medium mb-4">
        {{ t('setting::settings.sections.logo') }}
      </h3>
      <Logo />
    </section>

    <section>
      <h3 class="text-h6 font-weight-medium mb-4">
        {{ t('setting::settings.sections.currency') }}
      </h3>
      <Currency />
    </section>
  </div>
</template>
