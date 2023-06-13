import { fileURLToPath, URL } from "url";

import { defineConfig } from "vite";
import vue from "@vitejs/plugin-vue";

// only for local dev
// https://vitejs.dev/config/
export default defineConfig({
  plugins: [vue()], // include plugin vue
  test: {
    // JSDOM is a library for Node.js that allows you to emulate a browser environment in tests.
    // JSDOM is used to run tests in Node.js, not in the browser. Unlike Vitest, JSDOM is not a testing framework,
    // but only provides tools to emulate a browser environment.
    environment: "jsdom", // vitest
  },
  resolve: {
    alias: {
      // A built-in method in Node.js that is used to convert a URL object to a file path string on the file system
      "@": fileURLToPath(new URL("./src", import.meta.url)), // path to project src files in directory
    },
  },
  // sets the settings for the local development server, including the host and port, as well as the proxy server for accessing the API
  server: {
    host: true,
    port: 8080,
    proxy: {
      "/api": {
        target: "http://backend:3000/",
        rewrite: (path) => path.replace(/^\/api/, ""),
      },
    },
  },
});
