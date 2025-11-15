import { it, describe, beforeEach, expect } from "vitest";
import { createPinia, setActivePinia } from "pinia";
import { useFiltersStore } from "@/stores";

describe("filters store", () => {
  let filtersStore;

  beforeEach(() => {
    setActivePinia(createPinia());
    filtersStore = useFiltersStore();
  });

  it("should have initial empty state", () => {
    expect(filtersStore.search).toBe("");
    expect(filtersStore.users).toEqual([]);
    expect(filtersStore.statuses).toEqual([]);
  });

  it("should return filters getter", () => {
    expect(filtersStore.filters).toEqual({
      search: "",
      users: [],
      statuses: [],
    });
  });

  describe("applyFilters", () => {
    it("should apply search filter", () => {
      filtersStore.applyFilters({ item: "task name", entity: "search" });

      expect(filtersStore.search).toBe("task name");
    });

    it("should toggle user filter on", () => {
      filtersStore.applyFilters({ item: "user-uuid-1", entity: "users" });

      expect(filtersStore.users).toEqual(["user-uuid-1"]);
    });

    it("should toggle user filter off", () => {
      filtersStore.applyFilters({ item: "user-uuid-1", entity: "users" });
      filtersStore.applyFilters({ item: "user-uuid-1", entity: "users" });

      expect(filtersStore.users).toEqual([]);
    });

    it("should handle multiple user filters", () => {
      filtersStore.applyFilters({ item: "user-1", entity: "users" });
      filtersStore.applyFilters({ item: "user-2", entity: "users" });

      expect(filtersStore.users).toEqual(["user-1", "user-2"]);
    });

    it("should toggle status filter", () => {
      filtersStore.applyFilters({ item: "green", entity: "statuses" });

      expect(filtersStore.statuses).toEqual(["green"]);
    });
  });

  describe("resetFilters", () => {
    it("should reset all filters", () => {
      filtersStore.applyFilters({ item: "search text", entity: "search" });
      filtersStore.applyFilters({ item: "user-1", entity: "users" });
      filtersStore.applyFilters({ item: "green", entity: "statuses" });

      filtersStore.resetFilters();

      expect(filtersStore.search).toBe("");
      expect(filtersStore.users).toEqual([]);
      expect(filtersStore.statuses).toEqual([]);
    });
  });
});
