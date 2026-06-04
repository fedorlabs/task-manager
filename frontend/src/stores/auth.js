import { defineStore } from "pinia";
import { authService } from "../services";
import { setToken, removeToken } from "@/services/token-manager";
import { withLoading } from "@/common/store-helpers";

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
      const result = await withLoading(this, async () => {
        const data = await authService.login(email, password);
        setToken(data.token);
        return "ok";
      });
      // withLoading swallows errors, return error message if not "ok"
      return result ?? this.error;
    },

    async getMe() {
      const result = await withLoading(this, async () => {
        this.user = await authService.whoAmI();
      });
      if (this.error) {
        this.user = null;
      }
      return result;
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
