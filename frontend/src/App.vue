<template>
  <app-layout
    :tasks="filteredTasks"
    :filters="state.filters"
    @update-tasks="updateTasks"
  >
    <home-view
      :tasks="filteredTasks"
      :filters="state.filters"
      @update-tasks="updateTasks"
      @apply-filters="applyFilters"
    />
  </app-layout>
</template>

<script setup>
import { reactive, computed } from "vue";
import { AppLayout } from "@/layouts";
import { HomeView } from "@/views";
import { normalizeTask } from "./common/helpers";
import tasks from "./mocks/tasks.json";

const state = reactive({
  tasks: tasks.map((task) => normalizeTask(task)),
  filters: {
    search: "",
    users: [],
    statuses: [],
  },
});

/**
 * Фильтрация задач
 * @type {ComputedRef<unknown>}
 */
const filteredTasks = computed(() => {
  const filtersAreEmpty = Object.values(state.filters).every(
    // Нет какой-либо длинны (search, users, statuses), то вернуть все задачи
    (value) => !value.length
  );
  if (filtersAreEmpty) {
    // Вернуть все задачи если фильтры не применены
    return state.tasks;
  }

  // Применить фильтр по поиску
  const searchFilter = (task) =>
    // Поиск по заголовку через includes
    task.title
      .toLowerCase()
      .includes(state.filters.search.toLowerCase().trim());

  // Применить фильтр по пользователям
  const usersFilter = (task) =>
    // some - проходит ли хотя бы один элемент в массиве проверку
    state.filters.users.some((userId) => userId === task.userId);

  // Применить фильтр по статусам
  const statusesFilter = (task) =>
    state.filters.statuses.some(
      (el) => el === task.status || el === task.timeStatus
    );

  // Обработать задачи в соответствии с фильтрами
  return state.tasks.filter((task) => {
    let result = {
      search: searchFilter,
      users: usersFilter,
      statuses: statusesFilter,
    };

    // Срабатывает только тот фильтр в котором есть значения
    // Если условие  !state.filters[key].length  вернет  true, то callback(task) не будет вызван.
    return Object.entries(result).every(
      ([key, callback]) => !state.filters[key].length || callback(task)
    );
  });
});

/**
 * Обновляем задачу новыми данными
 * Функция обновляет задачи в массиве state.tasks на основе заданных данных из tasksToUpdate.
 * @param tasksToUpdate - содержит список задач, которые нужно обновить
 */
function updateTasks(tasksToUpdate) {
  tasksToUpdate.forEach((task) => {
    // Нахождение элемента с id, совпадающим с id задачи из tasksToUpdate.
    const index = state.tasks.findIndex(({ id }) => id === task.id);
    if (~index) {
      // Если такой элемент найден, то функция использует метод splice() для удаления старой задачи из массива state.tasks и вставки новой задачи на ее место.
      state.tasks.splice(index, 1, task);
    }
  });
}

/**
 * Функция используется для фильтрации массива данных.
 * Удаляем из массива фильтра сущности или добавляем item в массив фильтра по сущности.
 * @param item - элемент фильтра
 * @param entity - сущность, например users
 */
function applyFilters({ item, entity }) {
  // Если фильтры для данной сущности отсутствуют, то функция добавляет новый фильтр (item) в виде массива в объект
  // "state.filters" для данной сущности.
  if (!Array.isArray(state.filters[entity])) {
    state.filters[entity] = item;
  } else {
    // Функция ищет фильтр в массиве и если находит его, удаляет его из массива, в противном случае - добавляет его в массив.
    const resultValues = [...state.filters[entity]];
    // Сохраняет найденный индекс в переменной itemIndex. Затем этот индекс используется для удаления элемента из массива
    // resultValues с помощью метода  splice().
    const itemIndex = resultValues.findIndex((el) => el === item);
    ~itemIndex ? resultValues.splice(itemIndex, 1) : resultValues.push(item);
    state.filters[entity] = resultValues;
  }
}
</script>

<style lang="scss">
@import "@/assets/scss/app.scss";
</style>
