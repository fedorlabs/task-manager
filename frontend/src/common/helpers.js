import {
  DAY_IN_MILLISEC,
  TAG_SEPARATOR,
  MONTH_IN_SEC,
  YEAR_IN_SEC,
  DAY_IN_SEC,
  HOUR_IN_SEC,
  MINUTE_IN_SEC,
} from "./constants";
import timeStatuses from "./enums/timeStatuses";
import taskStatuses from "./enums/taskStatuses";
import { toRaw } from "vue";

/**
 * A function that takes a string with tags and splits it into an array by a certain identifier:
 *
 * @param tags
 * @returns {*}
 */
export const getTagsArrayFromString = (tags) => {
  const array = tags.split(TAG_SEPARATOR);
  return array.slice(1, array.length); // remove 1st element it is empty string
};

/**
 * Task completion date (deadline), compare it with the current time and return the status of the task by time.
 *
 * @param dueDate
 * @returns {string|string}
 */
export const getTimeStatus = (dueDate) => {
  if (!dueDate) {
    return "";
  }
  const currentTime = +new Date();
  const taskTime = Date.parse(dueDate);
  const timeDelta = taskTime - currentTime;
  if (timeDelta > DAY_IN_MILLISEC) {
    return "";
  }
  return timeDelta < 0 ? timeStatuses.DEADLINE : timeStatuses.EXPIRED;
};

/**
 * Normalize task object. Normalization is the transformation of a structure from one format to another.
 *
 * @param task
 * @returns {*&{timeStatus: (string|string), status: (*|string)}}
 */
export const normalizeTask = (task) => {
  return {
    ...task,
    // return enum value or " added enum(green, orange, red) string to task
    status: task.statusId ? taskStatuses[task.statusId] : "",
    // deadline or expired timeStatus added string to task
    timeStatus: getTimeStatus(task.dueDate),
  };
};

/**
 * Filtering tasks in a specific column
 * toRaw clear Proxy and return the object
 *
 * @param toColumnId
 * @param tasks
 * @returns {*}
 */
export const getTargetColumnTasks = (toColumnId, tasks) => {
  return tasks
    .filter((task) => task.columnId === toColumnId)
    .map((task) => toRaw(task));
};

/**
 *  Добавляет конкретную задачу в лист задач при перемещении
 *
 * @param active - перетаскиваемая задача
 * @param toTask - колонка после перемещаемой, может быть null
 * @param tasks - список задач, могут быть фильтрованы по колонке
 * @returns {*}
 */
export const addActive = (active, toTask, tasks) => {
  // Если перемещаемое задание найдено, то оно удаляется из массива tasks.
  const activeIndex = tasks.findIndex((task) => task.id === active.id);
  if (~activeIndex) {
    tasks.splice(activeIndex, 1);
  }
  // Сортируется в порядке возрастания.
  tasks.sort((a, b) => a.sortOrder - b.sortOrder);

  // Обновляем лист задач с перетаскиваемой задачей
  if (toTask) {
    const toTaskIndex = tasks.findIndex((task) => task.id === toTask.id);
    tasks.splice(toTaskIndex, 0, active);
  } else {
    tasks.push(active);
  }
  return tasks;
};

/**
 * Функция URL может принимать два параметра: базовый URL и относительный URL. В данном случае в качестве базового URL
 * используется  import.meta.url , которое является текущим URL модуля JavaScript, а относительный URL - это путь к
 * папке assets/img/ и имя файла image.
 *
 * @param {string} image
 */
export const getImage = (image) => {
  // https://vitejs.dev/guide/assets.html#new-url-url-import-meta-url
  return new URL(`../assets/img/${image}`, import.meta.url).href;
};

/**
 * Для определения, когда создана задача
 *
 * @param date
 * @returns {string}
 */
export const getTimeAgo = (date) => {
  // Проверяем, если дата приходит в корректном формате
  if (isNaN(Date.parse(date))) {
    return "... время не указано ...";
  }
  const seconds = Math.floor((new Date() - Date.parse(date)) / 1000);

  function getFinalString(number, pronounce) {
    return `${number} ${pronounce} назад`;
  }

  // Определяем правильное окончание
  function getPronounce(number, single, pluralTwoFour, pluralFive) {
    return number === 1
      ? single
      : number > 1 && number < 5
      ? pluralTwoFour
      : pluralFive;
  }

  // Проверяем, если задача создана более года назад
  let interval = seconds / YEAR_IN_SEC;
  if (interval > 1) {
    const number = Math.floor(interval);
    const pronounce = getPronounce(number, "год", "года", "лет");
    return getFinalString(number, pronounce);
  }
  // Проверяем, если задача создана более месяца назад
  interval = seconds / MONTH_IN_SEC;
  if (interval > 1) {
    const number = Math.floor(interval);
    const pronounce = getPronounce(number, "месяц", "месяца", "месяцев");
    return getFinalString(number, pronounce);
  }
  // Проверяем, если задача создана более дня назад
  interval = seconds / DAY_IN_SEC;
  if (interval > 1) {
    const number = Math.floor(interval);
    const pronounce = getPronounce(number, "день", "дня", "дней");
    return getFinalString(number, pronounce);
  }
  // Проверяем, если задача создана более часа назад
  interval = seconds / HOUR_IN_SEC;
  if (interval > 1) {
    const number = Math.floor(interval);
    const pronounce = getPronounce(number, "час", "часа", "часов");
    return getFinalString(number, pronounce);
  }
  // Проверяем, если задача создана более минуты назад
  interval = seconds / MINUTE_IN_SEC;
  if (interval > 1) {
    const number = Math.floor(interval);
    const pronounce = getPronounce(number, "минуту", "минуты", "минут");
    return getFinalString(number, pronounce);
  }
  return "сейчас";
};

/**
 * @param date
 * @returns {string}
 */
export const getReadableDate = (date) => {
  if (isNaN(Date.parse(date))) return "";
  const newDate = new Date(date);
  const year = newDate.getFullYear();
  const month = newDate.getMonth();
  const day = newDate.getDate();
  return `${day}.${month + 1}.${year}`;
};

/**
 * @returns {string}
 */
export const createUUIDv4 = () => {
  return "xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx".replace(/[xy]/g, (c) => {
    const r = (Math.random() * 16) | 0,
      v = c === "x" ? r : (r & 0x3) | 0x8;
    return v.toString(16);
  });
};

/**
 * @returns {Date}
 */
export const createNewDate = () => {
  return new Date(new Date().setHours(23, 59, 59, 999));
};
