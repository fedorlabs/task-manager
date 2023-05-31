<template>
  <div
    ref="dialog"
    class="task-card"
    tabindex="0"
    @click.self="closeDialog"
    @keydown.esc="closeDialog"
  >
    <section class="task-card__wrapper">
      <!--      Button to close the task dialog-->
      <button class="task-card__close" type="button" @click="closeDialog" />

      <!--      Block for entering a name and deleting a task-->
      <div class="task-card__block">
        <div class="task-card__row">
          <!--          Task name input field-->
          <input
            v-model="task.title"
            type="text"
            name="task_name"
            class="task-card__name"
            max="37"
          />
          <!--          Delete task button-->
          <a
            v-if="taskToEdit"
            class="task-card__edit task-card__edit--red"
            @click="deleteTask"
          >
            Delete a task
          </a>
        </div>
        <!--        Name input field validation error -->
        <span v-if="validations.title.error" class="task-card__error-text">
          {{ validations.title.error }}
        </span>
      </div>

      <!--      Task status block-->
      <div class="task-card__status">
        <h4 class="task-card__title">Select a status:</h4>

        <!--        List of task statuses-->
        <ul class="meta-filter task-card__meta">
          <li
            v-for="{ value, label } in statusList"
            :key="value"
            class="meta-filter__item"
            :class="{ active: value === taskStatuses[task.statusId] }"
            @click="setStatus(value)"
          >
            <a
              class="meta-filter__status"
              :class="`meta-filter__status--${value}`"
              :title="label"
            />
          </li>
        </ul>
      </div>

      <!--      Task due date block-->
      <div v-if="task.id" class="task-card__block">
        <p class="task-card__date">
          {{ useTaskCardDate(task) }}
        </p>
      </div>

      <!--      User Input and Due Date Block-->
      <div class="task-card__block">
        <ul class="task-card__params">
          <!--          Task User Selection Block-->
          <tasks-card-creator-user-selector v-model="task.userId" />
          <!--          Due Date Selection Block-->
          <tasks-card-creator-due-date-selector v-model="task.dueDate" />
        </ul>
      </div>

      <!--      Task description block-->
      <div class="task-card__block">
        <div class="task-card__description">
          <h4 class="task-card__title">Description</h4>
          <textarea
            v-model="task.description"
            name="task_description"
            placeholder="Add a description to the task"
          />
        </div>
      </div>

      <!--      External Link Block-->
      <div class="task-card__block">
        <div class="task-card__links">
          <h4 class="task-card__title">Links</h4>

          <div class="task-card__links-item">
            <!--            Link input field-->
            <input
              v-model="task.url"
              type="text"
              name="task_link_url"
              placeholder="Enter url"
            />
            <!--            Error validating the link input field-->
            <span v-if="validations.url.error" class="task-card__error-text">
              {{ validations.url.error }}
            </span>
            <!--            Link description-->
            <input
              v-model="task.urlDescription"
              type="text"
              name="task_link_desc"
              placeholder="Add a description to the link"
            />
          </div>
        </div>
      </div>

      <!--      Block of subtasks-->
      <div class="task-card__block">
        <!--        List of subtasks-->
        <task-card-view-ticks-list
          :ticks="task.ticks"
          @create-tick="createTick"
          @update-tick="updateTick"
          @remove-tick="removeTick"
        />
      </div>

      <!--      Tag block-->
      <div class="task-card__block">
        <!--        Tagging component-->
        <task-card-creator-tags :tags="task.tags" @set-tags="setTags" />
      </div>

      <!--      Block for saving and discarding changes-->
      <div class="task-card__buttons">
        <!--        Undo button to undo changes-->
        <app-button class="button--border" @click="closeDialog">
          Cancel
        </app-button>
        <!--        Save changes button-->
        <app-button
          class="button--primary"
          :class="{ 'button--disabled': !isFormValid }"
          :disabled="!isFormValid"
          @click="submit"
        >
          Save
        </app-button>
      </div>
    </section>
  </div>
</template>

<script setup>
import { ref, onMounted, watch } from "vue";
import TasksCardCreatorUserSelector from "./TaskCardCreatorUserSelector.vue";
import TasksCardCreatorDueDateSelector from "./TaskCardCreatorDueDateSelector.vue";
import TaskCardViewTicksList from "./TaskCardViewTicksList.vue";
import TaskCardCreatorTags from "./TaskCardCreatorTags.vue";
import AppButton from "@/common/components/AppButton.vue";
import { useRouter } from "vue-router";
import { createUUIDv4, createNewDate } from "@/common/helpers";
import { STATUSES } from "@/common/constants";
import taskStatuses from "@/common/enums/taskStatuses";
import { validateFields } from "@/common/validator";
import { useTaskCardDate } from "@/common/composables";
import { useTasksStore, useTicksStore } from "@/stores";

const props = defineProps({
  taskToEdit: {
    type: Object,
    default: null,
  },
});

// Function for creating new tasks
const createNewTask = () => ({
  userId: null,
  columnId: null,
  statusId: null,
  title: "Task name",
  description: "",
  sortOrder: 0,
  dueDate: createNewDate(),
  url: "",
  urlDescription: "",
  ticks: [],
  tags: "",
});

const createNewTick = () => ({
  // Add a temporary identifier until it is sent to the server
  uuid: createUUIDv4(),
  taskId: null,
  text: "",
  done: false,
});

const setEmptyValidations = () => ({
  title: {
    error: "",
    rules: ["required"],
  },
  url: {
    error: "",
    rules: ["url"],
  },
});

const router = useRouter();

const tasksStore = useTasksStore();
const ticksStore = useTicksStore();

// Determine if we are working on editing a task or creating a new one
const taskToWork = props.taskToEdit
  ? structuredClone(props.taskToEdit)
  : createNewTask();

const task = ref(taskToWork);
const validations = ref(setEmptyValidations());
const isFormValid = ref(true);
const statusList = ref(STATUSES.slice(0, 3));
const dialog = ref(null);

// Tracking changes in the task to reset validation errors
watch(
  task,
  () => {
    isFormValid.value = true;
    validations.value = setEmptyValidations();
  },
  { deep: true },
);

onMounted(() => {
  // Focus on the dialog box so that the esc key is triggered without an additional click on the window
  dialog.value.focus();
});

function closeDialog() {
  // Closing the dialog is just a transition to the root route
  router.push("/");
}

function deleteTask() {
  tasksStore.deleteTask(task.value.id);
  router.push("/");
}

function setStatus(status) {
  const [key] = Object.entries(taskStatuses).find(
    ([_, value]) => value === status,
  );
  const taskStatus = task.value.statusId;
  if (!taskStatus || taskStatus !== +key) {
    task.value.statusId = +key;
  } else {
    task.value.statusId = null;
  }
}

function createTick() {
  task.value.ticks = [...task.value.ticks, createNewTick()];
}

// Use uuid for new subtasks, id for existing ones
function updateTick(tick) {
  const index = task.value.ticks.findIndex(({ uuid, id }) => {
    if (uuid) {
      return tick.uuid === uuid;
    }
    if (id) {
      return tick.id === id;
    }
    return false;
  });
  if (~index) {
    task.value.ticks = [
      ...task.value.ticks.slice(0, index),
      tick,
      ...task.value.ticks.slice(index + 1),
    ];
  }
}

function removeTick({ uuid, id }) {
  if (uuid) {
    task.value.ticks = task.value.ticks.filter((tick) => tick.uuid !== uuid);
  }
  if (id) {
    task.value.ticks = task.value.ticks.filter((tick) => tick.id !== id);
    ticksStore.deleteTick(id);
  }
}

function setTags(tags) {
  task.value.tags = tags;
}

async function submit() {
  if (!validateFields(task.value, validations.value)) {
    isFormValid.value = false;
    return;
  }
  let taskId = task.value.id;
  if (props.taskToEdit) {
    // Editable task
    await tasksStore.editTask(task.value);
  } else {
    // New task
    const newTask = await tasksStore.addTask(task.value);
    taskId = newTask.id;
  }
  // Create or update subtasks
  await submitTicks(taskId, task.value.ticks);
  // Go to the main page
  await router.push("/");
}

async function submitTicks(taskId, ticks) {
  const promises = ticks
    .filter((tick) => tick.text)
    .map((tick) => {
      const { uuid, ...tickWithoutUuid } = tick;
      void uuid; // explicitly excluded from payload
      const tickToSubmit = { ...tickWithoutUuid, taskId };
      return tick.id
        ? ticksStore.updateTick(tickToSubmit)
        : ticksStore.addTick(tickToSubmit);
    });
  await Promise.all(promises);
}
</script>

<style lang="scss" scoped>
@import "@/assets/scss/app.scss";

.meta-filter {
  display: flex;
  align-items: center;

  $bl: ".meta-filter";
  @include clear-list;

  &__item {
    margin: 0 5px;

    &:first-child {
      margin-left: 0;
    }

    &.active {
      #{$bl}__status {
        box-shadow: 0 0 0 4px $blue-600;
      }
    }
  }

  &__status {
    display: block;

    box-sizing: content-box;
    width: 24px;
    height: 24px;
    margin: 0;
    padding: 0;

    cursor: pointer;
    transition: $animationSpeed;

    border-radius: 50%;
    outline: none;
    background-color: transparent;

    &:hover {
      box-shadow: 0 0 0 4px $orange-300;
    }

    &--green {
      background-color: $green-600;
    }

    &--orange {
      background-color: $orange-600;
    }

    &--red {
      background-color: $red-600;
    }

    &--time {
      width: 24px;
      height: 24px;

      background-image: url("~@/assets/img/status-time.svg");
      background-repeat: no-repeat;
      background-size: cover;
    }

    &--alert {
      width: 24px;
      height: 24px;

      background-image: url("~@/assets/img/status-alert.svg");
      background-repeat: no-repeat;
      background-size: cover;
    }

    &--current {
      border-color: $white-900;
      box-shadow: 0 0 0 1px $blue-600;
    }
  }
}

.task-card {
  position: fixed;
  z-index: 100;
  top: 0;
  left: 0;

  overflow-x: auto;

  box-sizing: border-box;
  width: 100%;
  height: 100%;
  padding-top: 50px;
  padding-bottom: 50px;

  outline: none;
  background: $gray-900;

  &__close {
    position: absolute;
    top: 16px;
    right: 16px;

    width: 20px;
    height: 20px;

    cursor: pointer;

    border: none;
    background-color: transparent;

    &::after,
    &::before {
      position: absolute;
      top: 50%;
      left: 50%;

      width: 100%;
      height: 1px;

      content: "";
      transition: background-color $animationSpeed;

      background-color: $blue-gray-300;
    }

    &::after {
      transform: translate(-50%, -50%) rotate(45deg);
    }

    &::before {
      transform: translate(-50%, -50%) rotate(-45deg);
    }

    &:hover {
      &::before,
      &::after {
        background-color: $blue-700;
      }
    }
  }

  &__error-text {
    color: $red-600;

    @include r-s10-h12;
  }

  &__wrapper {
    position: relative;

    box-sizing: border-box;
    width: 100%;
    max-width: 500px;
    margin: 0 auto;
    padding: 40px;

    background-color: $white-900;
    box-shadow: 0 4px 8px $shadow-500;
  }

  &__block {
    margin-bottom: 30px;
  }

  &__name {
    display: block;

    width: 100%;
    margin: 0;
    padding: 0;

    color: $gray-900;
    border: none;
    border-bottom: 1px solid transparent;
    border-radius: 0;
    outline: none;
    max-width: 59%;

    @include m-s24-h21;

    &:focus:not(:disabled) {
      cursor: text;

      border-color: $blue-gray-200;
    }

    &--disabled {
      pointer-events: none;
    }
  }

  &__row {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
  }

  &__date {
    margin-top: 5px;

    color: $blue-gray-300;

    @include r-s14-h21;
  }

  &__participant {
    display: inline-block;

    margin-left: 10px;

    vertical-align: baseline;
  }

  &__user,
  &__date-link {
    position: relative;

    display: flex;
    align-items: center;

    width: 100%;
    margin: 0;

    text-align: left;

    border: 0;
    outline: 0;
    background-color: transparent;

    @include r-s14-h16;

    img {
      width: 30px;
      height: 30px;
      margin-right: 10px;

      border-radius: 50%;
    }
  }

  &__user {
    &:active {
      color: $blue-gray-300;
    }
  }

  &__date-link {
    text-decoration: none;

    color: $gray-900;

    @include r-s16-h21;
  }

  &__params {
    position: relative;

    color: $gray-900;

    @include clear-list;
    @include r-s16-h21;

    li {
      display: flex;
      align-items: center;

      height: 30px;
      margin-bottom: 15px;
    }
  }

  &__links-item {
    margin-top: 16px;

    color: $blue-gray-300;

    @include r-s14-h21;

    a {
      color: $blue-600;
    }

    input {
      display: block;

      width: 100%;
      margin-bottom: 10px;

      color: $blue-gray-600;
      border: 0;
      outline: 0;

      font-family: inherit;
      font-size: 14px;
      font-weight: 400;
      font-style: normal;
      line-height: 21px;
    }
  }

  &__description {
    p {
      margin-top: 16px;

      color: $blue-gray-600;

      @include r-s14-h21;
    }

    textarea {
      display: block;

      box-sizing: border-box;
      width: 100%;
      height: 90px;
      margin-top: 16px;
      padding: 16px;

      resize: none;

      color: $blue-gray-600;
      border: 1px solid $gray-100;
      border-radius: 6px;
      outline: none;

      @include r-s14-h21;
    }
  }

  &__files {
    margin-top: 30px;
    margin-bottom: 20px;
  }

  &__list {
    @include clear-list;

    margin-top: 15px;
  }

  &__buttons {
    display: flex;
    justify-content: space-between;

    width: 410px;
    margin-top: 50px;
  }

  &__comments {
    margin-top: 20px;
  }

  &__status {
    display: flex;
    align-items: center;

    margin-bottom: 15px;
  }

  &__meta {
    margin-left: 5px;
  }

  &__edit {
    display: block;

    margin: 0;
    margin-top: 3px;
    padding: 0;

    cursor: pointer;
    transition: opacity $animationSpeed;

    opacity: 0.5;
    color: $blue-gray-600;
    border: none;
    outline: none;
    background-color: transparent;

    @include r-s14-h16;

    &:hover {
      opacity: 1;
    }

    &--red {
      align-self: center;

      margin-top: 0;

      color: $red-500;
    }
  }
}

:deep(.task-card__link) {
  position: relative;

  margin: 0;
  padding: 0 23px 0 0;

  cursor: pointer;
  text-decoration: underline;

  color: $blue-gray-600;
  border: none;
  background-color: transparent;

  @include r-s16-h21;

  &:after {
    position: absolute;
    top: 2px;
    right: 0;

    width: 14px;
    height: 14px;

    content: "";
    transition: opacity $animationSpeed;

    opacity: 0;
    background-image: url("@/assets/img/icon-pencil.svg");
    background-size: cover;
  }

  &:hover {
    text-decoration: none;

    &:after {
      opacity: 1;
    }
  }
}

:deep(.task-card__title) {
  margin: 0;

  color: $gray-900;

  @include m-s18-h21;
}

:deep(.task-card__item) {
  display: flex;
  align-items: center;
  justify-content: space-between;

  margin-top: 10px;

  &:hover {
    .task-card__icons {
      opacity: 1;
    }
  }
}

:deep(.task-card__icons) {
  transition: opacity $animationSpeed;

  opacity: 0;
}
</style>
