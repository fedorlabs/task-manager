import { defineStore } from "pinia";
import { authService } from "../services";
import { setToken, removeToken } from "@/services/token-manager";

export const useAuthStore = defineStore("auth", {
  state: () => ({
    user: null,
    loading: false,
    error: null,
  }),

  getters: {
    isAuthenticated: (state) => !!state.user,
    getUserAttribute: (state) => (attr) => state.user?.[attr] ?? "",
  },

  actions: {
    async login(email, password) {
      this.loading = true;
      this.error = null;
      try {
        const data = await authService.login(email, password);
        setToken(data.token);
        return "ok";
      } catch (e) {
        this.error = e.message;
        return e.message;
      } finally {
        this.loading = false;
      }
    },

    async getMe() {
      this.loading = true;
      this.error = null;
      try {
        this.user = await authService.whoAmI();
      } catch (e) {
        this.user = null;
        this.error = e.message;
      } finally {
        this.loading = false;
      }
    },

    async logout() {
      try {
        await authService.logout();
      } finally {
        this.user = null;
        removeToken();
      }
    },
  },
});
