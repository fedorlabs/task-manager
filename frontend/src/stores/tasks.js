import { defineStore } from "pinia";
import { useUsersStore, useFiltersStore, useTicksStore } from "@/stores";
import { tasksService } from "@/services";

export const useTasksStore = defineStore("tasks", {
  state: () => ({
    tasks: [],
  }),
  getters: {
    filteredTasks: (state) => {
      const filtersStore = useFiltersStore();

      const filtersAreEmpty = Object.values(filtersStore.filters).every(
        (value) => !value.length
      );

      if (filtersAreEmpty) {
        // Return all tasks if no filters are applied
        return state.tasks;
      }

      // Apply search filter
      const searchFilter = (task) =>
        task.title
          .toLowerCase()
          .includes(filtersStore.filters.search.toLowerCase().trim());

      // Apply filter by users
      const usersFilter = (task) =>
        filtersStore.filters.users.some((userId) => userId === task.userId);

      // Apply filter by statuses
      const statusesFilter = (task) =>
        filtersStore.filters.statuses.some(
          (el) => el === task.status || el === task.timeStatus
        );

      // Process tasks according to filters
      return state.tasks.filter((task) => {
        let result = {
          search: searchFilter,
          users: usersFilter,
          statuses: statusesFilter,
        };
        // If there are filters, then apply them
        return Object.entries(result).every(
          ([key, callback]) =>
            !filtersStore.filters[key].length || callback(task)
        );
      });
    },
    getTaskById: (state) => (id) => {
      const ticksStore = useTicksStore();
      const usersStore = useUsersStore();
      const task = state.tasks.find((task) => +task.id === +id);
      if (!task) return null;
      // Adding subtasks
      task.ticks = ticksStore.getTicksByTaskId(task.id);
      // Adding a user
      task.user = usersStore.users.find((user) => user.id === task.userId);
      return task;
    },
    getTaskUserById: () => (id) => {
      const usersStore = useUsersStore();
      return usersStore.users.find((user) => user.id === id);
    },
    // Filter tasks that belong to the backlog (columnId === null)
    sidebarTasks: (state) => {
      return state.filteredTasks
        .filter((task) => !task.columnId)
        .sort((a, b) => a.sortOrder - b.sortOrder);
    },
  },
  actions: {
    async fetchTasks() {
      this.tasks = await tasksService.fetchTasks();
    },
    updateTasks(tasksToUpdate) {
      // Go through the current tasks in storage, if there is an index, then update
      tasksToUpdate.forEach(async (task) => {
        const index = this.tasks.findIndex(({ id }) => id === task.id);
        // ~-1 return 0, then false
        if (~index) {
          await tasksService.updateTask(task);
          this.tasks.splice(index, 1, task);
        }
      });
    },
    async addTask(task) {
      // Add the task to the end of the task list in the backlog
      task.sortOrder = this.tasks.filter((task) => !task.columnId).length;
      const newTask = await tasksService.createTask(task);
      // Add a task to the array
      this.tasks = [...this.tasks, newTask];
      return newTask;
    },
    async editTask(task) {
      const newTask = await tasksService.updateTask(task);
      const index = this.tasks.findIndex(({ id }) => newTask.id === id);
      if (~index) {
        if (newTask.userId) {
          newTask.user = { ...this.getTaskUserById(newTask.userId) };
        }
        this.tasks.splice(index, 1, newTask);
      }
      return newTask;
    },
    async deleteTask(id) {
      await tasksService.deleteTask(id);
      this.tasks = this.tasks.filter((task) => task.id !== id);
    },
  },
});
