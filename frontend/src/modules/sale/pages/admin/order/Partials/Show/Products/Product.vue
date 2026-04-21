<script lang="ts" setup>
  import { useAuth } from '@/modules/auth/composables/auth.ts'

  defineProps<{
    product: Record<string, any>
  }>()

  const { can } = useAuth()
</script>

<template>
  <td>
    <span class="product-name">
      {{ product.product.name }}
    </span>
    <div v-if="product.options?.length" class="ml-4">
      <div
        v-for="opt in product.options"
        :key="opt.id"
      >
        <div class="option-container mt-1">
          <span class="option-name">
            {{ opt.name }} :
          </span>
          <span
            v-for="(val,index) in opt.values"
            :key="val.id"
          >
            {{ val.label || opt.value }}
            <span v-if="val.price['original'].amount > 0" class="ml-1 text-grey-darken-1">
              (<Money :money="val.price" />)
            </span>
            <span v-if="opt.values.length-1 !== index">, &nbsp;</span>
          </span>
        </div>
      </div>
    </div>
  </td>
  <td>
    <TableStatus :item="product" />
  </td>
  <td>
    <Money :money="product.unit_price" />
  </td>
  <td>{{ product.quantity }}</td>
  <td>
    <Money :money="product.subtotal" />
  </td>
  <td>
    <Money :money="product.tax_total" />
  </td>
  <td>
    <Money :money="product.total" />
  </td>
  <template v-if="can('admin.orders.financials')">
    <td>
      <Money :money="product.cost_price" />
    </td>
    <td>
      <Money :money="product.revenue" />
    </td>
  </template>
</template>

<style lang="scss" scoped>
.product-name {
  font-weight: 700;
}

.option-container {
  font-size: 13px;

  .option-name {
    font-weight: 700;
  }
}

</style>
