import { it, describe, beforeEach, expect, vi } from "vitest";
import FetchProvider from "../FetchProvider";

describe("FetchProvider", () => {
  let provider;

  beforeEach(() => {
    provider = new FetchProvider();
    vi.restoreAllMocks();
  });

  describe("computeQueryParams", () => {
    it("should return empty string for falsy values", () => {
      expect(provider.computeQueryParams(null)).toBe("");
      expect(provider.computeQueryParams(undefined)).toBe("");
      expect(provider.computeQueryParams("")).toBe("");
    });

    it("should convert object to query string", () => {
      const result = provider.computeQueryParams({ name: "John", age: 30 });
      expect(result).toBe("?name=John&age=30");
    });
  });

  describe("request", () => {
    it("should return parsed JSON for 200 response", async () => {
      global.fetch = vi.fn(() =>
        Promise.resolve({
          ok: true,
          status: 200,
          json: () => Promise.resolve({ data: "test" }),
        }),
      );

      const result = await provider.request({
        baseUrl: "http://api.test",
        path: "/users",
        method: "GET",
        headers: {},
      });

      expect(result).toEqual({ data: "test" });
    });

    it("should return raw response for 204 status", async () => {
      const mockResponse = {
        ok: true,
        status: 204,
      };
      global.fetch = vi.fn(() => Promise.resolve(mockResponse));

      const result = await provider.request({
        baseUrl: "http://api.test",
        path: "/users",
        method: "DELETE",
        headers: {},
      });

      expect(result).toBe(mockResponse);
    });

    it("should throw error with message from API response", async () => {
      global.fetch = vi.fn(() =>
        Promise.resolve({
          ok: false,
          status: 400,
          json: () =>
            Promise.resolve({
              error: { message: "Bad request", statusCode: 400 },
            }),
        }),
      );

      await expect(
        provider.request({
          baseUrl: "http://api.test",
          path: "/users",
          method: "POST",
          headers: {},
        }),
      ).rejects.toThrow("Bad request");
    });

    it("should throw error with statusText when no JSON body", async () => {
      global.fetch = vi.fn(() =>
        Promise.resolve({
          ok: false,
          status: 500,
          statusText: "Internal Server Error",
          json: () => Promise.reject(new Error("Invalid JSON")),
        }),
      );

      await expect(
        provider.request({
          baseUrl: "http://api.test",
          path: "/users",
          method: "GET",
          headers: {},
        }),
      ).rejects.toThrow("Internal Server Error");
    });

    it("should call interceptors on error", async () => {
      const interceptor = { onError: vi.fn() };
      provider.addInterceptor(interceptor);

      global.fetch = vi.fn(() =>
        Promise.resolve({
          ok: false,
          status: 401,
          json: () =>
            Promise.resolve({
              error: { message: "Unauthorized", statusCode: 401 },
            }),
        }),
      );

      await expect(
        provider.request({
          baseUrl: "http://api.test",
          path: "/users",
          method: "GET",
          headers: {},
        }),
      ).rejects.toThrow();

      expect(interceptor.onError).toHaveBeenCalledWith(401, "Unauthorized");
    });

    it("should throw network error for fetch failures", async () => {
      global.fetch = vi.fn(() => Promise.reject(new Error("Network failed")));

      await expect(
        provider.request({
          baseUrl: "http://api.test",
          path: "/users",
          method: "GET",
          headers: {},
        }),
      ).rejects.toThrow("Network failed");
    });
  });

  describe("addInterceptor", () => {
    it("should add valid interceptor", () => {
      const interceptor = { onError: vi.fn() };
      const result = provider.addInterceptor(interceptor);

      expect(result).toBe(provider); // Chainable
      expect(provider.interceptors).toContain(interceptor);
    });

    it("should throw for invalid interceptor", () => {
      expect(() => provider.addInterceptor({})).toThrow(
        "Interceptor is not supported",
      );
    });
  });

  describe("HTTP methods", () => {
    beforeEach(() => {
      global.fetch = vi.fn(() =>
        Promise.resolve({
          ok: true,
          status: 200,
          json: () => Promise.resolve({}),
        }),
      );
    });

    it("get should use GET method", async () => {
      await provider.get("/users", {
        baseUrl: "http://api.test",
        headers: {},
      });
      expect(global.fetch).toHaveBeenCalled();
      const [, init] = global.fetch.mock.calls[0];
      expect(init.method).toBe("GET");
    });

    it("post should use POST method", async () => {
      await provider.post("/users", {
        baseUrl: "http://api.test",
        headers: {},
      });
      const [, init] = global.fetch.mock.calls[0];
      expect(init.method).toBe("POST");
    });

    it("put should use PUT method", async () => {
      await provider.put("/users", {
        baseUrl: "http://api.test",
        headers: {},
      });
      const [, init] = global.fetch.mock.calls[0];
      expect(init.method).toBe("PUT");
    });

    it("delete should use DELETE method", async () => {
      await provider.delete("/users", {
        baseUrl: "http://api.test",
        headers: {},
      });
      const [, init] = global.fetch.mock.calls[0];
      expect(init.method).toBe("DELETE");
    });
  });
});
