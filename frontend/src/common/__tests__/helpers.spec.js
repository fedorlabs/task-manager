import { it, describe, expect, vi, beforeEach, afterEach } from "vitest";
import {
  getTagsArrayFromString,
  getTimeStatus,
  normalizeTask,
  getTargetColumnTasks,
  addActive,
  getTimeAgo,
  getReadableDate,
  createUUIDv4,
  createNewDate,
  getPublicImage,
} from "@/common/helpers";

describe("helpers", () => {
  describe("getTagsArrayFromString", () => {
    it("should split tags by # separator", () => {
      const result = getTagsArrayFromString("#vue#react#angular");

      expect(result).toEqual(["vue", "react", "angular"]);
    });

    it("should handle single tag", () => {
      const result = getTagsArrayFromString("#javascript");

      expect(result).toEqual(["javascript"]);
    });

    it("should return empty array for empty string with separator", () => {
      const result = getTagsArrayFromString("#");

      expect(result).toEqual([""]);
    });
  });

  describe("getTimeStatus", () => {
    beforeEach(() => {
      vi.useFakeTimers();
      vi.setSystemTime(new Date("2025-06-15T12:00:00Z"));
    });

    afterEach(() => {
      vi.useRealTimers();
    });

    it("should return empty string when no dueDate", () => {
      expect(getTimeStatus(null)).toBe("");
      expect(getTimeStatus("")).toBe("");
    });

    it("should return empty string when due date is more than a day away", () => {
      expect(getTimeStatus("2025-06-17T12:00:00Z")).toBe("");
    });

    it("should return 'time' (EXPIRED) for deadline within a day", () => {
      expect(getTimeStatus("2025-06-15T23:00:00Z")).toBe("time");
    });

    it("should return 'alert' (DEADLINE) for overdue tasks", () => {
      expect(getTimeStatus("2025-06-14T00:00:00Z")).toBe("alert");
    });
  });

  describe("normalizeTask", () => {
    beforeEach(() => {
      vi.useFakeTimers();
      vi.setSystemTime(new Date("2025-06-15T12:00:00Z"));
    });

    afterEach(() => {
      vi.useRealTimers();
    });

    it("should add status enum value based on statusId", () => {
      const task = { id: 1, title: "Test", statusId: 1, dueDate: null };
      const result = normalizeTask(task);

      expect(result.status).toBe("green");
    });

    it("should set empty status when no statusId", () => {
      const task = { id: 1, title: "Test", statusId: null, dueDate: null };
      const result = normalizeTask(task);

      expect(result.status).toBe("");
    });

    it("should add timeStatus based on dueDate", () => {
      const task = {
        id: 1,
        title: "Test",
        statusId: null,
        dueDate: "2025-06-14T00:00:00Z",
      };
      const result = normalizeTask(task);

      expect(result.timeStatus).toBe("alert");
    });

    it("should preserve original task properties", () => {
      const task = { id: 1, title: "Test", statusId: 2, dueDate: null };
      const result = normalizeTask(task);

      expect(result.id).toBe(1);
      expect(result.title).toBe("Test");
      expect(result.status).toBe("orange");
    });
  });

  describe("getTargetColumnTasks", () => {
    it("should return tasks for a specific column", () => {
      const tasks = [
        { id: 1, columnId: 1, title: "A" },
        { id: 2, columnId: 2, title: "B" },
        { id: 3, columnId: 1, title: "C" },
      ];

      const result = getTargetColumnTasks(1, tasks);

      expect(result).toHaveLength(2);
      expect(result[0].title).toBe("A");
      expect(result[1].title).toBe("C");
    });

    it("should return empty array when no tasks match", () => {
      const tasks = [{ id: 1, columnId: 1, title: "A" }];

      expect(getTargetColumnTasks(99, tasks)).toEqual([]);
    });
  });

  describe("addActive", () => {
    it("should add active task to end when no toTask", () => {
      const tasks = [
        { id: 1, sortOrder: 0 },
        { id: 2, sortOrder: 1 },
      ];
      const active = { id: 3, sortOrder: 2 };

      const result = addActive(active, null, tasks);

      expect(result).toHaveLength(3);
      expect(result[result.length - 1].id).toBe(3);
    });

    it("should insert active task before toTask", () => {
      const tasks = [
        { id: 1, sortOrder: 0 },
        { id: 2, sortOrder: 1 },
      ];
      const active = { id: 3, sortOrder: 2 };
      const toTask = { id: 2, sortOrder: 1 };

      const result = addActive(active, toTask, tasks);

      expect(result).toHaveLength(3);
      const ids = result.map((t) => t.id);
      expect(ids.indexOf(3)).toBeLessThan(ids.indexOf(2));
    });

    it("should remove active from tasks if already present", () => {
      const tasks = [
        { id: 1, sortOrder: 0 },
        { id: 2, sortOrder: 1 },
        { id: 3, sortOrder: 2 },
      ];
      const active = { id: 2, sortOrder: 1 };

      const result = addActive(active, null, tasks);

      expect(result).toHaveLength(3);
      expect(result[result.length - 1].id).toBe(2);
    });
  });

  describe("getTimeAgo", () => {
    beforeEach(() => {
      vi.useFakeTimers();
      vi.setSystemTime(new Date("2025-06-15T12:00:00Z"));
    });

    afterEach(() => {
      vi.useRealTimers();
    });

    it("should return 'now' for very recent dates", () => {
      expect(getTimeAgo("2025-06-15T11:59:30Z")).toBe("now");
    });

    it("should return minutes ago", () => {
      expect(getTimeAgo("2025-06-15T11:50:00Z")).toContain("minute");
    });

    it("should return hours ago", () => {
      expect(getTimeAgo("2025-06-15T08:00:00Z")).toContain("hour");
    });

    it("should return days ago", () => {
      expect(getTimeAgo("2025-06-10T12:00:00Z")).toContain("day");
    });

    it("should return months ago", () => {
      expect(getTimeAgo("2025-02-15T12:00:00Z")).toContain("month");
    });

    it("should return years ago", () => {
      expect(getTimeAgo("2023-06-15T12:00:00Z")).toContain("year");
    });

    it("should return fallback for invalid date", () => {
      expect(getTimeAgo("invalid")).toBe("... no time specified ...");
    });
  });

  describe("getReadableDate", () => {
    it("should format date as day.month.year", () => {
      expect(getReadableDate("2025-06-15T12:00:00Z")).toBe("15.6.2025");
    });

    it("should return empty string for invalid date", () => {
      expect(getReadableDate("invalid")).toBe("");
    });
  });

  describe("createUUIDv4", () => {
    it("should return a valid UUID v4 format", () => {
      const uuid = createUUIDv4();
      const uuidRegex =
        /^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/;

      expect(uuid).toMatch(uuidRegex);
    });

    it("should generate unique values", () => {
      const uuid1 = createUUIDv4();
      const uuid2 = createUUIDv4();

      expect(uuid1).not.toBe(uuid2);
    });
  });

  describe("createNewDate", () => {
    it("should return a date with time set to 23:59:59.999", () => {
      const date = createNewDate();

      expect(date.getHours()).toBe(23);
      expect(date.getMinutes()).toBe(59);
      expect(date.getSeconds()).toBe(59);
      expect(date.getMilliseconds()).toBe(999);
    });
  });

  describe("getPublicImage", () => {
    it("should prepend /api/ to path", () => {
      expect(getPublicImage("images/avatar.png")).toBe(
        "/api/images/avatar.png",
      );
    });

    it("should return empty string for null path", () => {
      expect(getPublicImage(null)).toBe("");
    });

    it("should return empty string for undefined path", () => {
      expect(getPublicImage(undefined)).toBe("");
    });

    it("should return empty string for empty string path", () => {
      expect(getPublicImage("")).toBe("");
    });
  });
});
