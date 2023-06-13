import { computed } from "vue";
import { getTimeAgo } from "./helpers";

export const useTaskCardDate = (task) => {
  return computed(() => {
    return `# ${task.id} established ${getTimeAgo(task.dueDate)}`;
  });
};
