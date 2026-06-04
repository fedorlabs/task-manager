import { it, describe, beforeEach, expect, vi } from "vitest";
import { createPinia, setActivePinia } from "pinia";

vi.mock("@/services", () => ({
  userService: {
    fetchUsers: vi.fn(),
  },
}));

import { useUsersStore } from "@/stores";
import { userService } from "@/services";

describe("users store", () => {
  let usersStore;

  beforeEach(() => {
    vi.clearAllMocks();
    setActivePinia(createPinia());
    usersStore = useUsersStore();
  });

  it("should have initial state", () => {
    expect(usersStore.users).toEqual([]);
    expect(usersStore.loading).toBe(false);
    expect(usersStore.error).toBeNull();
  });

  describe("fetchUsers", () => {
    it("should fetch users successfully", async () => {
      const mockUsers = [
        { id: "u1", name: "Alice", email: "alice@test.com" },
        { id: "u2", name: "Bob", email: "bob@test.com" },
      ];
      userService.fetchUsers.mockResolvedValue(mockUsers);

      await usersStore.fetchUsers();

      expect(usersStore.users).toEqual(mockUsers);
      expect(usersStore.loading).toBe(false);
      expect(usersStore.error).toBeNull();
    });

    it("should set loading during fetch", async () => {
      let resolveFn;
      userService.fetchUsers.mockReturnValue(
        new Promise((resolve) => {
          resolveFn = resolve;
        }),
      );

      const promise = usersStore.fetchUsers();
      expect(usersStore.loading).toBe(true);

      resolveFn([]);
      await promise;
      expect(usersStore.loading).toBe(false);
    });

    it("should handle fetch error", async () => {
      userService.fetchUsers.mockRejectedValue(new Error("Server error"));

      await expect(usersStore.fetchUsers()).rejects.toThrow("Server error");

      expect(usersStore.users).toEqual([]);
      expect(usersStore.error).toBe("Server error");
      expect(usersStore.loading).toBe(false);
    });
  });

  describe("getUserById", () => {
    it("should return user by id", () => {
      usersStore.users = [
        { id: "u1", name: "Alice" },
        { id: "u2", name: "Bob" },
      ];

      expect(usersStore.getUserById("u1")).toEqual({ id: "u1", name: "Alice" });
    });

    it("should return null for non-existent user", () => {
      usersStore.users = [{ id: "u1", name: "Alice" }];

      expect(usersStore.getUserById("u999")).toBeNull();
    });

    it("should return null when users list is empty", () => {
      expect(usersStore.getUserById("u1")).toBeNull();
    });
  });
});
