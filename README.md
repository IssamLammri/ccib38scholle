
# schollccib

Welcome to the **schollccib** project! Follow this guide to set up and run the project from scratch.

## Prerequisites

Ensure you have the following installed before starting:
- [Git](https://git-scm.com/)
- [Docker](https://www.docker.com/products/docker-desktop)
- [Docker Compose](https://docs.docker.com/compose/install/)
- [Composer](https://getcomposer.org/)

## Getting Started

### 1. Clone the Project

Clone the repository to your local machine using Git:

```bash
git clone https://github.com/IssamLammri/ccib38scholle.git
```

### 2. Build the Containers

In the root directory of the project, use the Makefile to build and run the containers:

```bash
make build
```

### 3. Start Development Environment

After building, run the development environment:

```bash
make dev
```

### 4. Set Up the Database

Create a new database named **`schollccib`** in your phpMyAdmin environment at [http://localhost:8082](http://localhost:8082). Then, update the database structure:

```bash
make update-structure
```

Run the migrations:

```bash
make migrate
```

### 5. Your Project is Ready!

Your project is now set up and ready for further development. You can start developing your application.

## Running Commands

To execute commands within the project, you must be in the root directory and prefix commands with:

```bash
docker-compose exec app
```

For example, to install Composer dependencies:

```bash
docker-compose exec app composer install
```

## Available Makefile Commands

The Makefile includes several commands to simplify project management. Here are some examples:

- **Build Containers**:

  Build and start Docker containers.
  ```bash
  make build
  ```

- **Install Composer Dependencies**:

  Install all PHP dependencies via Composer.
  ```bash
  make install-composer
  ```

- **Update Database Structure**:

  Create a new migration for the database.
  ```bash
  make update-structure
  ```

- **Run Migrations**:

  Execute pending migrations to update the database schema.
  ```bash
  make migrate
  ```

- **Install Node Modules**:

  Install the JavaScript dependencies via Yarn.
  ```bash
  make install-node-modules
  ```

- **Start Development Server**:

  Run the Symfony development server and watch for changes with Yarn.
  ```bash
  make dev
  ```

- **Clear Symfony Cache**:

  Clear the Symfony application cache.
  ```bash
  make clear-cache
  ```

- **Run Unit Tests**:

  Run the test suite using PHPUnit.
  ```bash
  make test
  ```

- **List Docker Containers**:

  Display the current status of all running Docker containers.
  ```bash
  make ps
  ```

- **Stop and Remove Containers, Networks, Images, and Volumes**:

  Shut down and clean up the environment.
  ```bash
  make down
  ```

- **Remove Unused Data**:

  Clean up all unused Docker resources.
  ```bash
  make prune
  ```

- **Remove Node Modules and Yarn Lock**:

  Clean up Node.js environment by deleting `node_modules` and `yarn.lock`.
  ```bash
  make clean-node-modules
  ```

- **All-in-One Command**:

  Build the project, install dependencies, run migrations, and start the development environment:
  ```bash
  make all
  ```

## Accessing the Application

- **Symfony Application**: [http://localhost:8081](http://localhost:8081)
- **phpMyAdmin**: [http://localhost:8082](http://localhost:8082) (Username: `root`, Password: `issam`)

## Troubleshooting

If you run into any issues, ensure all Docker containers are running:

```bash
docker ps
```

Check the logs for any errors:

```bash
docker-compose logs
```

## License

This project is licensed under the **Issam LAMMRI License**.
