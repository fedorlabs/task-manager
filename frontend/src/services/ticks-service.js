import { HttpClient } from "./HttpClient";
import { getToken } from "./token-manager";
import httpProvider from "@/services/providers";

const BASE_URL = "/api/ticks";

class TicksService extends HttpClient {
  fetchTicks() {
    return this.get("");
  }

  createTick(tick) {
    return this.post("", { data: tick });
  }

  updateTick(tick) {
    return this.put(`/${tick.id}`, { data: tick });
  }

  deleteTick(id) {
    return this.delete(`/${id}`);
  }
}

export default new TicksService({
  httpProvider,
  baseURL: BASE_URL,
  getToken,
});
