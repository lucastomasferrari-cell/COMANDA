<script lang="ts" setup>
  import DefaultLayoutWithVerticalNav from '@/app/layouts/components/DefaultLayoutWithVerticalNav.vue'

  const route = useRoute()

  const routerViewKey = computed(() => {
    const params = Object.keys(route.params)
      .sort()
      .map(k => `${k}:${route.params[k]}`)
      .join('|')

    const query = new URLSearchParams(
      Object.entries(route.query).reduce<Record<string, string>>(
        (acc, [key, value]) => {
          if (Array.isArray(value)) {
            acc[key] = value.join(',')
          } else if (value != null) {
            acc[key] = String(value)
          }
          return acc
        },
        {},
      ),
    ).toString()

    return `${String(route.name ?? '')}-${params}-${query}`
  })

</script>

<template>
  <DefaultLayoutWithVerticalNav :target="'admin'">
    <RouterView :key="routerViewKey" />
  </DefaultLayoutWithVerticalNav>
</template>

<style lang="scss">
@use "@/assets/scss/layouts/styles/default-layout";
</style>
