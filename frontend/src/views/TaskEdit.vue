<template>
  <task-card-creator v-if="taskToEdit" :task-to-edit="taskToEdit" />
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
const rawTask = tasksStore.getTaskById(route.params.id);

const taskToEdit = rawTask
  ? {
      ...rawTask,
      dueDate: rawTask.dueDate ? new Date(rawTask.dueDate) : createNewDate(),
    }
  : null;

if (!taskToEdit) {
  // Redirect to the main page if the task is not found
  router.push("/");
}
</script>
