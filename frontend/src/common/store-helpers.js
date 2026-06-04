/**
 * Wraps an async store action with loading/error state management.
 * Errors are caught and stored in `store.error`, not re-thrown.
 *
 * @param {Object} store - Pinia store instance (must have `loading` and `error` state)
 * @param {Function} action - Async function to execute
 * @returns {Promise<*>} - Resolves with action result or undefined on error
 */
export async function withLoading(store, action) {
  store.loading = true;
  store.error = null;
  try {
    return await action();
  } catch (e) {
    store.error = e.message || "Unknown error";
  } finally {
    store.loading = false;
  }
}
