<template>
  <div class="me-2 custom-navbar-container">
    <slot/>
  </div>
</template>

<style lang="scss" scoped>
.custom-navbar-container {
  border-radius: 50%;
  border: 1px solid #ededed;
  width: 40px;
  height: 40px;
  display: flex;
  justify-content: center;
  align-items: center;
}
</style>
