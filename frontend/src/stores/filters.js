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

      const next =
        itemIndex !== -1
          ? [...current.slice(0, itemIndex), ...current.slice(itemIndex + 1)]
          : [...current, item];

      this.$patch({ [entity]: next });
    },

    resetFilters() {
      this.search = "";
      this.users = [];
      this.statuses = [];
    },
  },
});
