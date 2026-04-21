<script lang="ts" setup>

  import { useI18n } from 'vue-i18n'

  const props = defineProps<{
    form: Record<string, any>
    meta: Record<string, any>
  }>()
  const { t } = useI18n()

  const { state, errors } = toRefs(props.form)
</script>

<template>
  <VRow>
    <VCol cols="12" md="6">
      <VTextField
        v-model="state.options.host"
        :error="!!errors?.[`options.host`]"
        :error-messages="errors?.[`options.host`]"
        :label="t('printer::attributes.printers.options.host')"
      />
    </VCol>
    <VCol cols="12" md="6">
      <VTextField
        v-model="state.options.port"
        v-integer-en
        :error="!!errors?.[`options.port`]"
        :error-messages="errors?.[`options.port`]"
        :label="t('printer::attributes.printers.options.port')"
      />
    </VCol>
    <VCol cols="12" md="6">
      <VSelect
        v-model="state.options.paper_size"
        :error="!!errors?.options.paper_size"
        :error-messages="errors?.options.paper_size"
        item-title="name"
        item-value="id"
        :items="meta.paperSizes"
        :label="t('printer::attributes.printers.options.paper_size')"
      />
    </VCol>
    <VCol cols="12" md="6">
      <VTextField
        v-model="state.options.timeout_ms"
        v-integer-en
        clearable
        :error="!!errors?.[`options.timeout_ms`]"
        :error-messages="errors?.[`options.timeout_ms`]"
        :label="t('printer::attributes.printers.options.timeout_ms')"
      />
    </VCol>
    <VCol cols="12" md="6">
      <VTextField
        v-model="state.options.copies"
        v-integer-en
        clearable
        :error="!!errors?.[`options.copies`]"
        :error-messages="errors?.[`options.copies`]"
        :label="t('printer::attributes.printers.options.copies')"
      />
    </VCol>
    <VCol cols="12" md="6">
      <VTextField
        v-model="state.options.retries"
        v-integer-en
        clearable
        :error="!!errors?.[`options.retries`]"
        :error-messages="errors?.[`options.retries`]"
        :label="t('printer::attributes.printers.options.retries')"
      />
    </VCol>
    <VRow>
      <VCol cols="12" md="4">
        <VCheckbox
          v-model="state.options.cut_paper"
          :label="t('printer::attributes.printers.options.cut_paper')"
        />
      </VCol>
      <VCol cols="12" md="4">
        <VCheckbox
          v-model="state.options.beep"
          :label="t('printer::attributes.printers.options.beep')"
        />
      </VCol>
      <VCol cols="12" md="4">
        <VCheckbox
          v-model="state.options.open_cash_drawer"
          :label="t('printer::attributes.printers.options.open_cash_drawer')"
        />
      </VCol>
    </VRow>
  </VRow>
</template>
