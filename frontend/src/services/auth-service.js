import { HttpClient } from "./HttpClient";
import { getToken } from "./token-manager";
import httpProvider from "@/services/providers";

const BASE_URL = "/api";

class AuthService extends HttpClient {
  login(email, password) {
    return this.post("/login", {
      data: { email, password },
    });
  }

  whoAmI() {
    return this.get("/whoAmI");
  }

  logout() {
    return this.delete("/logout");
  }
}

export default new AuthService({
  httpProvider,
  baseURL: BASE_URL,
  getToken,
});
