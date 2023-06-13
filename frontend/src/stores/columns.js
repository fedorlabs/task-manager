import { defineStore } from "pinia";
import { columnsService } from "@/services";

export const useColumnsStore = defineStore("columns", {
  state: () => ({
    columns: [],
  }),
  getters: {},
  actions: {
    async fetchColumns() {
      this.columns = await columnsService.fetchColumns();
    },
    async addColumn() {
      // When creating a column, we add a default column, which we can change later
      const newColumn = await columnsService.createColumn({
        title: "Новый столбец",
      });
      this.columns.push(newColumn);
    },
    async updateColumn(column) {
      await columnsService.updateColumns(column);
      const index = this.columns.findIndex(({ id }) => id === column.id);
      if (~index) {
        this.columns.splice(index, 1, column);
      }
    },
    async deleteColumn(id) {
      await columnsService.deleteColumns(id);
      this.columns = this.columns.filter((column) => column.id !== id);
    },
  },
});
