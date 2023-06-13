import { emailRegex, urlRegex } from "./constants";
import { isRef } from "vue";

const rules = {
  required: {
    rule: (value) => {
      if (isRef(value)) {
        // !! true or false
        return !!value.value.trim();
      }
      return !!value?.trim();
    },
    message: "Required field",
  },
  email: {
    rule: (value) => {
      // convert to normal value and check on regex
      if (isRef(value)) {
        value = value.value;
      }
      return value ? emailRegex.test(String(value).toLowerCase()) : true;
    },
    message: "Email is not in the correct format",
  },
  url: {
    rule: (value) => {
      if (isRef(value)) {
        value = value.value;
      }
      return value ? urlRegex.test(value) : true;
    },
    message: "The link is in the wrong format",
  },
};

/**
 * Compare by rules
 *
 * @param { String } value - exp. value login, value password
 * @param { String[] } appliedRules - exp. email, require
 * @returns {string}
 */
const validate = (value, appliedRules) => {
  let error = "";
  // has in rules
  appliedRules.forEach((appliedRule) => {
    if (!rules[appliedRule]) {
      return;
    }
    // get methods
    const { rule, message } = rules[appliedRule];
    if (!rule(value)) {
      error = message;
    }
  });
  return error;
};

/**
 * Validation start
 *
 * @param fields - object from form
 * {
 *     "email": "adm@mail.com",
 *     "password": "123"
 * }
 * @param validations proxy with validation rules
 * {
 *     "email": {
 *         "error": "",
 *         "rules": [
 *             "required",
 *             "email"
 *         ]
 *     },
 *     "password": {
 *         "error": "",
 *         "rules": [
 *             "required"
 *         ]
 *     }
 * }
 * @returns {boolean}
 */
export const validateFields = (fields, validations) => {
  let isValid = true;
  Object.keys(validations).forEach((key) => {
    validations[key].error = validate(fields[key], validations[key].rules);
    if (validations[key].error) {
      isValid = false;
    }
  });
  return isValid;
};

/**
 * Clean up validation errors
 *
 * @param validations
 */
export const clearValidationErrors = (validations) => {
  if (!validations) {
    return;
  }
  Object.keys(validations).forEach((key) => {
    validations[key].error = "";
  });
};
