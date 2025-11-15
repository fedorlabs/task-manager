import { it, describe, expect, vi, beforeEach, afterEach } from "vitest";
import { useTaskCardDate } from "@/common/composables";

describe("useTaskCardDate", () => {
  beforeEach(() => {
    vi.useFakeTimers();
    vi.setSystemTime(new Date("2025-06-15T12:00:00Z"));
  });

  afterEach(() => {
    vi.useRealTimers();
  });

  it("should return computed string with task id and time ago", () => {
    const task = { id: 42, dueDate: "2025-06-10T12:00:00Z" };

    const result = useTaskCardDate(task);

    expect(result.value).toContain("# 42");
    expect(result.value).toContain("established");
    expect(result.value).toContain("day");
  });

  it("should handle task without dueDate", () => {
    const task = { id: 1, dueDate: null };

    const result = useTaskCardDate(task);

    expect(result.value).toContain("# 1");
    expect(result.value).toContain("... no time specified ...");
  });

  it("should handle invalid dueDate", () => {
    const task = { id: 5, dueDate: "not-a-date" };

    const result = useTaskCardDate(task);

    expect(result.value).toBe("# 5 established ... no time specified ...");
  });
});
