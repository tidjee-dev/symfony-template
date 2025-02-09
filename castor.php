<?php

use function Castor\fs;
use function Castor\io;
use function Castor\run;

use Castor\Attribute\AsTask;

/*
 * Project
 */

/**
 * Creates a new Symfony project in the current directory.
 *
 * This task performs the following steps:
 * 1. It creates a new Symfony project using the `composer create-project` command.
 * 2. It initializes Git in the project directory (if needed).
 * 3. It creates a web application by installing the `webapp` package (if needed).
 * 4. It creates a `README.md` file if it doesn't already exist.
 *
 * The task will ask you the following questions:
 * 1. What version of Symfony do you want to use? (default: latest)
 * 2. What stability do you want to use? (default: stable)
 * 3. Do you want to initialize Git in the project? (default: yes)
 * 4. Do you want to make the first commit? (default: yes)
 * 5. Do you want to create a web application? (default: yes)
 *
 * @see https://symfony.com/doc/current/setup.html
 */
#[AsTask(description: 'Create new Symfony project', aliases: ['project:init'], namespace: 'project')]
function symfonyInit(): void
{
    io()->title('Creating new Symfony project');

    if (!fs()->exists('composer.json')) {
        io()->section('Creating a new Symfony project in the current directory');
        $sf_version = io()->ask('What version of Symfony do you want to use? (default: latest)', '');
        $stability = io()->ask('What stability do you want to use?', 'stable');
        run('composer create-project "symfony/skeleton ' . $sf_version . '" tmp --stability="' . $stability . '" --prefer-dist --no-progress --no-interaction --no-install');
        run('cp -Rp tmp/. .');
        run('rm -Rf tmp/');
        run('composer install --prefer-dist --no-progress --no-interaction');
    }

    run('composer config --json extra.symfony.docker false');

    if (!fs()->exists('.git')) {
        io()->section('Initializing Git');
        $git = io()->confirm('Do you want to initialize Git in the project? ', false);
        if ($git) {
            run('git init');
            $remoteUrl = io()->ask('What is the remote repository URL?');
            run('git remote add origin ' . $remoteUrl);
            io()->newLine();
            io()->info([
                'Git initialized and remote repository added.',
                'You can now push your code to the remote repository.'
            ]);

            $firstCommit = io()->confirm('Do you want to make the first commit?', false);
            if ($firstCommit) {
                run('git add .');
                run('git commit -m "Initial commit"');
            }
        }
    }

    if (!fs()->exists('templates')) {
        io()->section('Configuring project as a web application');
        $webapp = io()->confirm('Do you want to create a web application?', false);
        if ($webapp) {
            run('composer require webapp --no-progress --no-interaction');
        }
    }

    if (!fs()->exists('README.md')) {
        fs()->touch('README.md');
        fs()->appendToFile('README.md', '# ' . basename(getcwd()) . PHP_EOL);
    } else {
        fs()->copy('README.md', 'docs/templates/README.md');
        fs()->dumpFile('README.md', '# ' . basename(getcwd()) . PHP_EOL);
    }

    io()->success([
        "Your new Symfony project is successfully created in " . getcwd(),
    ]);
    io()->info([
        "Run `castor` to see all available tasks",
    ]);
}

/*
 * Composer
 */

/**
 * Install composer dependencies.
 *
 * This task runs the 'composer install' command to install
 * all dependencies defined in the composer.json file.
 */
#[AsTask(description: 'Install composer dependencies', aliases: ['comp:install'], namespace: 'composer')]
function composerInstall(): void
{
    io()->title('Installing composer dependencies');
    run('composer install');
    io()->newLine();
    io()->success('Composer dependencies installed');
}

/*
 * Docker
 */

/**
 * Start Docker Stack
 *
 * This task runs the 'docker compose up -d' command to start
 * all services defined in the compose.yml file in
 * detached mode.
 */
#[AsTask(description: 'Start Docker Stack', aliases: ['docker:start'], namespace: 'docker')]
function dockerStart(): void
{
    io()->title('Starting Docker Stack');
    run('docker compose up -d');
    io()->newLine();
    io()->success('Docker Stack started');
}

/**
 * Stop all services defined in the compose.yml file.
 *
 * This task is useful to stop all services when you are done with
 * development or testing.
 */
#[AsTask(description: 'Stop Docker Stack', aliases: ['docker:stop'], namespace: 'docker')]
function dockerStop(): void
{
    io()->title('Stopping Docker Stack');
    run('docker compose stop');
    io()->newLine();
    io()->success('Docker Stack stopped');
}

/**
 * Restart all services defined in the compose.yml file.
 *
 * This task is useful to restart all services when you have made
 * changes to the compose.yml file.
 */
#[AsTask(description: 'Restart Docker Stack', aliases: ['docker:restart'], namespace: 'docker')]
function dockerRestart(): void
{
    io()->title('Restarting Docker Stack');
    run('docker compose restart');
    io()->newLine();
    io()->success('Docker Stack restarted');
}

/**
 * Remove all services defined in the compose.yml file.
 *
 * This task is useful to remove all services when you are done with
 * development or testing.
 */
#[AsTask(description: 'Remove Docker Stack', aliases: ['docker:remove'], namespace: 'docker')]
function dockerRemove(): void
{
    io()->title('Removing Docker Stack');
    io()->info('This will remove all services defined in the compose.yml file.');
    $confirm = io()->confirm('Are you sure you want to remove the Docker Stack?', false);
    if ($confirm) {
        run('docker compose down');
        io()->newLine();
        io()->success('Docker Stack removed');
    } else {
        io()->warning('Docker Stack not removed');
    }
}

/**
 * Remove all unused Docker images, containers and networks.
 *
 * This task is useful when you want to clean up the Docker environment
 * after you are done with development or testing.
 *
 * @see https://docs.docker.com/engine/reference/commandline/system_prune/
 */
#[AsTask(description: 'Clean Docker Environment', aliases: ['docker:clean'], namespace: 'docker')]
function dockerClean(): void
{
    io()->title('Cleaning Docker Environment');
    io()->info('This will remove all unused Docker images, containers and networks.');
    $confirm = io()->confirm('Are you sure you want to clean the Docker Environment?', false);
    $volumes = io()->confirm('Do you want to remove unused Docker volumes too?', false);
    if ($confirm) {
        run('docker system prune -a -f ' . ($volumes ? '--volumes' : ''));
        io()->newLine();
        io()->success('Docker Environment cleaned');
    } else {
        io()->warning('Docker Environment not cleaned');
    }
}

/*
 * Symfony
 */

/**
 * Run Symfony server.
 *
 * This task runs the `symfony serve -d` command to start the Symfony server.
 * The `-d` option runs the server in detached mode.
 *
 * @see https://symfony.com/doc/current/setup/symfony_server.html
 */
#[AsTask(description: 'Start Symfony Server', aliases: ['sf:srv:start'], namespace: 'symfony')]
function serverStart(): void
{
    io()->title('Starting Symfony Server');
    run('symfony serve -d');
}

/**
 * Stop Symfony server.
 *
 * This task runs the `symfony server:stop` command to stop the Symfony server.
 *
 * @see https://symfony.com/doc/current/setup/symfony_server.html
 */
#[AsTask(description: 'Stop Symfony Server', aliases: ['sf:srv:stop'], namespace: 'symfony')]
function serverStop(): void
{
    io()->title('Stopping Symfony Server');
    run('symfony server:stop');
}

/**
 * Show Symfony server log.
 *
 * This task runs the `symfony server:log` command to show the Symfony server log.
 *
 * @see https://symfony.com/doc/current/setup/symfony_server.html
 */
#[AsTask(description: 'Show Symfony Server Log', aliases: ['sf:srv:log'], namespace: 'symfony')]
function serverLog(): void
{
    io()->title('Showing Symfony Server Log');
    run('symfony server:log');
}

/**
 * Clear Symfony cache.
 *
 * This task runs the `symfony console cache:clear` command to clear the
 * Symfony cache.
 */
#[AsTask(description: 'Clear Cache', aliases: ['sf:cc'], namespace: 'symfony')]
function clearCache(): void
{
    io()->title('Clearing Cache');
    run('symfony console cache:clear');
}

/*
 * Maker Bundle
 */

/**
 * Installs the Maker Bundle.
 *
 * This task runs the `composer require --dev symfony/maker-bundle` command to install
 * the Maker Bundle.
 *
 * @see https://symfony.com/doc/current/bundles/SymfonyMakerBundle/index.html
 */
#[AsTask(description: 'Install Maker Bundle', aliases: ['make:install'], namespace: 'maker')]
function installMakerBundle(): void
{
    io()->title('Installing Maker Bundle');
    run('composer require --dev symfony/maker-bundle');
    io()->newLine();
    io()->success('Maker Bundle installed');
}

/**
 * Create a new Symfony Controller.
 *
 * This task runs the `symfony console make:controller` command to generate
 * a new controller class and its associated template in the Symfony application.
 */
#[AsTask(description: 'Create new Controller', aliases: ['make:controller'], namespace: 'maker')]
function makeController(): void
{
    io()->title('Creating new Controller');
    run('symfony console make:controller');
}

/**
 * Create a new Symfony User.
 *
 * This task runs the `symfony console make:user` command to generate
 * a new User Entity and its associated repository in the Symfony application.
 */
#[AsTask(description: 'Create new User', aliases: ['make:user'], namespace: 'maker')]
function makeUser(): void
{
    io()->title('Creating new User');
    run('symfony console make:user');
}

/**
 * Create a new Symfony Entity.
 *
 * This task runs the `symfony console make:entity` command to generate
 * a new Entity and its associated repository in the Symfony application.
 */
#[AsTask(description: 'Create new Entity', aliases: ['make:entity'], namespace: 'maker')]
function makeEntity(): void
{
    io()->title('Creating new Entity');
    run('symfony console make:entity');
}

/**
 * Create a new Symfony Form.
 *
 * This task runs the `symfony console make:form` command to generate
 * a new Form in the Symfony application.
 */
#[AsTask(description: 'Create new Form', aliases: ['make:form'], namespace: 'maker')]
function makeForm(): void
{
    io()->title('Creating new Form');
    run('symfony console make:form');
}

/*
 * DB
 */

/**
 * Create a new database.
 *
 * This task runs the `symfony console doctrine:database:create` command to
 * create a new database.
 *
 * @see https://symfony.com/doc/current/doctrine.html#creating-the-database
 */
#[AsTask(description: 'Create new Database', aliases: ['db:create'], namespace: 'database')]
function createDatabase(): void
{
    io()->title('Creating new Database');
    run('symfony console doctrine:database:create --if-not-exists');
}

/**
 * Drop the current database.
 *
 * This task runs the `symfony console doctrine:database:drop --force` command to
 * drop the current database.
 *
 * @see https://symfony.com/doc/current/doctrine.html#drop-the-database
 */
#[AsTask(description: 'Drop Database', aliases: ['db:drop'], namespace: 'database')]
function dropDatabase(): void
{
    io()->title('Dropping Database');
    run('symfony console doctrine:database:drop --force');
}

/**
 * Create a new Doctrine Migration.
 *
 * This task runs the `symfony console make:migration` command to create
 * a new Doctrine migration.
 *
 * @see https://symfony.com/doc/current/doctrine.html#migrations-creating-the-database-tables-schema
 */
#[AsTask(description: 'Create new Migration', aliases: ['db:migration'], namespace: 'database')]
function createMigration(): void
{
    io()->title('Creating new Migration');
    run('symfony console make:migration --no-interaction');
}

/**
 * Run all available Doctrine migrations to update the database to the latest version.
 *
 * This task runs the `symfony console doctrine:migrations:migrate` command to execute
 * all available Doctrine migrations to update the database to the latest version.
 *
 * @see https://symfony.com/doc/current/doctrine.html#migrations-creating-the-database-tables-schema
 */
#[AsTask(description: 'Run Migrations', aliases: ['db:migrate'], namespace: 'database')]
function runMigrations(): void
{
    io()->title('Running Migrations');
    run('symfony console doctrine:migrations:migrate --no-interaction');
}

/**
 * Initialize the database by creating it if it does not exist,
 * generating a new migration, and applying all migrations.
 *
 * This task performs the following commands:
 * 1. `symfony console doctrine:database:create --if-not-exists` to create the database if it doesn't exist.
 * 2. `symfony console make:migration` to create a new Doctrine migration.
 * 3. `symfony console doctrine:migrations:migrate` to apply all available migrations.
 *
 * @see https://symfony.com/doc/current/doctrine.html
 */
#[AsTask(description: 'Initialize Database', aliases: ['db:init'], namespace: 'database')]
function initializeDatabase(): void
{
    io()->title('Initializing Database');
    run('symfony console doctrine:database:create --if-not-exists');
    run('symfony console make:migration');
    run('symfony console doctrine:migrations:migrate');
    $fixtures = io()->ask('Would you like to load fixtures?', 'y');
    if ($fixtures === 'y') {
        loadFixtures();
    }
    io()->newLine();
    io()->success('Database initialized');
}

/**
 * Reset the current database.
 *
 * This task runs the following commands to reset the current database:
 *
 * 1. `symfony console doctrine:database:drop --force` to drop the current database.
 * 2. `symfony console doctrine:database:create` to create a new database.
 * 3. `symfony console doctrine:migrations:migrate` to apply all migrations.
 *
 * @see https://symfony.com/doc/current/doctrine.html#resetting-the-database
 */
#[AsTask(description: 'Reset Database', aliases: ['db:reset'], namespace: 'database')]
function resetDatabase(): void
{
    io()->title('Resetting Database');
    run('symfony console doctrine:database:drop --force');
    run('symfony console doctrine:database:create');
    run('symfony console doctrine:migrations:migrate');
    $fixtures = io()->ask('Would you like to load fixtures?', 'y');
    if ($fixtures === 'y') {
        loadFixtures();
    }
    io()->newLine();
    io()->success('Database reset');
}

/*
 * Fixtures
 */

/**
 * Installs the Doctrine Fixtures Bundle and asks if you want to install FakerPHP.
 *
 * If you choose to install FakerPHP, it will ask you where you want to create your fixtures.
 * If the file does not exist, it will create it with a sample content.
 * If the file already exists, it will inform you that you can edit it to add your fixtures.
 *
 * @see https://symfony.com/doc/current/bundles/DoctrineFixturesBundle/index.html
 * @see https://fakerphp.org/
 */
#[AsTask(description: 'Install Fixtures Bundle', aliases: ['fixt:install'], namespace: 'fixtures')]
function installFixtures(): void
{
    io()->title('Installing Fixtures Bundle');
    run('composer require --dev doctrine/doctrine-fixtures-bundle');

    io()->newLine();
    $useFaker = io()->ask('Would you use FakerPHP?', 'y');

    if ($useFaker === 'y') {
        io()->section('Installing FakerPHP');
        run('composer require --dev fakerphp/faker');

        io()->newLine();
        $path = io()->ask('Where do you want to create your fixtures?', 'src/DataFixtures');

        if (!fs()->exists($path . '/AppFixtures.php')) {
            fs()->mkdir($path);
            fs()->touch($path . '/AppFixtures.php');

            $fixturesFileContent = <<<'EOF'
            <?php

            namespace App\DataFixtures;

            use Faker\Factory as Factory;
            use Doctrine\Persistence\ObjectManager;
            use Doctrine\Bundle\FixturesBundle\Fixture;

            class AppFixtures extends Fixture
            {
                public function load(ObjectManager $manager): void
                {
                    $faker = Factory::create('fr_FR');
                    // ...
                }
            }
            EOF;

            fs()->appendToFile($path . '/AppFixtures.php', $fixturesFileContent);
            io()->newLine();
            io()->info([
                '`' . $path . '/AppFixtures.php` created.',
                'Edit this file to add your fixtures.'
            ]);
        } else {
            io()->newLine();
            io()->info([
                '`' . $path . '/AppFixtures.php` already exists.',
                'Edit this file to add your fixtures.'
            ]);
        }
        io()->success('FakerPHP installed');
    }
}

/**
 * Load fixtures from the App\DataFixtures namespace to the database.
 *
 * @see https://symfony.com/doc/current/bundles/DoctrineFixturesBundle/index.html
 */
#[AsTask(description: 'Load Fixtures', aliases: ['fixt:load'], namespace: 'fixtures')]
function loadFixtures(): void
{
    io()->title('Loading Fixtures');
    run('symfony console doctrine:fixtures:load --no-interaction');
}
