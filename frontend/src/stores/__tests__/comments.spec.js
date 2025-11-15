import { it, describe, beforeEach, expect, vi } from "vitest";
import { createPinia, setActivePinia } from "pinia";

vi.mock("@/services", () => ({
  commentsService: {
    fetchComments: vi.fn(),
    createComment: vi.fn(),
  },
  userService: {
    fetchUsers: vi.fn(),
  },
}));

import { useCommentsStore, useUsersStore } from "@/stores";
import { commentsService } from "@/services";

describe("comments store", () => {
  let commentsStore;

  beforeEach(() => {
    vi.clearAllMocks();
    setActivePinia(createPinia());
    commentsStore = useCommentsStore();
  });

  it("should have initial state", () => {
    expect(commentsStore.comments).toEqual([]);
    expect(commentsStore.loading).toBe(false);
    expect(commentsStore.error).toBeNull();
  });

  describe("fetchComments", () => {
    it("should fetch comments successfully", async () => {
      const mockComments = [
        { id: 1, text: "Comment 1", taskId: 1, userId: "u1" },
        { id: 2, text: "Comment 2", taskId: 1, userId: "u2" },
      ];
      commentsService.fetchComments.mockResolvedValue(mockComments);

      await commentsStore.fetchComments();

      expect(commentsStore.comments).toEqual(mockComments);
      expect(commentsStore.loading).toBe(false);
      expect(commentsStore.error).toBeNull();
    });

    it("should set loading during fetch", async () => {
      let resolveFn;
      commentsService.fetchComments.mockReturnValue(
        new Promise((resolve) => {
          resolveFn = resolve;
        }),
      );

      const promise = commentsStore.fetchComments();
      expect(commentsStore.loading).toBe(true);

      resolveFn([]);
      await promise;
      expect(commentsStore.loading).toBe(false);
    });

    it("should handle fetch error", async () => {
      commentsService.fetchComments.mockRejectedValue(
        new Error("Failed to load"),
      );

      await commentsStore.fetchComments();

      expect(commentsStore.comments).toEqual([]);
      expect(commentsStore.error).toBe("Failed to load");
      expect(commentsStore.loading).toBe(false);
    });
  });

  describe("addComment", () => {
    it("should add comment and return it", async () => {
      const newComment = { id: 3, text: "New comment", taskId: 1 };
      commentsService.createComment.mockResolvedValue(newComment);

      const result = await commentsStore.addComment({
        text: "New comment",
        taskId: 1,
      });

      expect(result).toEqual(newComment);
      expect(commentsStore.comments).toContainEqual(newComment);
    });

    it("should append to existing comments", async () => {
      commentsStore.comments = [{ id: 1, text: "Existing" }];
      const newComment = { id: 2, text: "Added" };
      commentsService.createComment.mockResolvedValue(newComment);

      await commentsStore.addComment({ text: "Added" });

      expect(commentsStore.comments).toHaveLength(2);
    });
  });

  describe("getCommentsByTaskId", () => {
    it("should return comments for a specific task", () => {
      commentsStore.comments = [
        { id: 1, text: "Comment A", taskId: 1, userId: "u1" },
        { id: 2, text: "Comment B", taskId: 2, userId: "u1" },
        { id: 3, text: "Comment C", taskId: 1, userId: "u2" },
      ];

      const result = commentsStore.getCommentsByTaskId(1);

      expect(result).toHaveLength(2);
      expect(result[0].text).toBe("Comment A");
      expect(result[1].text).toBe("Comment C");
    });

    it("should attach user data to comments", () => {
      commentsStore.comments = [
        { id: 1, text: "Comment", taskId: 1, userId: "u1" },
      ];

      const usersStore = useUsersStore();
      usersStore.users = [{ id: "u1", name: "Alice" }];

      const result = commentsStore.getCommentsByTaskId(1);

      expect(result[0].user).toEqual({ id: "u1", name: "Alice" });
    });

    it("should set user to null when user not found", () => {
      commentsStore.comments = [
        { id: 1, text: "Comment", taskId: 1, userId: "unknown" },
      ];

      const result = commentsStore.getCommentsByTaskId(1);

      expect(result[0].user).toBeNull();
    });

    it("should return empty array when no comments for task", () => {
      commentsStore.comments = [
        { id: 1, text: "Other task", taskId: 5, userId: "u1" },
      ];

      expect(commentsStore.getCommentsByTaskId(99)).toEqual([]);
    });
  });
});
