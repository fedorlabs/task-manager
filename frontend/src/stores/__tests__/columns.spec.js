import { it, describe, beforeEach, expect } from "vitest";
import "@/stores/__tests__/mockServices";
// Due to the fact that vi.mock pops up to the very top of the file, it does not have access to global variables
// Therefore, it is important to import before using the main files
import { createPinia, setActivePinia } from "pinia";
import { useColumnsStore } from "@/stores";

describe("columns store", () => {
  let columnsStore;
  beforeEach(async () => {
    // Defining Pinia
    setActivePinia(createPinia());
    columnsStore = useColumnsStore();
    await columnsStore.fetchColumns();
  });
  it("should have initial columns", async () => {
    // The number of columns loaded should match the number in our columns.json file
    expect(columnsStore.columns.length).toBe(5);
  });
  it("should add a new column", async () => {
    // In this test, it doesn't matter what we send to the server, it's important what result mockStore will give us.js
    await columnsStore.addColumn({ title: "Новая колонка" });
    const columnsLength = columnsStore.columns.length;
    // Check that the number of columns has increased by one
    expect(columnsLength).toBe(6);
    // the id of the new column should be 6 (as we defined in the mockStore file.js)
    expect(columnsStore.columns[columnsLength - 1].id).toBe(6);
  });
  it("should update column", async () => {
    const newTitle = "Our new column title";
    await columnsStore.updateColumn({ id: 1, title: newTitle });
    // We check that we still have 6 elements (5 initially and one added in the previous test)
    expect(columnsStore.columns.length).toBe(5);
    // Check that the name of the first column has changed
    expect(columnsStore.columns[0].title).toBe(newTitle);
  });
  it("should delete column", async () => {
    await columnsStore.deleteColumn(5);
    // Check that the number has decreased by one column
    expect(columnsStore.columns.length).toBe(4);
    // Check that the column with id = 5 does not exist
    expect(
      columnsStore.columns.find((column) => column.id == 5)
    ).toBeUndefined();
  });
});
