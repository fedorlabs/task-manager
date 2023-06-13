// set a handler function before each route navigation
export default (router) => {
  router.beforeEach(async (to, from) => {
    // Finding middlewares in the meta.middlewares property of the selected route
    const middlewares = to.meta.middlewares;
    // If the route has no middleware, then go to the page
    if (!middlewares) {
      return true;
    }
    // Middlewares in block meta
    for (const middleware of middlewares) {
      const result = await middleware({ to, from });
      // If the middleware returns an object or a route string, then we break the chain and return the result
      // Example of object {path:'/'}, this will redirect to the route /
      if (
        typeof result === "object" ||
        typeof result === "string" ||
        result === false
      ) {
        return result;
      }
    }
    return true;
  });
};
