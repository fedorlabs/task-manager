import { it, describe, beforeEach, expect, vi } from "vitest";
import { createPinia, setActivePinia } from "pinia";

vi.mock("@/services", () => ({
  tasksService: {
    fetchTasks: vi.fn(),
    createTask: vi.fn(),
    updateTask: vi.fn(),
    deleteTask: vi.fn(),
  },
  ticksService: {
    fetchTicks: vi.fn(),
  },
  userService: {
    fetchUsers: vi.fn(),
  },
}));

import {
  useTasksStore,
  useFiltersStore,
  useTicksStore,
  useUsersStore,
} from "@/stores";
import { tasksService } from "@/services";

describe("tasks store", () => {
  let tasksStore;

  beforeEach(() => {
    vi.clearAllMocks();
    setActivePinia(createPinia());
    tasksStore = useTasksStore();
  });

  it("should have initial state", () => {
    expect(tasksStore.tasks).toEqual([]);
    expect(tasksStore.loading).toBe(false);
    expect(tasksStore.error).toBeNull();
  });

  describe("fetchTasks", () => {
    it("should fetch tasks successfully", async () => {
      const mockTasks = [
        { id: 1, title: "Task 1", columnId: null },
        { id: 2, title: "Task 2", columnId: 1 },
      ];
      tasksService.fetchTasks.mockResolvedValue(mockTasks);

      await tasksStore.fetchTasks();

      expect(tasksStore.tasks).toEqual(mockTasks);
      expect(tasksStore.loading).toBe(false);
      expect(tasksStore.error).toBeNull();
    });

    it("should set loading during fetch", async () => {
      let resolveFn;
      tasksService.fetchTasks.mockReturnValue(
        new Promise((resolve) => {
          resolveFn = resolve;
        }),
      );

      const promise = tasksStore.fetchTasks();
      expect(tasksStore.loading).toBe(true);

      resolveFn([]);
      await promise;
      expect(tasksStore.loading).toBe(false);
    });

    it("should handle fetch error", async () => {
      tasksService.fetchTasks.mockRejectedValue(new Error("Network error"));

      await expect(tasksStore.fetchTasks()).rejects.toThrow("Network error");

      expect(tasksStore.tasks).toEqual([]);
      expect(tasksStore.error).toBe("Network error");
      expect(tasksStore.loading).toBe(false);
    });
  });

  describe("addTask", () => {
    it("should add task and return it", async () => {
      const newTask = { id: 3, title: "New Task", columnId: null };
      tasksService.createTask.mockResolvedValue(newTask);

      const result = await tasksStore.addTask({ title: "New Task" });

      expect(result).toEqual(newTask);
      expect(tasksStore.tasks).toContainEqual(newTask);
    });

    it("should set sortOrder based on sidebar tasks count", async () => {
      tasksStore.tasks = [
        { id: 1, title: "Sidebar 1", columnId: null },
        { id: 2, title: "In column", columnId: 1 },
      ];
      tasksService.createTask.mockResolvedValue({ id: 3, title: "New" });

      await tasksStore.addTask({ title: "New" });

      expect(tasksService.createTask).toHaveBeenCalledWith(
        expect.objectContaining({ sortOrder: 1 }),
      );
    });
  });

  describe("editTask", () => {
    it("should update task in the list", async () => {
      tasksStore.tasks = [
        { id: 1, title: "Old Title", userId: null },
        { id: 2, title: "Other" },
      ];
      const updated = { id: 1, title: "New Title", userId: null };
      tasksService.updateTask.mockResolvedValue(updated);

      const result = await tasksStore.editTask({ id: 1, title: "New Title" });

      expect(result).toEqual(updated);
      expect(tasksStore.tasks[0].title).toBe("New Title");
    });
  });

  describe("deleteTask", () => {
    it("should remove task from list", async () => {
      tasksStore.tasks = [
        { id: 1, title: "Keep" },
        { id: 2, title: "Delete" },
      ];
      tasksService.deleteTask.mockResolvedValue();

      await tasksStore.deleteTask(2);

      expect(tasksStore.tasks).toHaveLength(1);
      expect(tasksStore.tasks[0].id).toBe(1);
    });
  });

  describe("updateTasks", () => {
    it("should batch update existing tasks", async () => {
      tasksStore.tasks = [
        { id: 1, title: "Task 1", sortOrder: 0 },
        { id: 2, title: "Task 2", sortOrder: 1 },
      ];
      tasksService.updateTask.mockImplementation((task) =>
        Promise.resolve(task),
      );

      await tasksStore.updateTasks([
        { id: 1, title: "Task 1", sortOrder: 1 },
        { id: 2, title: "Task 2", sortOrder: 0 },
      ]);

      expect(tasksService.updateTask).toHaveBeenCalledTimes(2);
    });

    it("should skip tasks not in the list", async () => {
      tasksStore.tasks = [{ id: 1, title: "Task 1" }];
      tasksService.updateTask.mockImplementation((task) =>
        Promise.resolve(task),
      );

      await tasksStore.updateTasks([
        { id: 1, title: "Updated" },
        { id: 999, title: "Not found" },
      ]);

      expect(tasksService.updateTask).toHaveBeenCalledTimes(1);
    });
  });

  describe("filteredTasks", () => {
    it("should return all tasks when no filters applied", () => {
      tasksStore.tasks = [
        { id: 1, title: "A" },
        { id: 2, title: "B" },
      ];

      expect(tasksStore.filteredTasks).toHaveLength(2);
    });

    it("should filter by search text", () => {
      tasksStore.tasks = [
        { id: 1, title: "Important task" },
        { id: 2, title: "Another one" },
      ];
      const filtersStore = useFiltersStore();
      filtersStore.applyFilters({ item: "important", entity: "search" });

      expect(tasksStore.filteredTasks).toHaveLength(1);
      expect(tasksStore.filteredTasks[0].title).toBe("Important task");
    });

    it("should filter by user", () => {
      tasksStore.tasks = [
        { id: 1, title: "A", userId: "u1" },
        { id: 2, title: "B", userId: "u2" },
      ];
      const filtersStore = useFiltersStore();
      filtersStore.applyFilters({ item: "u1", entity: "users" });

      expect(tasksStore.filteredTasks).toHaveLength(1);
      expect(tasksStore.filteredTasks[0].userId).toBe("u1");
    });

    it("should filter by status", () => {
      tasksStore.tasks = [
        { id: 1, title: "A", status: "green", timeStatus: "" },
        { id: 2, title: "B", status: "red", timeStatus: "" },
      ];
      const filtersStore = useFiltersStore();
      filtersStore.applyFilters({ item: "green", entity: "statuses" });

      expect(tasksStore.filteredTasks).toHaveLength(1);
      expect(tasksStore.filteredTasks[0].status).toBe("green");
    });
  });

  describe("sidebarTasks", () => {
    it("should return only tasks without columnId sorted by sortOrder", () => {
      tasksStore.tasks = [
        { id: 1, title: "In column", columnId: 1, sortOrder: 0 },
        { id: 2, title: "Sidebar B", columnId: null, sortOrder: 2 },
        { id: 3, title: "Sidebar A", columnId: null, sortOrder: 1 },
      ];

      expect(tasksStore.sidebarTasks).toHaveLength(2);
      expect(tasksStore.sidebarTasks[0].sortOrder).toBe(1);
      expect(tasksStore.sidebarTasks[1].sortOrder).toBe(2);
    });
  });

  describe("getTaskById", () => {
    it("should return null for non-existent task", () => {
      tasksStore.tasks = [];

      expect(tasksStore.getTaskById(999)).toBeNull();
    });

    it("should return task with ticks and user", () => {
      tasksStore.tasks = [{ id: 1, title: "Task", userId: "u1" }];

      const ticksStore = useTicksStore();
      ticksStore.ticks = [
        { id: 10, taskId: 1, text: "Tick 1" },
        { id: 11, taskId: 2, text: "Other task tick" },
      ];

      const usersStore = useUsersStore();
      usersStore.users = [{ id: "u1", name: "Alice" }];

      const result = tasksStore.getTaskById(1);

      expect(result).not.toBeNull();
      expect(result.title).toBe("Task");
      expect(result.ticks).toHaveLength(1);
      expect(result.user).toEqual({ id: "u1", name: "Alice" });
    });
  });
});
