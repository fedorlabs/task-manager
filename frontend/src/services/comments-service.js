import { HttpClient } from "./HttpClient";
import { getToken } from "./token-manager";
import httpProvider from "@/services/providers";

const BASE_URL = "/api/comments";

class CommentsService extends HttpClient {
  fetchComments() {
    return this.get("");
  }

  createComment(comment) {
    return this.post("", { data: comment });
  }
}

export default new CommentsService({
  httpProvider,
  baseURL: BASE_URL,
  getToken,
});
