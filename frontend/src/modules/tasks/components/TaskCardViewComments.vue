<template>
  <div class="task-card__comments">
    <h2 class="task-card__title">Comments</h2>
    <div class="comments">
      <!--      List of comments-->
      <ul class="comments__list">
        <li
          v-for="comment in comments"
          :key="comment.id"
          class="comments__item"
        >
          <div class="comments__user">
            <img
              :src="getPublicImage(comment.user.avatar)"
              :alt="comment.user.name"
              width="30"
              height="30"
            />
            {{ comment.user.name }}
          </div>
          <p>{{ comment.text }}</p>
        </li>
      </ul>

      <!--      Block for adding a new comment-->
      <form v-if="user" action="#" class="comments__form" method="post">
        <app-textarea
          v-model="newComment"
          name="comment_text"
          placeholder="Enter the text of the comment"
          :error-text="validations.newComment.error"
        />
        <app-button
          class="comments__form__button"
          :type="'submit'"
          @click.prevent="submit"
        >
          Write a comment
        </app-button>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch } from "vue";
import { validateFields, clearValidationErrors } from "@/common/validator";
import AppTextarea from "@/common/components/AppTextarea.vue";
import AppButton from "@/common/components/AppButton.vue";
import { getPublicImage } from "@/common/helpers";
import { useAuthStore, useCommentsStore } from "@/stores";

const authStore = useAuthStore();
const commentsStore = useCommentsStore();

const props = defineProps({
  taskId: {
    type: Number,
    required: true,
  },
});

const emits = defineEmits(["createNewComment"]);

const newComment = ref("");
const validations = ref({
  newComment: {
    error: "",
    rules: ["required"],
  },
});

const user = authStore.user;
const comments = computed(() => {
  return commentsStore.getCommentsByTaskId(props.taskId);
});

// Track the value of the comment field and clear the error when changing
watch(newComment, () => {
  if (validations.value.newComment.error) {
    clearValidationErrors(validations.value);
  }
});

const submit = async function () {
  // Check if the comment field is valid
  if (!validateFields({ newComment }, validations.value)) return;
  // Create a comment object
  const comment = {
    text: newComment.value,
    taskId: props.taskId,
    userId: user.id,
  };
  // Creating a comment
  await commentsStore.addComment(comment);
  // Clear the comment field
  newComment.value = "";
};
</script>

<style lang="scss" scoped>
@import "@/assets/scss/app.scss";
.comments {
  &__list {
    @include clear-list;
  }

  &__item {
    margin-top: 24px;

    p {
      @include r-s14-h21;
    }

    strong {
      color: $blue-600;

      @include r-s14-h21;
    }
  }

  &__user {
    display: flex;
    align-items: center;

    width: 100%;
    margin: 0;
    padding: 0;

    text-align: left;

    background-color: transparent;

    @include r-s14-h16;

    img {
      width: 30px;
      height: 30px;
      margin-right: 10px;

      border-radius: 50%;
    }

    span {
      display: block;

      box-sizing: border-box;
      width: 30px;
      height: 30px;
      margin-right: 10px;
      padding-top: 5px;

      text-align: center;

      color: $white-900;
      border-radius: 50%;
      background-color: $green-700;

      @include m-s14-h21;
    }
  }

  &__form {
    margin-top: 24px;

    &__button {
      display: block;

      margin: 0;
      margin-top: 15px;
      margin-left: auto;
      padding: 0;

      cursor: pointer;
      transition: opacity $animationSpeed;

      opacity: 0.5;
      color: $blue-gray-600;
      border: none;
      outline: none;
      background-color: transparent;

      @include m-s14-h21;

      &:hover {
        opacity: 1;
      }
    }
  }
}
</style>
