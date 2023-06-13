export default class FetchProvider {
    // Errors
    interceptors = []

    // Method for adding query parameters
    computeQueryParams(query) {
        if (!query) {
            return ''
        }
        // For example this code translate to "?name=John&age=30&city=New+York"
        // const query = {
        //     name: 'John',
        //     age: 30,
        //     city: 'New York'
        // };
        const queryParams = new URLSearchParams(query)
        return '?' + queryParams.toString()
    }

    // Method for a specific request
    request(options) {
        const body = options.data ? JSON.stringify(options.data) : null
        return fetch(
            // URL + path + query
            options.baseUrl + options.path + this.computeQueryParams(options.query),
            {headers: options.headers, body, method: options.method}
        )
            .then((response) => {
                // Response code not in range 200-299
                if (!response.ok) {
                    return Promise.reject(response)
                }
                return response
            })
            .then((response) => {
                if (response.status > 201) return Promise.resolve(response)
                // Convert body to json format
                return response.json()
            })
            .then((data) => {
                return data
            })
            .catch(async (response) => {
                const message = await this.onError(response)
                throw Error(message)
            })
    }

    addInterceptor(interceptor) {
        // Interceptor has error
        if (interceptor && interceptor.onError) {
            this.interceptors.push(interceptor)
        } else {
            throw Error('Interceptor не поддерживается')
        }
        return this
    }

    // Error from Promise
    async onError(response) {
        if (response.json) {
            // Retrieve values from an object and assign them to variables with appropriate names
            const {error} = await response.json()
            const {message, statusCode} = error
            // If the interceptor object has an onError method, then it will be called with two arguments: statusCode and message.
            this.interceptors.forEach((interceptor) => {
                if (interceptor.onError) {
                    interceptor.onError(statusCode, message)
                }
            })
            throw Error(message)
        } else if (response.message) {
            throw Error(response.message)
        }
    }

    get(path, requestOptions) {
        return this.request({path, method: 'GET', ...requestOptions})
    }

    post(path, requestOptions) {
        return this.request({path, method: 'POST', ...requestOptions})
    }

    put(path, requestOptions) {
        return this.request({path, method: 'PUT', ...requestOptions})
    }

    delete(path, requestOptions) {
        return this.request({path, method: 'DELETE', ...requestOptions})
    }
}
