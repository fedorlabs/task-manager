export class HttpClient {
  constructor(options) {
    if (!options.baseURL) {
      throw Error("[HttpClient]: Base url is empty");
    }
    this.httpProvider = options.httpProvider;
    this.getToken = options.getToken;
    this.baseUrl = options.baseURL;
  }

  buildRequest(options = {}) {
    const token = this.getToken();
    const headers = {
      "Content-Type": "application/json",
      ...(token && { Authorization: `Bearer ${token}` }),
      ...options.headers,
    };

    return {
      baseUrl: this.baseUrl,
      headers,
      ...options,
    };
  }

  // path must start with / to prevent malformed URLs like my-domain.comtaskscreate
  checkPath(path) {
    if (path !== "" && !path.startsWith("/")) {
      throw Error("Path must start with /", path);
    }
  }

  async get(path, options) {
    this.checkPath(path);
    return this.httpProvider.get(path, this.buildRequest(options));
  }

  async post(path, options) {
    this.checkPath(path);
    return this.httpProvider.post(path, this.buildRequest(options));
  }

  async put(path, options) {
    this.checkPath(path);
    return this.httpProvider.put(path, this.buildRequest(options));
  }

  async delete(path, options) {
    this.checkPath(path);
    return this.httpProvider.delete(path, this.buildRequest(options));
  }
}
