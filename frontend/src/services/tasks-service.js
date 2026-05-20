import { HttpClient } from "./HttpClient";
import { getToken } from "./token-manager";
import httpProvider from "@/services/providers";
import taskStatuses from "@/common/enums/taskStatuses";
import { getTimeStatus } from "@/common/helpers";

const BASE_URL = "/api/tasks";

class TasksService extends HttpClient {
  createRequest(task) {
    // Removing unnecessary frontend-only parameters from the task
    // eslint-disable-next-line no-unused-vars
    const { ticks, comments, status, timeStatus, user, ...request } = task;
    return request;
  }

  normalizeTask(task) {
    return {
      ...task,
      ticks: task.ticks || [],
      dueDate: task.dueDate ? new Date(task.dueDate) : null,
      status: task.statusId ? taskStatuses[task.statusId] : "",
      timeStatus: getTimeStatus(task.dueDate),
      columnId: task.column?.id ?? null,
    };
  }

  async fetchTasks() {
    const tasks = await this.get("");
    return tasks.map((task) => this.normalizeTask(task));
  }

  async createTask(task) {
    const newTask = await this.post("", { data: this.createRequest(task) });
    return this.normalizeTask(newTask);
  }

  async updateTask(task) {
    await this.put(`/${task.id}`, { data: this.createRequest(task) });
    return this.normalizeTask(task);
  }

  async deleteTask(id) {
    await this.delete(`/${id}`);
  }
}

export default new TasksService({
  httpProvider,
  baseURL: BASE_URL,
  getToken,
});
