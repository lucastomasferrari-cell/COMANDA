<script lang="ts" setup>
  import { onBeforeMount, ref } from 'vue'
  import { useI18n } from 'vue-i18n'
  import { type RouteLocationRaw, useRoute, useRouter } from 'vue-router'
  import { useToast } from 'vue-toastification'
  import { useForm } from '@/modules/core/composables/form.ts'
  import { useProduct } from '@/modules/menu/composables/product.ts'
  import General from './General.vue'
  import Ingredients from './Ingredient/Index.vue'
  import Media from './Media.vue'
  import Options from './Options/Index.vue'
  import Pricing from './Pricing.vue'

  const props = defineProps<{
    item?: Record<string, any> | null
    action: 'update' | 'create'
  }>()

  const { getFormMeta, update, store } = useProduct()
  const router = useRouter()
  const route = useRoute()
  const menuId = (route.params as Record<string, any>)?.menuId
  const toast = useToast()
  const { t } = useI18n()
  // Campos removidos post-refactor Fix 3:
  // - taxes: IVA se maneja a nivel fiscal (AFIP WSFE futuro), no por producto.
  // - special_price*: descuentos viven en Marketing > Descuentos.
  // - new_from/new_to: no hay menú digital que muestre badge "Nuevo".
  // Las columnas siguen en DB (nullable) por compat con el vendor; al no
  // enviarse, el backend guarda null y todo sigue funcionando. Las rules
  // ya son `nullable` en SaveProductRequest.
  const form = useForm({
    name: props.item?.name || {},
    description: !props.item?.description || Array.isArray(props.item?.description) ? {} : props.item?.description,
    categories: props.item?.categories || [],
    is_active: props.item?.is_active || false,
    menu_id: menuId,
    sku: props.item?.sku,
    sku_locked: props.item?.sku_locked || false,
    price: props.item?.price?.amount,
    options: (props.item?.options || []).map((option: Record<string, any>) => ({
      ...option,
      type: option.type.id,
      values: (option.values || []).map((v: Record<string, any>) => ({
        ...v,
        label: v.label || {},
        price: (v.price?.amount === undefined ? v.price : v.price?.amount),
        ingredients: (v.ingredients || []).map((i: Record<string, any>) => ({
          ...i,
          ingredient_id: i.ingredient.id,
          operation: i.operation.id,
        })),
      })),
    })),
    files: {
      thumbnail: props.item?.thumbnail?.id || null,
    },
    ingredients: (props.item?.ingredients || []).map((i: Record<string, any>) => ({
      ...i,
      ingredient_id: i.ingredient.id,
      operation: i.operation.id,
    })),
  })

  const meta = ref({
    categories: [],
    optionTypes: [],
    optionTemplates: [],
    ingredients: [],
    ingredientOperations: [],
    currency: 'JO',
  })

  async function submit () {
    if (
      !form.loading.value
      && await form.submit(() => props.action == 'create' ? store(form.state) : update(props.item?.id, form.state))
    ) {
      await goToListPage()
    } else {
      if (form.errors.value.menu_id) {
        toast.error(t('menu::menus.menu_is_not_active'))
      }
    }
  }

  onBeforeMount(async () => {
    try {
      const response = (await getFormMeta(menuId)).data.body
      meta.value.categories = response.categories
      meta.value.optionTypes = response.option_types
      meta.value.optionTemplates = response.option_templates
      meta.value.ingredients = response.ingredients
      meta.value.ingredientOperations = response.ingredient_operations
      form.state.menu_id = response.menu_id
      meta.value.currency = response.currency
      if (!response.menu_id) {
        toast.error(t('menu::menus.menu_is_not_active'))
      }
    } catch {
    /* Empty */
    }
  })

  async function goToListPage () {
    const menuId = (route.params as Record<string, any>)?.menuId
    await (menuId
      ? router.push({
        name: 'admin.menus.products.index',
        params: { menuId },
      } as unknown as RouteLocationRaw)
      : router.push({ name: 'admin.products.index' } as unknown as RouteLocationRaw))
  }
</script>

<template>
  <BaseForm
    v-slot="{ currentLanguage }"
    :action="action"
    :disabled="!form.state.menu_id"
    has-multiple-language
    :loading="form.loading.value"
    :on-click-cancel="goToListPage"
    resource="products"
    @submit="submit"
  >
    <VRow>
      <VCol cols="12" md="8">
        <General :action="action" :current-language="currentLanguage" :form="form" :meta="meta" />
        <Ingredients :current-language="currentLanguage" :form="form" :meta="meta" />
        <Options :current-language="currentLanguage" :form="form" :meta="meta" />
      </VCol>
      <VCol cols="12" md="4">
        <Media :form="form" :item="item" />
        <Pricing :form="form" :meta="meta" />
      </VCol>
    </VRow>
  </BaseForm>
</template>
