import columnsJSON from "../../mocks/columns.json";
import tasksJSON from "../../mocks/tasks.json";
import usersJSON from "../../mocks/users.json";
import { vi } from "vitest";

vi.mock("@/services", () => {
  return {
    columnsService: {
      // In node.js version 17+, you can use structuredClone for deep cloning
      fetchColumns: vi.fn(async () => {
        return await JSON.parse(JSON.stringify(columnsJSON));
      }),
      createColumn: vi.fn(async () => ({ id: 6 })),
      updateColumns: vi.fn(),
      deleteColumns: vi.fn(),
    },
    tasksService: {
      fetchTasks: vi.fn(async () => JSON.parse(JSON.stringify(tasksJSON))),
    },
    userService: {
      fetchUsers: vi.fn(async () => {
        return await JSON.parse(JSON.stringify(usersJSON));
      }),
    },
  };
});
