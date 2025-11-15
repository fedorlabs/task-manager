import { it, describe, expect, vi, beforeEach, afterEach } from "vitest";
import { clickOutside } from "@/common/directives";

describe("clickOutside directive", () => {
  let el;
  let binding;
  let handler;

  beforeEach(() => {
    el = document.createElement("div");
    document.body.appendChild(el);
    handler = vi.fn();
    binding = { value: handler };
  });

  afterEach(() => {
    clickOutside.unmounted(el);
    el.remove();
  });

  it("should add click event listener on mount", () => {
    const spy = vi.spyOn(document.body, "addEventListener");

    clickOutside.mounted(el, binding);

    expect(spy).toHaveBeenCalledWith("click", expect.any(Function));
    spy.mockRestore();
  });

  it("should remove click event listener on unmount", () => {
    clickOutside.mounted(el, binding);
    const spy = vi.spyOn(document.body, "removeEventListener");

    clickOutside.unmounted(el);

    expect(spy).toHaveBeenCalledWith("click", el.clickOutsideEvent);
    spy.mockRestore();
  });

  it("should call handler when clicking outside element", () => {
    clickOutside.mounted(el, binding);

    const outsideEl = document.createElement("span");
    document.body.appendChild(outsideEl);

    const event = new Event("click", { bubbles: true });
    Object.defineProperty(event, "target", { value: outsideEl });
    el.clickOutsideEvent(event);

    expect(handler).toHaveBeenCalledWith(event, el);
    outsideEl.remove();
  });

  it("should not call handler when clicking on element itself", () => {
    clickOutside.mounted(el, binding);

    const event = new Event("click", { bubbles: true });
    Object.defineProperty(event, "target", { value: el });
    el.clickOutsideEvent(event);

    expect(handler).not.toHaveBeenCalled();
  });

  it("should not call handler when clicking on child element", () => {
    const child = document.createElement("span");
    el.appendChild(child);
    clickOutside.mounted(el, binding);

    const event = new Event("click", { bubbles: true });
    Object.defineProperty(event, "target", { value: child });
    el.clickOutsideEvent(event);

    expect(handler).not.toHaveBeenCalled();
  });
});
