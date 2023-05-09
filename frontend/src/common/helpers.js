import {
    DAY_IN_MILLISEC,
    TAG_SEPARATOR
} from './constants';
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
    return array.slice(1, array.length); // 1st element is empty string after TAG_SEPARATOR split then remove it
};

/**
 * Task completion date (deadline), compare it with the current time and return the status of the task by time.
 *
 * @param dueDate
 * @returns {string|string}
 */
export const getTimeStatus = (dueDate) => {
    if (!dueDate) {
        return '';
    }
    const currentTime = +new Date();
    const taskTime = Date.parse(dueDate);
    const timeDelta = taskTime - currentTime;
    if (timeDelta > DAY_IN_MILLISEC) {
        return '';
    }
    return timeDelta < 0 ? timeStatuses.DEADLINE : timeStatuses.EXPIRED;
};

/**
 * Normalize task object. Normalization is the transformation of a structure from one format to another.
 * taskStatuses enum(green, orange, red)
 *
 * @param task
 * @returns {*&{timeStatus: (string|string), status: (*|string)}}
 */
export const normalizeTask = (task) => {
    return {
        ...task,
        status: task.statusId ? taskStatuses[task.statusId] : '',
        timeStatus: getTimeStatus(task.dueDate)
    };
};