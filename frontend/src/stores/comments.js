import { defineStore } from "pinia";
import { commentsService } from "@/services";
import { useUsersStore } from "@/stores";

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
      this.loading = true;
      this.error = null;
      try {
        this.comments = await commentsService.fetchComments();
      } catch (e) {
        this.error = e.message;
      } finally {
        this.loading = false;
      }
    },

    async addComment(comment) {
      const newComment = await commentsService.createComment(comment);
      this.comments.push(newComment);
      return newComment;
    },
  },
});
