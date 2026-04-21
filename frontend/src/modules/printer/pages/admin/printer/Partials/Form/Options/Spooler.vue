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
        v-model="state.options.spooler_name"
        :error="!!errors?.[`options.spooler_name`]"
        :error-messages="errors?.[`options.spooler_name`]"
        :label="t('printer::attributes.printers.options.spooler_name')"
      />
    </VCol>
    <VCol cols="12" md="6">
      <VTextField
        v-model="state.options.host"
        clearable
        :error="!!errors?.[`options.host`]"
        :error-messages="errors?.[`options.host`]"
        :label="t('printer::attributes.printers.options.host')"
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
      <VSelect
        v-model="state.options.orientation"
        clearable
        :error="!!errors?.options.orientation"
        :error-messages="errors?.options.orientation"
        item-title="name"
        item-value="id"
        :items="meta.spoolerOrientations"
        :label="t('printer::attributes.printers.options.orientation')"
      />
    </VCol>
    <VCol cols="12" md="6">
      <VSelect
        v-model="state.options.sides"
        clearable
        :error="!!errors?.options.sides"
        :error-messages="errors?.options.sides"
        item-title="name"
        item-value="id"
        :items="meta.spoolerSides"
        :label="t('printer::attributes.printers.options.sides')"
      />
    </VCol>
    <VCol cols="12" md="6">
      <VSelect
        v-model="state.options.color_mode"
        clearable
        :error="!!errors?.options.color_mode"
        :error-messages="errors?.options.color_mode"
        item-title="name"
        item-value="id"
        :items="meta.spoolerColorModes"
        :label="t('printer::attributes.printers.options.color_mode')"
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
    <VCol cols="12">
      <VRow>
        <VCol cols="12" md="3">
          <VTextField
            v-model="state.options.margins.top"
            v-integer-en
            clearable
            :error="!!errors?.[`options.margins.top`]"
            :error-messages="errors?.[`options.margins.top`]"
            :label="t('printer::attributes.printers.options.margins.top')"
          />
        </VCol>
        <VCol cols="12" md="3">
          <VTextField
            v-model="state.options.margins.right"
            v-integer-en
            clearable
            :error="!!errors?.[`options.margins.right`]"
            :error-messages="errors?.[`options.margins.right`]"
            :label="t('printer::attributes.printers.options.margins.right')"
          />
        </VCol>
        <VCol cols="12" md="3">
          <VTextField
            v-model="state.options.margins.bottom"
            v-integer-en
            clearable
            :error="!!errors?.[`options.margins.bottom`]"
            :error-messages="errors?.[`options.margins.bottom`]"
            :label="t('printer::attributes.printers.options.margins.bottom')"
          />
        </VCol>
        <VCol cols="12" md="3">
          <VTextField
            v-model="state.options.margins.left"
            v-integer-en
            clearable
            :error="!!errors?.[`options.margins.left`]"
            :error-messages="errors?.[`options.margins.left`]"
            :label="t('printer::attributes.printers.options.margins.left')"
          />
        </VCol>
      </VRow>
    </VCol>
    <VRow>
      <VCol cols="12" md="4">
        <VCheckbox
          v-model="state.options.raw"
          :label="t('printer::attributes.printers.options.raw')"
        />
      </VCol>
      <VCol cols="12" md="4">
        <VCheckbox
          v-model="state.options.fallback_text_mode"
          :label="t('printer::attributes.printers.options.fallback_text_mode')"
        />
      </VCol>
    </VRow>
  </VRow>
</template>
