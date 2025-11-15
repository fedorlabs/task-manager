import { it, describe, expect } from "vitest";
import { validateFields, clearValidationErrors } from "../validator";

describe("validator", () => {
  describe("validateFields as single-field validate proxy", () => {
    it("should return true for valid required field", () => {
      const v = { field: { error: "", rules: ["required"] } };
      expect(validateFields({ field: "hello" }, v)).toBe(true);
      expect(v.field.error).toBe("");
    });

    it("should return false for empty required field", () => {
      const v = { field: { error: "", rules: ["required"] } };
      expect(validateFields({ field: "" }, v)).toBe(false);
      expect(v.field.error).toBeTruthy();
    });

    it("should return false for whitespace-only required field", () => {
      const v = { field: { error: "", rules: ["required"] } };
      expect(validateFields({ field: "   " }, v)).toBe(false);
      expect(v.field.error).toBeTruthy();
    });

    it("should return true for valid email", () => {
      const v = { email: { error: "", rules: ["email"] } };
      expect(validateFields({ email: "test@example.com" }, v)).toBe(true);
      expect(v.email.error).toBe("");
    });

    it("should return false for invalid email", () => {
      const v = { email: { error: "", rules: ["email"] } };
      expect(validateFields({ email: "not-an-email" }, v)).toBe(false);
      expect(v.email.error).toBeTruthy();
    });

    it("should return true for valid url", () => {
      const v = { url: { error: "", rules: ["url"] } };
      expect(validateFields({ url: "https://example.com" }, v)).toBe(true);
      expect(v.url.error).toBe("");
    });

    it("should return false for invalid url", () => {
      const v = { url: { error: "", rules: ["url"] } };
      expect(validateFields({ url: "not-a-url" }, v)).toBe(false);
      expect(v.url.error).toBeTruthy();
    });
  });

  describe("validateFields", () => {
    it("should validate multiple fields and return true when valid", () => {
      const validations = {
        title: { error: "", rules: ["required"] },
        email: { error: "", rules: ["email"] },
      };

      const result = validateFields(
        { title: "Test", email: "test@test.com" },
        validations,
      );

      expect(result).toBe(true);
    });

    it("should return false when validation fails", () => {
      const validations = {
        title: { error: "", rules: ["required"] },
      };

      const result = validateFields({ title: "" }, validations);

      expect(result).toBe(false);
      expect(validations.title.error).toBeTruthy();
    });
  });

  describe("clearValidationErrors", () => {
    it("should clear all validation errors", () => {
      const validations = {
        title: { error: "Required", rules: ["required"] },
        email: { error: "Invalid email", rules: ["email"] },
      };

      clearValidationErrors(validations);

      expect(validations.title.error).toBe("");
      expect(validations.email.error).toBe("");
    });
  });
});
