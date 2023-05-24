<template>
  <!--  Keeps track of which column the task is dragged to -->
  <app-drop class="column" @drop="moveTask">
    <h2 class="column__name">
      <!--      Shows the name of the column-->
      <span v-if="!state.isInputShowed" data-test="desk-column-title">
        {{ state.columnTitle }}
      </span>

      <!--      Shows input if the column is being edited-->
      <!--      @blur - when an element loses focus-->
      <input
        v-else
        ref="columnTitle"
        v-model="state.columnTitle"
        type="text"
        class="column__input"
        name="column_title"
        @blur="updateInput"
      />

      <!--      Shows the task editing icon-->
      <app-icon
        v-if="!state.isInputShowed"
        class="icon--edit"
        @click="showInput"
      />
      <!--      Shows the column deletion icon-->
      <!--      The icon will not be displayed if there are tasks in the column-->
      <app-icon
        v-if="!state.isInputShowed && !columnTasks.length"
        class="icon--trash"
        @click="$emit('delete', column.id)"
      />
    </h2>

    <div data-test="column-target-area" class="column__target-area">
      <!--      Moved tasks to a separate component-->
      <transition-group name="tasks">
        <div v-for="task in columnTasks" :key="task.id">
          <task-card
            :task="task"
            class="column__task"
            @drop="moveTask($event, task)"
          />
        </div>
      </transition-group>
    </div>
  </app-drop>
</template>

<script setup>
import { reactive, computed, nextTick, ref } from "vue";
import AppDrop from "@/common/components/AppDrop.vue";
import AppIcon from "@/common/components/AppIcon.vue";
import TaskCard from "@/modules/tasks/components/TaskCard.vue";
import { getTargetColumnTasks, addActive } from "@/common/helpers";
import { useTasksStore } from "@/stores";

const props = defineProps({
  column: {
    type: Object,
    required: true,
  },
});

const emits = defineEmits(["update", "delete"]);

const tasksStore = useTasksStore();

const columnTitle = ref(null);
const state = reactive({
  isInputShowed: false,
  columnTitle: props.column.title,
});
const columnTasks = computed(() => {
  return tasksStore.filteredTasks
    .filter((task) => task.columnId === props.column.id)
    .sort((a, b) => a.sortOrder - b.sortOrder);
});

async function showInput() {
  state.isInputShowed = true;
  await nextTick();
  columnTitle.value.focus();
}

function updateInput() {
  state.isInputShowed = false;
  if (props.column.title === state.columnTitle) {
    return;
  }
  emits("update", {
    ...props.column,
    title: state.columnTitle,
  });
}

function moveTask(active, toTask) {
  if (toTask && active.id === toTask.id) {
    return;
  }

  const toColumnId = props.column ? props.column.id : null;
  const targetColumnTasks = getTargetColumnTasks(toColumnId, tasksStore.tasks);
  const activeClone = { ...active, columnId: toColumnId };
  const resultTasks = addActive(activeClone, toTask, targetColumnTasks);
  const tasksToUpdate = resultTasks.reduce((acc, task, index) => {
    if (task.sortOrder !== index || task.id === active.id) {
      acc.push({ ...task, sortOrder: index });
    }
    return acc;
  }, []);
  tasksStore.updateTasks(tasksToUpdate);
}
</script>

<style lang="scss" scoped>
@import "@/assets/scss/app.scss";

.column {
  display: flex;
  flex-direction: column;

  padding-top: 15px;

  border-right: 1px solid $blue-gray-200;

  $bl: &;

  &__name,
  &__input {
    display: flex;
    align-items: center;

    margin: 0 8px;

    color: $blue-gray-600;

    @include m-s14-h21;

    &:hover {
      #{$bl}__button {
        opacity: 1;
      }
    }
  }

  &__input {
    margin: 0;
    padding: 0;

    border: none;
    border-bottom: 1px solid $blue-gray-200;
    outline: none;
  }

  &__target-area {
    overflow-y: auto;
    flex-grow: 1;

    min-width: 224px;
    max-width: 380px;
    height: 1px;
    padding-right: 8px;
    padding-bottom: 30px;
    padding-left: 8px;

    @media (min-width: 1500px) {
      min-width: 244px;
    }
  }

  &__task {
    display: block;

    margin-top: 16px;
  }

  &__button {
    margin: 0;
    padding: 0;

    transition: opacity $animationSpeed;
    transform: scale(0.8);

    opacity: 0;
    border: none;
    outline: none;
    background-color: transparent;
  }

  &__update {
    margin-right: 5px;
    margin-left: 5px;
  }
}

.tasks-enter-active,
.tasks-leave-active {
  transition: all $animationSpeed ease;
}

.tasks-enter,
.tasks-leave-to {
  transform: scale(1.1);

  opacity: 0;
}
</style>
