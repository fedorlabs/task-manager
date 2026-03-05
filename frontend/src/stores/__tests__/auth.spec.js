import { it, describe, beforeEach, expect, vi } from "vitest";
import { createPinia, setActivePinia } from "pinia";

vi.mock("@/services/token-manager", () => ({
  setToken: vi.fn(),
  removeToken: vi.fn(),
  getToken: vi.fn(() => "mock-token"),
}));

vi.mock("@/services", () => ({
  authService: {
    login: vi.fn(),
    whoAmI: vi.fn(),
    logout: vi.fn(),
  },
}));

import { useAuthStore } from "@/stores";
import { authService } from "@/services";
import { setToken, removeToken } from "@/services/token-manager";

describe("auth store", () => {
  let authStore;

  beforeEach(() => {
    vi.clearAllMocks();
    setActivePinia(createPinia());
    authStore = useAuthStore();
  });

  it("should have initial state", () => {
    expect(authStore.user).toBeNull();
    expect(authStore.loading).toBe(false);
    expect(authStore.error).toBeNull();
    expect(authStore.isAuthenticated).toBe(false);
  });

  describe("login", () => {
    it("should login successfully and set token", async () => {
      authService.login.mockResolvedValue({ token: "jwt-token-123" });

      const result = await authStore.login("admin@example.com", "admin");

      expect(result).toBe("ok");
      expect(authService.login).toHaveBeenCalledWith(
        "admin@example.com",
        "admin",
      );
      expect(setToken).toHaveBeenCalledWith("jwt-token-123");
      expect(authStore.loading).toBe(false);
      expect(authStore.error).toBeNull();
    });

    it("should handle login failure", async () => {
      authService.login.mockRejectedValue(new Error("Invalid credentials"));

      await expect(authStore.login("bad@email.com", "wrong")).rejects.toThrow(
        "Invalid credentials",
      );

      expect(authStore.error).toBe("Invalid credentials");
      expect(authStore.loading).toBe(false);
    });
  });

  describe("getMe", () => {
    it("should fetch current user", async () => {
      const user = { id: "uuid-1", name: "Admin", email: "admin@example.com" };
      authService.whoAmI.mockResolvedValue(user);

      await authStore.getMe();

      expect(authStore.user).toEqual(user);
      expect(authStore.isAuthenticated).toBe(true);
      expect(authStore.loading).toBe(false);
    });

    it("should handle getMe failure", async () => {
      authService.whoAmI.mockRejectedValue(new Error("Unauthorized"));

      await expect(authStore.getMe()).rejects.toThrow("Unauthorized");

      expect(authStore.user).toBeNull();
      expect(authStore.isAuthenticated).toBe(false);
      expect(authStore.error).toBe("Unauthorized");
    });
  });

  describe("logout", () => {
    it("should clear user and remove token", async () => {
      authStore.user = { id: "uuid-1", name: "Admin" };
      authService.logout.mockResolvedValue();

      await authStore.logout();

      expect(authStore.user).toBeNull();
      expect(removeToken).toHaveBeenCalled();
      expect(authStore.isAuthenticated).toBe(false);
    });

    it("should clear state even if logout API fails", async () => {
      authStore.user = { id: "uuid-1", name: "Admin" };
      authService.logout.mockRejectedValue(new Error("Network error"));

      await expect(authStore.logout()).rejects.toThrow("Network error");

      expect(authStore.user).toBeNull();
      expect(removeToken).toHaveBeenCalled();
    });
  });

  describe("getters", () => {
    it("getUserAttribute returns attribute value", () => {
      authStore.user = {
        id: "uuid-1",
        name: "Admin",
        isAdmin: true,
      };

      expect(authStore.getUserAttribute("name")).toBe("Admin");
      expect(authStore.getUserAttribute("isAdmin")).toBe(true);
    });

    it("getUserAttribute returns empty string when no user", () => {
      expect(authStore.getUserAttribute("name")).toBe("");
    });
  });
});
