import { it, describe, beforeEach, expect, vi } from "vitest";
import { createPinia, setActivePinia } from "pinia";

vi.mock("@/services", () => ({
  ticksService: {
    fetchTicks: vi.fn(),
    createTick: vi.fn(),
    updateTick: vi.fn(),
    deleteTick: vi.fn(),
  },
}));

import { useTicksStore } from "@/stores";
import { ticksService } from "@/services";

describe("ticks store", () => {
  let ticksStore;

  beforeEach(() => {
    vi.clearAllMocks();
    setActivePinia(createPinia());
    ticksStore = useTicksStore();
  });

  it("should have initial state", () => {
    expect(ticksStore.ticks).toEqual([]);
    expect(ticksStore.loading).toBe(false);
    expect(ticksStore.error).toBeNull();
  });

  describe("fetchTicks", () => {
    it("should fetch ticks successfully", async () => {
      const mockTicks = [
        { id: 1, text: "Tick 1", done: false, taskId: 1 },
        { id: 2, text: "Tick 2", done: true, taskId: 1 },
      ];
      ticksService.fetchTicks.mockResolvedValue(mockTicks);

      await ticksStore.fetchTicks();

      expect(ticksStore.ticks).toEqual(mockTicks);
      expect(ticksStore.loading).toBe(false);
      expect(ticksStore.error).toBeNull();
    });

    it("should set loading during fetch", async () => {
      let resolveFn;
      ticksService.fetchTicks.mockReturnValue(
        new Promise((resolve) => {
          resolveFn = resolve;
        }),
      );

      const promise = ticksStore.fetchTicks();
      expect(ticksStore.loading).toBe(true);

      resolveFn([]);
      await promise;
      expect(ticksStore.loading).toBe(false);
    });

    it("should handle fetch error", async () => {
      ticksService.fetchTicks.mockRejectedValue(new Error("Connection lost"));

      await expect(ticksStore.fetchTicks()).rejects.toThrow("Connection lost");

      expect(ticksStore.ticks).toEqual([]);
      expect(ticksStore.error).toBe("Connection lost");
      expect(ticksStore.loading).toBe(false);
    });
  });

  describe("addTick", () => {
    it("should add tick and return it", async () => {
      const newTick = { id: 3, text: "New tick", done: false, taskId: 1 };
      ticksService.createTick.mockResolvedValue(newTick);

      const result = await ticksStore.addTick({
        text: "New tick",
        taskId: 1,
      });

      expect(result).toEqual(newTick);
      expect(ticksStore.ticks).toContainEqual(newTick);
    });

    it("should append to existing ticks", async () => {
      ticksStore.ticks = [{ id: 1, text: "Existing" }];
      ticksService.createTick.mockResolvedValue({
        id: 2,
        text: "New",
      });

      await ticksStore.addTick({ text: "New" });

      expect(ticksStore.ticks).toHaveLength(2);
    });
  });

  describe("updateTick", () => {
    it("should update tick in list", async () => {
      ticksStore.ticks = [
        { id: 1, text: "Tick 1", done: false },
        { id: 2, text: "Tick 2", done: false },
      ];
      ticksService.updateTick.mockResolvedValue();

      await ticksStore.updateTick({ id: 1, text: "Tick 1", done: true });

      expect(ticksStore.ticks[0].done).toBe(true);
    });

    it("should not fail when tick is not in list", async () => {
      ticksStore.ticks = [{ id: 1, text: "Tick 1" }];
      ticksService.updateTick.mockResolvedValue();

      await ticksStore.updateTick({ id: 999, text: "Unknown" });

      expect(ticksStore.ticks).toHaveLength(1);
    });
  });

  describe("deleteTick", () => {
    it("should remove tick from list", async () => {
      ticksStore.ticks = [
        { id: 1, text: "Keep" },
        { id: 2, text: "Delete" },
      ];
      ticksService.deleteTick.mockResolvedValue();

      await ticksStore.deleteTick(2);

      expect(ticksStore.ticks).toHaveLength(1);
      expect(ticksStore.ticks[0].id).toBe(1);
    });
  });

  describe("getTicksByTaskId", () => {
    it("should return ticks for specific task", () => {
      ticksStore.ticks = [
        { id: 1, text: "A", taskId: 1 },
        { id: 2, text: "B", taskId: 2 },
        { id: 3, text: "C", taskId: 1 },
      ];

      const result = ticksStore.getTicksByTaskId(1);

      expect(result).toHaveLength(2);
      expect(result[0].text).toBe("A");
      expect(result[1].text).toBe("C");
    });

    it("should return empty array when no ticks for task", () => {
      ticksStore.ticks = [{ id: 1, text: "A", taskId: 5 }];

      expect(ticksStore.getTicksByTaskId(99)).toEqual([]);
    });
  });
});
