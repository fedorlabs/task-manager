<template>
  <task-card-creator v-if="task" :task-to-edit="task" />
</template>

<script setup>
import TaskCardCreator from "../modules/tasks/components/TaskCardCreator.vue";
import { useRoute, useRouter } from "vue-router";
import { createNewDate } from "@/common/helpers";
import { useTasksStore } from "@/stores";

const tasksStore = useTasksStore();

const route = useRoute();
const router = useRouter();

// Find the task from the array of tasks by id from the URL string
const task = tasksStore.getTaskById(route.params.id);

if (task) {
  const taskDate = task.dueDate;
  task.dueDate = taskDate ? new Date(taskDate) : createNewDate();
} else {
  // Redirect to the main page if the task is not found
  router.push("/");
}
</script>
