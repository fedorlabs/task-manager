/* eslint-env node */
require("@rushstack/eslint-patch/modern-module-resolution");

module.exports = {
  root: true,
  extends: [
    "plugin:vue/vue3-recommended",
    "eslint:recommended",
    "@vue/eslint-config-prettier",
  ],
  env: {
    "vue/setup-compiler-macros": true,
    es2022: true,
  },
  parserOptions: {
    ecmaVersion: "latest",
    sourceType: "module",
  },
  rules: {
    // Code quality
    "no-console": ["warn", { allow: ["warn", "error"] }],
    "no-debugger": "warn",
    "no-unused-vars": ["error", { argsIgnorePattern: "^_" }],
    "no-var": "error",
    "prefer-const": "error",
    "prefer-template": "error",
    eqeqeq: ["error", "smart"],
    "no-implicit-coercion": ["error", { allow: ["!!", "+"] }],
    curly: ["error", "multi-line"],
    "no-nested-ternary": "error",
    "no-unneeded-ternary": "error",
    "no-duplicate-imports": "error",

    // Vue specific
    "vue/component-name-in-template-casing": ["error", "kebab-case"],
    "vue/no-unused-refs": "error",
    "vue/no-useless-v-bind": "error",
    "vue/prefer-separate-static-class": "error",
    "vue/padding-line-between-blocks": ["error", "always"],
    "vue/block-lang": [
      "error",
      { script: { lang: "js" }, style: { lang: "scss" } },
    ],
    "vue/define-macros-order": [
      "error",
      { order: ["defineProps", "defineEmits"] },
    ],
  },
};
