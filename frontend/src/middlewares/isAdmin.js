import { useAuthStore } from "@/stores";

/**
 * If not admin, redirect to /
 * @returns {boolean|{path: string}}
 */
export default function isAdmin() {
  const authStore = useAuthStore();
  if (!authStore.user?.isAdmin) {
    return { path: "/" };
  }
  return true;
}
