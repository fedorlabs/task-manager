import { defineStore } from "pinia";

export const useFiltersStore = defineStore("filters", {
  state: () => ({
    search: "",
    users: [],
    statuses: [],
  }),

  getters: {
    filters: (state) => ({
      search: state.search,
      users: state.users,
      statuses: state.statuses,
    }),
  },

  actions: {
    applyFilters({ item, entity }) {
      if (entity === "search") {
        this.search = item;
        return;
      }

      const current = [...this[entity]];
      const itemIndex = current.indexOf(item);

      if (itemIndex !== -1) {
        current.splice(itemIndex, 1);
      } else {
        current.push(item);
      }

      this.$patch({ [entity]: current });
    },

    resetFilters() {
      this.search = "";
      this.users = [];
      this.statuses = [];
    },
  },
});
