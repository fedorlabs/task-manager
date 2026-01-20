import { defineStore } from "pinia";
import { userService } from "@/services";
import { withLoading } from "@/common/store-helpers";

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
      return withLoading(this, async () => {
        this.users = await userService.fetchUsers();
      });
    },
  },
});
