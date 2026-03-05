import { defineStore } from "pinia";
import { columnsService } from "@/services";
import { withLoading } from "@/common/store-helpers";

export const useColumnsStore = defineStore("columns", {
  state: () => ({
    columns: [],
    loading: false,
    error: null,
  }),

  getters: {},

  actions: {
    async fetchColumns() {
      return withLoading(this, async () => {
        this.columns = await columnsService.fetchColumns();
      });
    },

    async addColumn() {
      const newColumn = await columnsService.createColumn({
        title: "New column",
      });
      this.columns = [...this.columns, newColumn];
    },

    async updateColumn(column) {
      await columnsService.updateColumns(column);
      const index = this.columns.findIndex(({ id }) => id === column.id);
      if (index !== -1) {
        this.columns = [
          ...this.columns.slice(0, index),
          column,
          ...this.columns.slice(index + 1),
        ];
      }
    },

    async deleteColumn(id) {
      await columnsService.deleteColumns(id);
      this.columns = this.columns.filter((column) => column.id !== id);
    },
  },
});
