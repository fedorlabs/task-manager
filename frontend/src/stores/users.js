import { defineStore } from "pinia";
import { userService } from "@/services";

export const useUsersStore = defineStore("users", {
  state: () => ({
    users: [],
    loading: false,
    error: null,
  }),

  getters: {
    getUserById: (state) => (id) =>
      state.users.find((user) => user.id === id) ?? null,
  },

  actions: {
    async fetchUsers() {
      this.loading = true;
      this.error = null;
      try {
        this.users = await userService.fetchUsers();
      } catch (e) {
        this.error = e.message;
      } finally {
        this.loading = false;
      }
    },
  },
});
