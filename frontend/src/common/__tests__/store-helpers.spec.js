import { it, describe, beforeEach, expect } from "vitest";
import { withLoading } from "@/common/store-helpers";

describe("withLoading", () => {
  let store;

  beforeEach(() => {
    store = {
      loading: false,
      error: null,
    };
  });

  it("should set loading to true before action", async () => {
    let loadingDuringAction;

    await withLoading(store, async () => {
      loadingDuringAction = store.loading;
    });

    expect(loadingDuringAction).toBe(true);
  });

  it("should set loading to false after successful action", async () => {
    await withLoading(store, async () => "result");

    expect(store.loading).toBe(false);
  });

  it("should return action result on success", async () => {
    const result = await withLoading(store, async () => "success");

    expect(result).toBe("success");
  });

  it("should clear error before action", async () => {
    store.error = "previous error";

    await withLoading(store, async () => "ok");

    expect(store.error).toBeNull();
  });

  it("should set error message on failure and re-throw", async () => {
    await expect(
      withLoading(store, async () => {
        throw new Error("Something went wrong");
      }),
    ).rejects.toThrow("Something went wrong");

    expect(store.error).toBe("Something went wrong");
  });

  it("should set loading to false even on failure", async () => {
    await expect(
      withLoading(store, async () => {
        throw new Error("Failure");
      }),
    ).rejects.toThrow("Failure");

    expect(store.loading).toBe(false);
  });

  it("should handle errors without message and re-throw", async () => {
    await expect(
      withLoading(store, async () => {
        throw {}; // Error without message
      }),
    ).rejects.toThrow();

    expect(store.error).toBe("Unknown error");
    expect(store.loading).toBe(false);
  });

  it("should handle synchronous exceptions and re-throw", async () => {
    await expect(
      withLoading(store, () => {
        throw new Error("Sync error");
      }),
    ).rejects.toThrow("Sync error");

    expect(store.error).toBe("Sync error");
    expect(store.loading).toBe(false);
  });
});
