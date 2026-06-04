export default class FetchProvider {
  // Errors
  interceptors = [];

  // Method for adding query parameters
  computeQueryParams(query) {
    if (!query) {
      return "";
    }
    const queryParams = new URLSearchParams(query);
    return `?${queryParams.toString()}`;
  }

  // Method for a specific request
  async request(options) {
    const body = options.data ? JSON.stringify(options.data) : null;

    try {
      const response = await fetch(
        options.baseUrl + options.path + this.computeQueryParams(options.query),
        { headers: options.headers, body, method: options.method },
      );

      if (!response.ok) {
        await this.onError(response);
        return;
      }

      // No content or status > 201 — return response as-is
      if (response.status === 204 || response.status > 201) {
        return response;
      }

      return await response.json();
    } catch (error) {
      // Re-throw network errors and already-processed API errors
      if (error.message) {
        throw error;
      }
      throw new Error("Network error");
    }
  }

  addInterceptor(interceptor) {
    if (interceptor && interceptor.onError) {
      this.interceptors.push(interceptor);
    } else {
      throw Error("Interceptor is not supported");
    }
    return this;
  }

  // Error from Promise
  async onError(response) {
    let message = "Unknown error";
    let statusCode = response.status;

    try {
      const data = await response.json();
      message = data.error?.message || data.message || message;
      statusCode = data.error?.statusCode || statusCode;
    } catch {
      message = response.statusText || message;
    }

    // Notify interceptors
    this.interceptors.forEach((interceptor) => {
      if (interceptor.onError) {
        interceptor.onError(statusCode, message);
      }
    });

    throw new Error(message);
  }

  get(path, requestOptions) {
    return this.request({ path, method: "GET", ...requestOptions });
  }

  post(path, requestOptions) {
    return this.request({ path, method: "POST", ...requestOptions });
  }

  put(path, requestOptions) {
    return this.request({ path, method: "PUT", ...requestOptions });
  }

  delete(path, requestOptions) {
    return this.request({ path, method: "DELETE", ...requestOptions });
  }
}
