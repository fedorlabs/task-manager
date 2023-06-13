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

// Watching the route change
watch(
  () => route.meta,
  async (meta) => {
    try {
      if (meta.layout) {
        // Trying to find the component from the meta property and dynamically import it
        const component = await import(`./${meta.layout}.vue`);
        // If the imported module contains only one value, that value will be available through the default property
        layout.value = component?.default || AppLayoutDefault;
      } else {
        layout.value = AppLayoutDefault;
      }
    } catch (e) {
      console.error(
        "Dynamic template not found. The default template is set.",
        e
      );
      layout.value = AppLayoutDefault;
    }
  }
);
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
