import { defineStore } from "pinia";
import { commentsService } from "@/services";
import { useUsersStore } from "@/stores";
import { withLoading } from "@/common/store-helpers";

export const useCommentsStore = defineStore("comments", {
  state: () => ({
    comments: [],
    loading: false,
    error: null,
  }),

  getters: {
    getCommentsByTaskId: (state) => (taskId) => {
      const usersStore = useUsersStore();
      return state.comments
        .filter((comment) => comment.taskId === taskId)
        .map((comment) => ({
          ...comment,
          user:
            usersStore.users.find((user) => comment.userId === user.id) ?? null,
        }));
    },
  },

  actions: {
    async fetchComments() {
      return withLoading(this, async () => {
        this.comments = await commentsService.fetchComments();
      });
    },

    async addComment(comment) {
      const newComment = await commentsService.createComment(comment);
      this.comments = [...this.comments, newComment];
      return newComment;
    },
  },
});
