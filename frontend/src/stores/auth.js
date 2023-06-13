import { defineStore } from "pinia";
import { authService } from "../services";
import { setToken, removeToken } from "@/services/token-manager";

export const useAuthStore = defineStore("auth", {
  state: () => ({
    user: null,
  }),
  // Functions that allow you to access the application's state from the current store.
  // This means we can use getters to calculate values based on the current state.
  getters: {
    isAuthenticated: (state) => !!state.user, // null => false
    getUserAttribute: (state) => (attr) => state.user ? state.user[attr] : "",
  },
  // Methods that can be called to modify and update the data in this store
  actions: {
    async login(email, password) {
      try {
        const data = await authService.login(email, password);
        setToken(data.token);
        return "ok";
      } catch (e) {
        return e.message;
      }
    },
    async getMe() {
      this.user = await authService.whoAmI();
    },
    async logout() {
      await authService.logout();
      this.user = null;
      removeToken();
    },
  },
});
