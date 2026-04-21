<script lang="ts" setup>
import type {Component} from 'vue'
import { storeToRefs } from 'pinia'
import {useDisplay} from 'vuetify'
import {useAppStore} from "@/modules/core/stores/appStore.ts";

interface Props {
  tag?: string | Component
  isOverlayNavActive: boolean
  toggleIsOverlayNavActive: (value: boolean) => void
}

const props = withDefaults(defineProps<Props>(), {
  tag: 'aside',
})

const {mdAndDown} = useDisplay()
const appStore = useAppStore()
const { isRtl, logo, appName } = storeToRefs(appStore)

const refNav = ref()

/*
ℹ️ Close overlay side when route is changed
Close overlay vertical nav when link is clicked
*/
const route = useRoute()

watch(
  () => route.path,
  () => {
    props.toggleIsOverlayNavActive(false)
  })

const isVerticalNavScrolled = ref(false)
const updateIsVerticalNavScrolled = (val: boolean) => isVerticalNavScrolled.value = val

const handleNavScroll = (evt: Event) => {
  isVerticalNavScrolled.value = (evt.target as HTMLElement).scrollTop > 0
}

</script>

<template>
  <Component
    :is="props.tag"
    ref="refNav"
    :class="[
      {
        'visible': isOverlayNavActive,
        'scrolled': isVerticalNavScrolled,
        'overlay-nav': mdAndDown,
        'layout-vertical-nav-rtl':isRtl,
        'layout-with-hidden-vertical-nav': route.meta.hiddenSidebar,
        'layout-with-hidden-vertical-nav-rtl':isRtl && route.meta.hiddenSidebar
      },
    ]"
    class="layout-vertical-nav  overlay-nav"
    data-allow-mismatch
  >
    <!-- 👉 Header -->
    <div class="nav-header">
      <slot name="nav-header">
        <RouterLink
          class="app-logo app-title-wrapper"
          to="/"
        >
          <img
            v-if="logo"
            :src="logo"
            alt="logo"
            height="50px"
            width="50px"
          >

          <h1 class="leading-normal">
            {{ appName }}
          </h1>
        </RouterLink>
      </slot>
    </div>
    <slot name="before-nav-items">
      <div class="vertical-nav-items-shadow"/>
    </slot>
    <slot
      :update-is-vertical-nav-scrolled="updateIsVerticalNavScrolled"
      name="nav-items"
    >
      <ul
        class="nav-items"
        @scroll.passive="handleNavScroll"
      >
        <slot/>
      </ul>
    </slot>
    <slot name="after-nav-items"/>
  </Component>
</template>

<style lang="scss" scoped>
.app-logo {
  display: flex;
  align-items: center;
  column-gap: 0.75rem;

  .app-logo-title {
    font-size: 1.25rem;
    font-weight: 500;
    line-height: 1.75rem;
    text-transform: uppercase;
  }
}
</style>

<style lang="scss">
@use "@configured-variables" as variables;
@use "@/assets/scss/layouts/styles/mixins";

// 👉 Vertical Nav
.layout-vertical-nav {
  position: fixed;
  z-index: variables.$layout-vertical-nav-z-index;
  display: flex;
  flex-direction: column;
  block-size: 100%;
  inline-size: variables.$layout-vertical-nav-width;
  inset-block-start: 0;
  inset-inline-start: 0;
  transition: inline-size 0.25s ease-in-out, box-shadow 0.25s ease-in-out;
  will-change: transform, inline-size;

  .nav-header {
    display: flex;
    align-items: center;

    .header-action {
      cursor: pointer;

      @at-root {
        #{variables.$selector-vertical-nav-mini} .nav-header .header-action {
          &.nav-pin,
          &.nav-unpin {
            display: none !important;
          }
        }
      }
    }
  }

  .app-title-wrapper {
    margin-inline-end: auto;
  }

  .nav-items {
    block-size: 100%;
    overflow-x: hidden;
    overflow-y: auto;
  }

  .nav-item-title {
    overflow: hidden;
    margin-inline-end: auto;
    text-overflow: ellipsis;
    white-space: nowrap;
  }

  // 👉 Collapsed
  .layout-vertical-nav-collapsed & {
    &:not(.hovered) {
      inline-size: variables.$layout-vertical-nav-collapsed-width;
    }
  }
}

// Small screen vertical nav transition
@media (max-width: 1279px) {
  .layout-vertical-nav {
    &:not(.visible) {
      transform: translateX(-#{variables.$layout-vertical-nav-width});
    }

    transition: transform 0.25s ease-in-out;
  }

  .layout-vertical-nav-rtl {
    &:not(.visible) {
      transform: translateX(variables.$layout-vertical-nav-width);
    }
  }
}

.layout-with-hidden-vertical-nav {
  &:not(.visible) {
    transform: translateX(-#{variables.$layout-vertical-nav-width});
  }

  transition: transform 0.25s ease-in-out;
}

.layout-with-hidden-vertical-nav-rtl {
  &:not(.visible) {
    transform: translateX(variables.$layout-vertical-nav-width);
  }
}

</style>
