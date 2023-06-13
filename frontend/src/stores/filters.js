import { defineStore } from "pinia";

export const useFiltersStore = defineStore("filters", {
  state: () => ({
    search: "",
    users: [],
    statuses: [],
  }),
  getters: {
    filters: (state) => {
      const { search, users, statuses } = state;
      return {
        search,
        users,
        statuses,
      };
    },
  },
  actions: {
    applyFilters({ item, entity }) {
      if (entity === "search") {
        this.search = item;
      } else {
        // select if isset
        const resultValues = [...this[entity]];
        // if we find an already added ID, then delete it, or add it to the array
        const itemIndex = resultValues.findIndex((el) => el === item);
        ~itemIndex
          ? resultValues.splice(itemIndex, 1)
          : resultValues.push(item);
        // update state (new or exist entity and array values)
        // to access a property of an object with a name stored in a variable, square brackets and the variable inside them are used.
        this.$patch({ [entity]: resultValues });
      }
    },
  },
});
