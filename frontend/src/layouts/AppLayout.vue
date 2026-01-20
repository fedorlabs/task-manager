<template>
  <component :is="layout">
    <slot />
  </component>
</template>

<script setup>
import { shallowRef, watch } from "vue";
import { useRoute } from "vue-router";
import AppLayoutDefault from "./AppLayoutDefault.vue";

const route = useRoute();
const layout = shallowRef(null);

// Set layout immediately on mount and watch for route changes
const setLayout = async (meta) => {
  try {
    if (meta.layout) {
      const component = await import(`./${meta.layout}.vue`);
      layout.value = component?.default || AppLayoutDefault;
    } else {
      layout.value = AppLayoutDefault;
    }
  } catch (e) {
    console.error(
      "Dynamic template not found. The default template is set.",
      e,
    );
    layout.value = AppLayoutDefault;
  }
};

// Immediate watch to set layout on initial load
watch(() => route.meta, setLayout, { immediate: true });
</script>

<style lang="scss" scoped>
.app_layout {
  display: flex;
  flex-direction: column;
  height: 100vh;
}

.content {
  display: flex;
  flex-grow: 1;
}
</style>
