# Tidjee's Symfony Template [WIP]

## 📝 Description

This repository is a boilerplate for quickly setting up a new Symfony project with a ready-to-use configuration.

It provides:

✅ An **Apache web server**

✅ Support for **PostgreSQL** and **MySQL** databases

✅ Database management tools: **PHPMyAdmin** or **pgAdmin**

✅ **Mailpit** as fake SMTP server

✅ A **Docker** environment for easy setup

✅ **Castor** as a task runner

## 🚀 Tech Stack

- **Languages & Database**

  [![PHP](https://img.shields.io/badge/PHP-8.4.x-777BB4?logo=php)](https://www.php.net/) [![MySQL](https://img.shields.io/badge/MySQL-latest-4479A1?logo=mysql)](https://www.mysql.com/) [![PostgreSQL](https://img.shields.io/badge/PostgreSQL-latest-316192?logo=postgresql)](https://www.postgresql.org/)

- **Frameworks**

  [![Symfony](https://img.shields.io/badge/Symfony-7.x-000?logo=symfony)](https://symfony.com/)

- **Web Server**

  [![Apache](https://img.shields.io/badge/Apache-2.4-D42029?logo=apache)](https://httpd.apache.org/)

- **Tools**

  [![Castor](https://img.shields.io/badge/Castor-latest-000)](https://castor.jolicode.com/) [![Docker](https://img.shields.io/badge/Docker-latest-0db7ed?logo=docker)](https://docs.docker.com/) [![Mailpit](https://img.shields.io/badge/Mailpit-latest-000)](https://mailpit.axllent.org/) [![PHPMyAdmin](https://img.shields.io/badge/PHPMyAdmin-latest-4479A1?logo=phpmyadmin)](https://www.phpmyadmin.net/) [![pgAdmin](https://img.shields.io/badge/pgAdmin-latest-000)](https://www.pgadmin.org/)

## 📌 Requirements

Before using this template, ensure you have:

- [Docker](https://www.docker.com/) & [Docker Compose](https://docs.docker.com/compose/)
- [PHP](https://www.php.net/) installed
- [Castor](https://castor.jolicode.com/) installed

## 🛠️ How to Use

1. **Create** a new Symfony project using this template
2. **Clone** your newly created repository
3. **Initialize the project** with:

   ```sh
   castor project:init
   ```

4. **Adapt the Docker configuration** to your needs

   Follow the instructions in the `compose.yml` file to configure the Docker environment.

5. **Start the Docker Stack** with:

   ```sh
   castor docker:start
   ```

## 🤝 Contributing

If you have any suggestions or find any issues, please [open an issue](https://github.com/tidjee-dev/symfony-template/issues/new).

## 🎉 Happy Coding! 🚀
