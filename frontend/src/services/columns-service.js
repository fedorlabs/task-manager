import { HttpClient } from "./HttpClient";
import { getToken } from "./token-manager";
import httpProvider from "@/services/providers";

const BASE_URL = "/api/columns";

class ColumnsService extends HttpClient {
  fetchColumns() {
    return this.get("");
  }

  createColumn(column) {
    return this.post("", { data: column });
  }

  updateColumns(column) {
    return this.put(`/${column.id}`, { data: column });
  }

  deleteColumns(id) {
    return this.delete(`/${id}`);
  }
}

export default new ColumnsService({
  httpProvider,
  baseURL: BASE_URL,
  getToken,
});
