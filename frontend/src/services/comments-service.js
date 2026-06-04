import { HttpClient } from "./HttpClient";
import { getToken } from "./token-manager";
import httpProvider from "@/services/providers";

const BASE_URL = "/api/comments";

class CommentsService extends HttpClient {
  normalizeComment(comment) {
    return {
      ...comment,
      taskId: comment.task?.id ?? null,
      userId: comment.user?.id ?? null,
    };
  }

  async fetchComments() {
    const comments = await this.get("");
    return comments.map((comment) => this.normalizeComment(comment));
  }

  async createComment(comment) {
    const newComment = await this.post("", { data: comment });
    return this.normalizeComment(newComment);
  }
}

export default new CommentsService({
  httpProvider,
  baseURL: BASE_URL,
  getToken,
});
