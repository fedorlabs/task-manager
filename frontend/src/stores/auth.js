import { defineStore } from "pinia";
import { authService } from "../services";
import { setToken, removeToken } from "@/services/token-manager";

export const useAuthStore = defineStore("auth", {
  state: () => ({
    user: null,
  }),
  getters: {},
  actions: {},
});
