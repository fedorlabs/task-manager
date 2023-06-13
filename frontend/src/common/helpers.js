import { toRaw } from "vue";
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
  const currentTime = Date.now();
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
 *  Adds a specific task to the task list when moving
 *
 *  On the example of draggable tasks
 *
 * @param active - draggable task
 * @param toTask - the task that was dropped on (can be null)
 * @param tasks - list of tasks by the column we are working with (filtered in advance by the column)
 * @returns {*}
 */
export const addActive = (active, toTask, tasks) => {
  // If a task to be moved is found, it is removed from the tasks array.
  const activeIndex = tasks.findIndex((task) => task.id === active.id);
  if (~activeIndex) {
    tasks.splice(activeIndex, 1);
  }
  // Sorted in ascending order.
  tasks.sort((a, b) => a.sortOrder - b.sortOrder);

  // Update a task list with a draggable task
  if (toTask) {
    const toTaskIndex = tasks.findIndex((task) => task.id === toTask.id);
    tasks.splice(toTaskIndex, 0, active);
  } else {
    tasks.push(active);
  }
  return tasks;
};

/**
 * The URL function can take two parameters: a base URL and a relative URL.
 * In this case, the base URL is import.meta.url, which is the current URL of the JavaScript module,
 * and the relative URL is the path to the assets img folder and the name of the image file.
 *
 * @param {string} image
 */
export const getImage = (image) => {
  // https://vitejs.dev/guide/assets.html#new-url-url-import-meta-url
  return new URL(`../assets/img/${image}`, import.meta.url).href;
};

/**
 * To determine when a task has been created
 *
 * @param date
 * @returns {string}
 */
export const getTimeAgo = (date) => {
  // Checking if the date is in the correct format
  if (isNaN(Date.parse(date))) {
    return "... no time specified ...";
  }
  const seconds = Math.floor((new Date() - Date.parse(date)) / 1000);
  function getFinalString(number, pronounce) {
    return `${number} ${pronounce} ago`;
  }
  // Determine the correct ending
  function getPronounce(number, single, pluralTwoFour, pluralFive) {
    return number === 1
      ? single
      : number > 1 && number < 5
      ? pluralTwoFour
      : pluralFive;
  }
  // Check if the task was created more than a year ago
  let interval = seconds / YEAR_IN_SEC;
  if (interval > 1) {
    const number = Math.floor(interval);
    const pronounce = getPronounce(number, "year", "of the year", "years");
    return getFinalString(number, pronounce);
  }
  // Check if the task was created more than a month ago
  interval = seconds / MONTH_IN_SEC;
  if (interval > 1) {
    const number = Math.floor(interval);
    const pronounce = getPronounce(number, "month", "months", "months");
    return getFinalString(number, pronounce);
  }
  // Check if the task was created more than a day ago
  interval = seconds / DAY_IN_SEC;
  if (interval > 1) {
    const number = Math.floor(interval);
    const pronounce = getPronounce(number, "day", "day", "days");
    return getFinalString(number, pronounce);
  }
  // Check if the task was created more than one hour ago
  interval = seconds / HOUR_IN_SEC;
  if (interval > 1) {
    const number = Math.floor(interval);
    const pronounce = getPronounce(number, "hour", "hour", "hours");
    return getFinalString(number, pronounce);
  }
  // Check if the task was created more than one minute ago
  interval = seconds / MINUTE_IN_SEC;
  if (interval > 1) {
    const number = Math.floor(interval);
    const pronounce = getPronounce(number, "minute", "minutes", "minutes");
    return getFinalString(number, pronounce);
  }
  return "now";
};

/**
 * (DueDate шаблон для TaskView)
 *
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
 * UUID
 *
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
 * (Create a date template if there is no value in the date field in createTask)
 *
 * @returns {Date}
 */
export const createNewDate = () => {
  return new Date(new Date().setHours(23, 59, 59, 999));
};

/**
 * Path to files on the server
 *
 * @param path
 * @returns {`/api/${string}`}
 */
export const getPublicImage = (path) => {
  const publicUrl = "/api";
  return `${publicUrl}/${path}`;
};
