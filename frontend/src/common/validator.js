import { emailRegex, urlRegex } from "./constants";
import { isRef } from "vue";

/**
 * isRef - используется для проверки, является ли значение ссылкой (ref) на другой элемент. Если да, то берется значение по этой ссылке. Это нужно для случаев, когда в форме используются связанные поля, и валидация одного поля зависит от значения другого.
 * @type {{required: {rule: ((function(*): *)|*), message: string}, email: {rule: (function(*): boolean|boolean), message: string}, url: {rule: (function(*): boolean|boolean), message: string}}}
 */
const rules = {
  required: {
    rule: (value) => {
      if (isRef(value)) return !!value.value.trim();
      return !!value?.trim();
    },
    message: "Поле обязательно для заполнения",
  },
  email: {
    rule: (value) => {
      if (isRef(value)) {
        value = value.value;
      }
      return value ? emailRegex.test(String(value).toLowerCase()) : true;
    },
    message: "Электронная почта имеет неверный формат",
  },
  url: {
    rule: (value) => {
      if (isRef(value)) {
        value = value.value;
      }
      return value ? urlRegex.test(value) : true;
    },
    message: "Ссылка имеет неверный формат",
  },
};

/**
 *
 * @param value - строка для проверки
 * @param appliedRules - массив имён правил валидации
 * @returns {string}
 */
const validate = (value, appliedRules) => {
  let error = "";
  // Для каждого правила он проверяет, есть ли оно в объекте rules
  // Если правило есть, он извлекает из него функцию валидации rule и сообщение об ошибке message
  // Он вызывает функцию валидации rule, передавая ей value
  // Если функция вернет false, то error будет присвоено сообщение об ошибке для этого правила
  // В конце возвращается либо пустая строка, либо сообщение об ошибке
  appliedRules.forEach((appliedRule) => {
    if (!rules[appliedRule]) {
      return;
    }
    const { rule, message } = rules[appliedRule];
    if (!rule(value)) {
      error = message;
    }
  });
  return error;
};

/**
 * @param fields - объект с полями для валидации, например { myText: 'abc', myEmail: 'email@example.com' }.
 * @param validations - объект с правилами валидаций
 * {
 * 	myText: {
 * 		error: '', - будет записана ошибка валидации
 * 		rules: ['required'] - содержит правила валидации для конкретного поля.
 * 	},
 *   	myEmail: {
 * 		error: '',
 * 		rules: ['required', 'email']
 * 	}
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
 * Функция для очистки ошибок валидации
 * @param validations
 */
export const clearValidationErrors = (validations) => {
  console.log("validate");
  if (!validations) {
    return;
  }
  Object.keys(validations).forEach((key) => {
    validations[key].error = "";
  });
};
