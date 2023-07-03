# «VueWork: Task Manager»

![Illustration for the project](./presentation/main.png)
![Illustration for the project](./presentation/task-view.png)
![Illustration for the project](./presentation/task-edit.png)

___

## Requirements
- Docker
- NodeJS >= 16
- GNU Make

### 1. Install project dependencies

`make install_project_dependencies`

### 2. Start project

`make start_project`

___

## Endpoints

Server `localhost:3000`

Client `localhost:8080`

Documentation (OpenAPI)  `localhost:3000/explorer/`

___

## Configurations

Database connection configuration for the server in a file `backend/src/datasources/database.datasource.ts`

### Authorized user login

To log in (login) to the system, use the following data:

```
email: user@example.com
password: user@example.com
```

You can change the user data in the file `backend/src/factory/users.json`
