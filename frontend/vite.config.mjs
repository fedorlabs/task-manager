import { fileURLToPath, URL } from "node:url";

import { defineConfig } from "vite";
import vue from "@vitejs/plugin-vue";

// https://vitejs.dev/config/
export default defineConfig({
  plugins: [vue()],
  test: {
    // JSDOM is a library for Node.js that allows you to emulate a browser environment in tests.
    environment: "jsdom",
  },
  build: {
    target: "esnext", // Support for top-level await
  },
  css: {
    preprocessorOptions: {
      scss: {
        api: "modern-compiler",
      },
    },
  },
  resolve: {
    alias: {
      // A built-in method in Node.js that is used to convert a URL object to a file path string on the file system
      "@": fileURLToPath(new URL("./src", import.meta.url)),
    },
  },
  // sets the settings for the local development server, including the host and port, as well as the proxy server for accessing the API
  server: {
    host: true,
    port: 8080,
    proxy: {
      "/api": {
        target: "http://backend:8000/",
        rewrite: (path) => path.replace(/^\/api/, ""),
      },
    },
  },
});
