import { defineStore } from "pinia";
import { columnsService } from "@/services";

export const useColumnsStore = defineStore("columns", {
  state: () => ({
    columns: [],
    loading: false,
    error: null,
  }),

  getters: {},

  actions: {
    async fetchColumns() {
      this.loading = true;
      this.error = null;
      try {
        this.columns = await columnsService.fetchColumns();
      } catch (e) {
        this.error = e.message;
      } finally {
        this.loading = false;
      }
    },

    async addColumn() {
      const newColumn = await columnsService.createColumn({
        title: "New column",
      });
      this.columns.push(newColumn);
    },

    async updateColumn(column) {
      await columnsService.updateColumns(column);
      const index = this.columns.findIndex(({ id }) => id === column.id);
      if (index !== -1) {
        this.columns.splice(index, 1, column);
      }
    },

    async deleteColumn(id) {
      await columnsService.deleteColumns(id);
      this.columns = this.columns.filter((column) => column.id !== id);
    },
  },
});
