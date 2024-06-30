
# HealthVenture

Welcome to the HealthVenture project! This guide will help you set up and run the project from scratch.

## Prerequisites

Before you begin, make sure you have the following installed on your machine:

- [Git](https://git-scm.com/)
- [Docker](https://www.docker.com/products/docker-desktop)
- [Docker Compose](https://docs.docker.com/compose/install/)
- [Composer](https://getcomposer.org/)

## Getting Started

### 1. Clone the Repository

First, clone the repository to your local machine using Git:

```bash
gh repo clone IssamLammri/healthventure
```

### 2. Build and Run Docker Containers

Use Docker Compose to build and run the containers:

```bash
docker-compose up --build
```

### 3. Install PHP Dependencies

Open a new terminal window and install the PHP dependencies using Composer:

```bash
docker-compose exec app composer install
```

### 4. Set Up the Database

Run the database migrations to set up the database schema:

```bash
docker-compose exec app php bin/console doctrine:migrations:migrate
```

### 5. Access the Application

- **Symfony Application**: Open your browser and navigate to [http://localhost:8081](http://localhost:8081).
- **phpMyAdmin**: Open your browser and navigate to [http://localhost:8082](http://localhost:8082). Log in with \`root\` as the username and \`issam\` as the password.

## Additional Commands

### Docker Compose Commands

- **List Containers**: Display the status of the running containers.

  ```bash
  docker-compose ps
  ```

- **Stop and Remove Containers, Networks, Images, and Volumes**: This will stop and remove all containers, networks, images, and volumes defined in the `docker-compose.yml` file.

  ```bash
  docker-compose down --rmi all
  ```

- **Remove Unused Data**: This command will remove all unused containers, networks, images (both dangling and unreferenced), and optionally, volumes.

  ```bash
  docker system prune -af
  ```

- **Rebuild and Start Containers**: This will rebuild the images and start the containers.

  ```bash
  docker-compose up --build
  ```

### Node.js Commands

- **Remove Node Modules and Yarn Lock**: This will remove the `node_modules` directory and `yarn.lock` file from the app container.

  ```bash
  docker-compose exec app rm -rf node_modules yarn.lock
  ```

- **Install Node Modules**: This will install the Node.js dependencies defined in `package.json` using Yarn.

  ```bash
  docker-compose exec app yarn install
  ```

- **Run Development Server**: This will start the development server with file watching enabled.

  ```bash
  docker-compose exec app yarn run dev --watch
  ```

### Symfony Commands

- **Clear Symfony Cache**:

  ```bash
  docker-compose exec app php bin/console cache:clear
  ```

- **Run Tests**:

  ```bash
  docker-compose exec app php bin/phpunit
  ```

## Project Structure

A brief overview of the project structure:

```plaintext
healthVenture/
├── assets/
├── bin/
├── config/
├── docker/
│   ├── mysql/
│   │   └── Dockerfile
│   ├── php/
│   │   ├── Dockerfile
│   │   └── startup.sh
│   └── web/
│       ├── Dockerfile
│       └── default.conf
├── migrations/
├── public/
├── src/
├── templates/
├── tests/
├── translations/
├── var/
├── vendor/
├── .env
├── .env.test
├── .gitignore
├── compose.override.yaml
├── composer.json
├── composer.lock
├── docker-compose.yml
├── importmap.php
├── phpunit.xml.dist
└── symfony.lock
```

## Troubleshooting

If you encounter any issues, please check the following:

### Ensure all Docker containers are running:

```bash
docker ps
```

### Check the logs for any errors:

```bash
docker-compose logs
```

Feel free to open an issue if you encounter any problems or need further assistance.

## Makefile Commands

The project includes a Makefile to simplify the use of Docker and other commands. Below are the available commands:

- **Build and Start Containers**:

  ```bash
  make build
  ```

- **Install Composer Dependencies**:

  ```bash
  make install-composer
  ```

- **Run Database Migrations**:

  ```bash
  make migrate
  ```

- **Install Node Modules**:

  ```bash
  make install-node-modules
  ```

- **Start Development Server**:

  ```bash
  make dev
  ```

- **Clear Symfony Cache**:

  ```bash
  make clear-cache
  ```

- **Run Tests**:

  ```bash
  make test
  ```

- **List Docker Containers**:

  ```bash
  make ps
  ```

- **Stop and Remove Containers, Networks, Images, and Volumes**:

  ```bash
  make down
  ```

- **Remove Unused Data**:

  ```bash
  make prune
  ```

- **Remove Node Modules and Yarn Lock**:

  ```bash
  make clean-node-modules
  ```


- **All-in-One**: Build, install dependencies, migrate database, and start the development server::

  ```bash
  make all
  ```

## License

This project is licensed under the Issam LAMMRI License.