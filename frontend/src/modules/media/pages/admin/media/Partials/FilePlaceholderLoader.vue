<template>
  <div class="file-container">
    <!-- Image Placeholder -->
    <div class="placeholder-img" />

    <!-- Filename Placeholder -->
    <div class="placeholder-text filename" />

    <!-- File Size Placeholder -->
    <div class="placeholder-text size" />
  </div>
</template>

<style scoped>
.file-container {
  margin: 6px;
  width: 150px;
  height: 150px;
  border: 1px dashed #d2d6de;
  border-radius: 6px;
  text-align: center;
  padding-top: 12px;
  display: flex;
  flex-direction: column;
  align-items: center;
}

.placeholder-img {
  width: 90%;
  height: 64px;
  border-radius: 4px;
  background-color: #e0e0e0;
  animation: pulse 1.5s infinite;
  margin-bottom: 8px;
}

.placeholder-text {
  background-color: #e0e0e0;
  height: 14px;
  border-radius: 3px;
  animation: pulse 1.5s infinite;
}

.filename {
  width: 70%;
  margin-bottom: 4px;
}

.size {
  width: 40%;
}

@keyframes pulse {
  0% {
    opacity: 1;
  }
  50% {
    opacity: 0.4;
  }
  100% {
    opacity: 1;
  }
}
</style>
