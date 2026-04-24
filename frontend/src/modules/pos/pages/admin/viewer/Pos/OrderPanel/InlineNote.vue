<script lang="ts" setup>
  import type { PosForm } from '@/modules/pos/contracts/posViewer.ts'
  import { computed, ref } from 'vue'
  import { useI18n } from 'vue-i18n'

  // Sprint 3.A.11 — nota de la comanda inline al final de items.
  // Reemplaza el textarea "Notas" de AdditionalInformation (eliminado
  // en 3.A.9). Estado collapsed por default con botón "+ Agregar nota";
  // expanded con textarea + preview inline de la nota escrita.

  const props = defineProps<{
    form: PosForm
  }>()

  const { t } = useI18n()

  const expanded = ref(false)
  const hasNote = computed(() => {
    const n = props.form.meta.notes
    return typeof n === 'string' && n.trim().length > 0
  })

  const openEditor = (): void => {
    expanded.value = true
  }

  const closeEditor = (): void => {
    expanded.value = false
  }

  const clearNote = (): void => {
    props.form.meta.notes = null
    expanded.value = false
  }
</script>

<template>
  <div class="inline-note px-3 py-2">
    <!-- Sin nota + sin editor abierto: botón text que invita a agregar. -->
    <VBtn
      v-if="!hasNote && !expanded"
      class="inline-note__add"
      prepend-icon="tabler-notes"
      size="small"
      variant="text"
      @click="openEditor"
    >
      {{ t('pos::pos_viewer.check_header.add_note') }}
    </VBtn>

    <!-- Con nota + editor cerrado: muestra la nota compacta con un botón
         edit al lado. Tap en cualquier parte abre el editor. -->
    <div
      v-else-if="hasNote && !expanded"
      class="inline-note__preview d-flex align-start ga-2"
      @click="openEditor"
    >
      <VIcon
        class="inline-note__icon flex-shrink-0 mt-1"
        color="primary"
        icon="tabler-notes"
        size="18"
      />
      <p class="inline-note__text flex-grow-1 text-body-2 mb-0">
        {{ form.meta.notes }}
      </p>
      <VBtn
        aria-label="editar nota"
        density="comfortable"
        icon="tabler-pencil"
        size="small"
        variant="text"
      />
    </div>

    <!-- Editor abierto. VTextarea auto-grow, acciones de eliminar y
         cerrar debajo. Save en vivo vía v-model (no hay botón Guardar
         explícito — el flujo Toast es que el textarea persiste
         cuando el cajero sale del campo). -->
    <div v-else class="inline-note__editor">
      <VTextarea
        v-model="form.meta.notes"
        autofocus
        auto-grow
        hide-details
        :label="t('order::attributes.orders.notes')"
        rows="2"
        variant="outlined"
      />
      <div class="d-flex justify-end ga-2 mt-2">
        <VBtn
          v-if="hasNote"
          color="error"
          size="small"
          variant="text"
          @click="clearNote"
        >
          {{ t('admin::admin.buttons.delete') }}
        </VBtn>
        <VBtn
          color="primary"
          size="small"
          variant="text"
          @click="closeEditor"
        >
          {{ t('admin::admin.buttons.close') }}
        </VBtn>
      </div>
    </div>
  </div>
</template>

<style lang="scss" scoped>
/* Botón collapsed invisual — text variant, color on-surface-variant.
   El cajero lo ve como una opción opcional, no como un CTA. Cuando la
   nota existe, el preview la muestra con un acento primary sutil
   (ícono) pero sin robar atención al TOTAL del footer. */
.inline-note__add {
  color: rgb(var(--v-theme-on-surface-variant));
}

.inline-note__preview {
  padding: 8px 12px;
  border-radius: 8px;
  cursor: pointer;
  background-color: rgba(var(--v-theme-primary), 0.04);
  border: 1px solid rgba(var(--v-theme-primary), 0.12);
  transition: background-color 150ms ease, border-color 150ms ease;

  &:hover {
    background-color: rgba(var(--v-theme-primary), 0.08);
    border-color: rgba(var(--v-theme-primary), 0.24);
  }
}

.inline-note__text {
  /* Wrap + limit para no romper layout con notas muy largas. */
  overflow-wrap: anywhere;
  white-space: pre-wrap;
}
</style>
