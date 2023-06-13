import FetchProvider from "./FetchProvider";
import { useAuthStore } from "@/stores";

const httpProvider = new FetchProvider();
httpProvider.addInterceptor({
  onError: async (status) => {
    // If an error 401 Unauthorized comes from the service, then we log out of the system
    if (status === 401) {
      const authStore = useAuthStore();
      if (authStore.isAuthenticated) {
        await authStore.logout();
      }
    }
  },
});

export default httpProvider;
