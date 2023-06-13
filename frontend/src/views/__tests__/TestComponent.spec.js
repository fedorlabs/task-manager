import { mount } from "@vue/test-utils";
import "@/stores/__tests__/mockServices";
import { describe, beforeEach, it, expect, vi } from "vitest";
import TestComponent from "../TestComponent.vue";
import { createTestingPinia } from "@pinia/testing";
import { useColumnsStore, useTasksStore, useUsersStore } from "@/stores";
import router from "@/router";

describe("TestComponent", () => {
  let wrapper, usersStore, tasksStore, columnsStore;
  beforeEach(async () => {
    // Create an instance of Pinia
    const pinia = createTestingPinia({
      createSpy: vi.fn,
      stubActions: false,
    });
    // Create an instance of the component
    wrapper = mount(TestComponent, {
      global: {
        plugins: [pinia, router], // add Pinia and vue-router to the component
      },
    });
    // Create storage instances
    usersStore = useUsersStore();
    tasksStore = useTasksStore();
    columnsStore = useColumnsStore();
    // We make initial requests to fill the storages
    await usersStore.fetchUsers();
    await tasksStore.fetchTasks();
    await columnsStore.fetchColumns();
  });
  it("should render", async () => {
    expect(wrapper.exists).toBeTruthy();
  });
  it("should have a proper title", () => {
    const deskTitle = wrapper.find('[data-test="desk-title"]');
    expect(deskTitle.text()).toBe("Design Coffee Lab");
  });
  it("should render add column button", () => {
    const addColumnButton = wrapper.find('[data-test="desk-add"]');
    expect(addColumnButton.text()).toBe("Add column");
  });
  it("should trigger addColumn action", async () => {
    const addColumnButton = wrapper.find('[data-test="desk-add"]');
    addColumnButton.trigger("click");
    expect(columnsStore.addColumn).toHaveBeenCalledTimes(1);
  });
  it("should have user filter", () => {
    const users = wrapper.findAll('[data-test="user-filter"]');
    expect(users.length).toBe(8);
  });
  it("should have statuses filter", () => {
    const statuses = wrapper.findAll('[data-test="status-filter"]');
    expect(statuses.length).toBe(5);
  });
  it("should should have initial columns", () => {
    const columns = wrapper.findAll('[data-test="desk-column-title"]');
    expect(columns.length).toBe(5);
    expect(columns[0].text()).toBe("Scheduled");
    expect(columns[1].text()).toBe("In work");
    expect(columns[2].text()).toBe("Under review");
    expect(columns[3].text()).toBe("Done");
    expect(columns[4].text()).toBe("For removal");
  });
  it("should have tasks in the first column", () => {
    const firstColumn = wrapper.find('[data-test="column-target-area"]');
    const taskTitles = firstColumn.findAll('[data-test="task-title"]');
    expect(taskTitles.length).toBe(5);
    expect(taskTitles[0].text()).toBe("Task № 2");
    expect(taskTitles[1].text()).toBe("Task № 6");
    expect(taskTitles[2].text()).toBe("Task № 10");
    expect(taskTitles[3].text()).toBe("Task № 14");
    expect(taskTitles[4].text()).toBe("Task № 18");
  });
});
