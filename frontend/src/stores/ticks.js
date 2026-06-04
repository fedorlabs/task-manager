import { defineStore } from "pinia";
import { ticksService } from "@/services";
import { withLoading } from "@/common/store-helpers";

export const useTicksStore = defineStore("ticks", {
  state: () => ({
    ticks: [],
    loading: false,
    error: null,
  }),

  getters: {
    getTicksByTaskId: (state) => (taskId) =>
      state.ticks.filter((tick) => tick.taskId === taskId),
  },

  actions: {
    async fetchTicks() {
      return withLoading(this, async () => {
        this.ticks = await ticksService.fetchTicks();
      });
    },

    async addTick(tick) {
      const newTick = await ticksService.createTick(tick);
      this.ticks = [...this.ticks, newTick];
      return newTick;
    },

    async updateTick(tick) {
      await ticksService.updateTick(tick);
      const index = this.ticks.findIndex(({ id }) => id === tick.id);
      if (index !== -1) {
        this.ticks = [
          ...this.ticks.slice(0, index),
          tick,
          ...this.ticks.slice(index + 1),
        ];
      }
    },

    async deleteTick(id) {
      await ticksService.deleteTick(id);
      this.ticks = this.ticks.filter((tick) => tick.id !== id);
    },
  },
});
