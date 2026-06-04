/**
 * Wraps an async store action with loading/error state management.
 * Errors are stored in `store.error` for UI display, then re-thrown
 * so callers can handle them (show toast, rollback, etc.).
 *
 * @param {Object} store - Pinia store instance (must have `loading` and `error` state)
 * @param {Function} action - Async function to execute
 * @returns {Promise<*>} - Resolves with action result
 * @throws {Error} - Re-throws the original error after storing it
 */
export async function withLoading(store, action) {
  store.loading = true;
  store.error = null;
  try {
    return await action();
  } catch (e) {
    store.error = e.message || "Unknown error";
    throw e;
  } finally {
    store.loading = false;
  }
}
