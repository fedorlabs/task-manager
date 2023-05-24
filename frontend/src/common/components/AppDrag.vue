<template>
  <div
    :draggable="authStore.isAuthenticated"
    @dragstart.self="onDrag"
    @dragover.prevent
    @dragenter.prevent
  >
    <!--    TaskCardTags-->
    <slot />
  </div>
</template>

<script setup>
import { DATA_TRANSFER_PAYLOAD, MOVE } from "../constants";
import { useAuthStore } from "@/stores";

const props = defineProps({
  transferData: {
    type: Object,
    required: true,
  },
});

const authStore = useAuthStore();

function onDrag({ dataTransfer }) {
  dataTransfer.effectAllowed = MOVE;
  dataTransfer.dropEffect = MOVE;
  dataTransfer.setData(
    DATA_TRANSFER_PAYLOAD,
    JSON.stringify(props.transferData),
  );
}
</script>
