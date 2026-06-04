import { defineStore } from "pinia";
import { useUsersStore, useFiltersStore, useTicksStore } from "@/stores";
import { tasksService } from "@/services";

export const useTasksStore = defineStore("tasks", {
  state: () => ({
    tasks: [],
    loading: false,
    error: null,
  }),

  getters: {
    filteredTasks(state) {
      const filtersStore = useFiltersStore();
      const { search, users, statuses } = filtersStore.filters;

      const hasFilters = search.length || users.length || statuses.length;
      if (!hasFilters) {
        return state.tasks;
      }

      return state.tasks.filter((task) => {
        if (search.length) {
          const match = task.title
            .toLowerCase()
            .includes(search.toLowerCase().trim());
          if (!match) return false;
        }

        if (users.length) {
          if (!users.includes(task.userId)) return false;
        }

        if (statuses.length) {
          const match = statuses.some(
            (s) => s === task.status || s === task.timeStatus,
          );
          if (!match) return false;
        }

        return true;
      });
    },

    getTaskById: (state) => (id) => {
      const ticksStore = useTicksStore();
      const usersStore = useUsersStore();
      const task = state.tasks.find((t) => +t.id === +id);
      if (!task) return null;

      return {
        ...task,
        ticks: ticksStore.getTicksByTaskId(task.id),
        user: usersStore.users.find((u) => u.id === task.userId) ?? null,
      };
    },

    getTaskUserById: () => (id) => {
      const usersStore = useUsersStore();
      return usersStore.users.find((u) => u.id === id) ?? null;
    },

    sidebarTasks() {
      return this.filteredTasks
        .filter((task) => !task.columnId)
        .sort((a, b) => a.sortOrder - b.sortOrder);
    },
  },

  actions: {
    async fetchTasks() {
      this.loading = true;
      this.error = null;
      try {
        this.tasks = await tasksService.fetchTasks();
      } catch (e) {
        this.error = e.message;
      } finally {
        this.loading = false;
      }
    },

    async updateTasks(tasksToUpdate) {
      const updates = tasksToUpdate
        .filter((task) => this.tasks.findIndex(({ id }) => id === task.id) !== -1)
        .map(async (task) => {
          await tasksService.updateTask(task);
          const index = this.tasks.findIndex(({ id }) => id === task.id);
          if (index !== -1) {
            this.tasks.splice(index, 1, task);
          }
        });

      await Promise.all(updates);
    },

    async addTask(task) {
      task.sortOrder = this.tasks.filter((t) => !t.columnId).length;
      const newTask = await tasksService.createTask(task);
      this.tasks = [...this.tasks, newTask];
      return newTask;
    },

    async editTask(task) {
      const newTask = await tasksService.updateTask(task);
      const index = this.tasks.findIndex(({ id }) => newTask.id === id);
      if (index !== -1) {
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
