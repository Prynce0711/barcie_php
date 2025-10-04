# Docker setup for barcie_php

This file explains how to run the project using Docker and Docker Compose.

Prerequisites:

- Docker and Docker Compose installed on your machine.

Quick start:

1. Build and start containers:

```powershell
docker-compose up --build
```

2. The PHP app will be available at: http://localhost:8080

3. To create/import your SQL dump into the MariaDB container, you can either:

- Copy the SQL dump into the container and run mysql CLI inside the `db` container, or
- Run the following from your host (replace path):

```powershell
# Import dump.sql into the running MariaDB container
docker exec -i $(docker-compose ps -q db) mysql -u root -p${DB_PASS:-} ${DB_NAME:-barcie_db} < C:\path\to\dump.sql
```

Environment variables:

- DB_USER, DB_PASS, DB_NAME may be set in your shell or in an `.env` file. By default the compose file uses root with an empty password and database `barcie_db`.

Notes:

- `database/db_connect.php` reads DB credentials from environment variables so the app will connect to the `db` service automatically.
