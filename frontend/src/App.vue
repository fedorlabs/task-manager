<template>
  <app-layout>
    <router-view />
  </app-layout>
</template>

<script setup>
import { AppLayout } from "./layouts";
import {
  useTasksStore,
  useColumnsStore,
  useUsersStore,
  useCommentsStore,
  useTicksStore,
} from "./stores";

// Defining repositories
const tasksStore = useTasksStore();
const usersStore = useUsersStore();
const columnsStore = useColumnsStore();
const commentsStore = useCommentsStore();
const ticksStore = useTicksStore();

// Loading the initial data
void tasksStore.fetchTasks();
void usersStore.fetchUsers();
void columnsStore.fetchColumns();
void commentsStore.fetchComments();
void ticksStore.fetchTicks();
</script>

<style lang="scss">
@import "@/assets/scss/app.scss";
</style>
