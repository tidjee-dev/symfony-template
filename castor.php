<?php

use function Castor\fs;
use function Castor\io;
use function Castor\run;
use Castor\Attribute\AsTask;
use function Castor\load_dot_env;


/**
 * Load environment variables from a .env file.
 *
 * This function loads environment variables from a .env file into an array.
 *
 * To retrieve the value of a specific environment variable, you can use the array syntax:
 *     $varName = loadEnv(__DIR__.'/path/to/.env/file')['VAR_NAME'];
 *
 * @param string $path The path to the .env file.
 *
 * @return array<string, string> The environment variables loaded from the file.
 *
 * @throws RuntimeException If the file does not exist.
 */
function loadEnv(string $envPath): array
{
    if (!fs()->exists($envPath)) {
        throw new RuntimeException(sprintf('The file "%s" does not exist.', $envPath));
    }

    $env = load_dot_env($envPath);

    return $env;
}

/**
 ** Project
 */

/**
 * Creates a new Symfony project.
 *
 * This task initializes a new Symfony project in the current directory by using the Symfony skeleton.
 * It optionally sets up Git, Docker, and configures a basic web application structure.
 */
#[AsTask(description: 'Create new Symfony project', aliases: ['project:init'], namespace: 'project')]
function symfonyInit(): void
{
    io()->title('Symfony new project wizard');

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

    run('cp MODEL.env .env.docker');

    if (!fs()->exists('.git')) {
        io()->section('Initializing Git');
        $git = io()->confirm('Do you want to initialize Git in the project? ', false);
        if ($git) {
            run('git init');
            $remote = io()->confirm('Do you want to add a remote repository?', false);
            if ($remote) {
                $remoteUrl = io()->ask('What is the remote repository URL?');
                run('git remote add origin ' . $remoteUrl);
                io()->newLine();
                io()->info([
                    'Git initialized and remote repository added.',
                    'You can now push your code to the remote repository.'
                ]);
            } else {
                io()->newLine();
                io()->info([
                    'Git initialized.',
                    'You can now add your files and make the first commit.'
                ]);
            }

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
            if (!fs()->exists('compose.yml') || !fs()->exists('.docker')) {
                $docker = io()->confirm('Do you want to use Docker?', false);
                if ($docker) {
                    io()->section('Creating Docker configuration');
                    run('composer config --json extra.symfony.docker true');
                }
            }
            run('composer require webapp --no-progress --no-interaction');
        }
    }

    if (!fs()->exists('README.md')) {
        fs()->touch('README.md');
        fs()->appendToFile('README.md', '# ' . basename(getcwd()) . PHP_EOL);
    } else {
        fs()->copy('README.md', 'docs/template/README.md');
        fs()->dumpFile('README.md', '# ' . basename(getcwd()) . PHP_EOL);
    }

    io()->success([
        "Your new Symfony project is successfully created in " . getcwd(),
    ]);
    io()->info([
        "Run `castor` to see all available tasks",
    ]);
    io()->text([
        "To use Docker:",
        "1. Modify the compose.yml file to setup your Docker stack",
        "2. Run `castor docker:start` to start the Docker stack",
    ]);
    io()->comment([
        "Fell free to delete compose.yml and .docker/ folder if you don't want to use Docker",
    ]);
}

/**
 ** Composer
 */

/**
 * Installs composer dependencies.
 *
 * This task runs the `composer install` command to install all dependencies defined in the composer.json file.
 */
#[AsTask(description: 'Install composer dependencies', aliases: ['comp:install'], namespace: 'composer')]
function composerInstall(): void
{
    io()->title('Installing composer dependencies');
    run('composer install');
    io()->newLine();
    io()->success('Composer dependencies installed');
}

/**
 ** Docker
 */

/**
 * Starts the Docker stack.
 *
 * This task starts Docker containers defined in the `compose.yml` file using the environment file `.env.docker`.
 */
#[AsTask(description: 'Start Docker Stack', aliases: ['docker:start'], namespace: 'docker')]
function dockerStart(): void
{
    io()->title('Starting Docker Stack');
    run('docker compose --env-file .env.docker up -d');
    io()->newLine();
    io()->success('Docker Stack started');

    $app_port = loadEnv(__DIR__ . '/.env.docker')['APP_PORT'];
    $phpma_port = loadEnv(__DIR__ . '/.env.docker')['PHPMYADMIN_PORT'];
    $pgadmin_port = loadEnv(__DIR__ . '/.env.docker')['PGADMIN_PORT'];
    $mailpit_port = loadEnv(__DIR__ . '/.env.docker')['MAILPIT_HTTP_PORT'];

    $msg = [];

    if ($app_port) {
        $app_msg = 'You can now access your Symfony application at http://localhost:' . $app_port;
        $msg[] = $app_msg;
    }
    if ($phpma_port) {
        $phpma_msg = 'You can now access PHPMyAdmin at http://localhost:' . $phpma_port;
        $msg[] = $phpma_msg;
    }
    if ($pgadmin_port) {
        $pgadmin_msg = 'You can now access PgAdmin at http://localhost:' . $pgadmin_port;
        $msg[] = $pgadmin_msg;
    }

    if ($mailpit_port) {
        $mailpit_msg = 'You can now access Mailpit at http://localhost:' . $mailpit_port;
        $msg[] = $mailpit_msg;
    }

    io()->info($msg);
}

/**
 * Stops the Docker stack.
 *
 * This task stops the running Docker containers using the `compose.yml` file and the `.env.docker` environment file.
 */
#[AsTask(description: 'Stop Docker Stack', aliases: ['docker:stop'], namespace: 'docker')]
function dockerStop(): void
{
    io()->title('Stopping Docker Stack');
    run('docker compose --env-file .env.docker stop');
    io()->newLine();
    io()->success('Docker Stack stopped');
}

/**
 * Restarts the Docker stack.
 *
 * This task restarts the Docker containers using the `compose.yml` file and the `.env.docker` environment file.
 */
#[AsTask(description: 'Restart Docker Stack', aliases: ['docker:restart'], namespace: 'docker')]
function dockerRestart(): void
{
    io()->title('Restarting Docker Stack');
    run('docker compose --env-file .env.docker restart');
    io()->newLine();
    io()->success('Docker Stack restarted');

    $app_port = loadEnv(__DIR__ . '/.env.docker')['APP_PORT'];
    $phpma_port = loadEnv(__DIR__ . '/.env.docker')['PHPMYADMIN_PORT'];
    $pgadmin_port = loadEnv(__DIR__ . '/.env.docker')['PGADMIN_PORT'];
    $mailpit_port = loadEnv(__DIR__ . '/.env.docker')['MAILPIT_HTTP_PORT'];

    $msg = [];

    if ($app_port) {
        $app_msg = 'You can now access your Symfony application at http://localhost:' . $app_port;
        $msg[] = $app_msg;
    }
    if ($phpma_port) {
        $phpma_msg = 'You can now access PHPMyAdmin at http://localhost:' . $phpma_port;
        $msg[] = $phpma_msg;
    }
    if ($pgadmin_port) {
        $pgadmin_msg = 'You can now access PgAdmin at http://localhost:' . $pgadmin_port;
        $msg[] = $pgadmin_msg;
    }

    if ($mailpit_port) {
        $mailpit_msg = 'You can now access Mailpit at http://localhost:' . $mailpit_port;
        $msg[] = $mailpit_msg;
    }

    io()->info($msg);
}

/**
 * Removes the Docker stack.
 *
 * This task stops and removes all Docker services defined in the `compose.yml` file.
 * Optionally, it can also remove associated volumes.
 */
#[AsTask(description: 'Remove Docker Stack', aliases: ['docker:remove'], namespace: 'docker')]
function dockerRemove(): void
{
    io()->title('Removing Docker Stack');
    io()->info('This will remove all services defined in the compose.yml file.');
    $confirm = io()->confirm('Are you sure you want to remove this Docker Stack?', false);
    if ($confirm) {
        $volumes = io()->confirm('Do you want to remove volumes too?', false);
        if ($volumes) {
            run('docker compose --env-file .env.docker down --volumes');
            io()->newLine();
            io()->success('Docker Stack and volumes removed');
        } else {
            run('docker compose --env-file .env.docker down');
            io()->newLine();
            io()->success('Docker Stack removed');
        }
    } else {
        io()->warning('Docker Stack not removed');
    }
}

/**
 * Cleans the Docker environment.
 *
 * This task removes all unused Docker images, containers, networks, and optionally volumes.
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

/**
 ** Symfony
 */

/**
 * Clears the Symfony cache.
 *
 * This task runs the Symfony console command to clear the application cache.
 */
#[AsTask(description: 'Clear Cache', aliases: ['sf:cc'], namespace: 'symfony')]
function clearCache(): void
{
    io()->title('Clearing Cache');
    run('symfony console cache:clear');
}

/**
 ** Maker Bundle
 */

/**
 * Installs the Symfony Maker Bundle.
 *
 * This task installs the Maker Bundle to assist in code generation during development.
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
 * Creates a new Controller.
 *
 * This task invokes the Symfony console command to generate a new controller.
 */
#[AsTask(description: 'Create new Controller', aliases: ['make:controller'], namespace: 'maker')]
function makeController(): void
{
    io()->title('Creating new Controller');
    run('symfony console make:controller');
}

/**
 * Creates a new User.
 *
 * This task invokes the Symfony console command to generate a new user class.
 */
#[AsTask(description: 'Create new User', aliases: ['make:user'], namespace: 'maker')]
function makeUser(): void
{
    io()->title('Creating new User');
    run('symfony console make:user');
}

/**
 * Creates a new Entity.
 *
 * This task invokes the Symfony console command to generate a new entity class.
 */
#[AsTask(description: 'Create new Entity', aliases: ['make:entity'], namespace: 'maker')]
function makeEntity(): void
{
    io()->title('Creating new Entity');
    run('symfony console make:entity');
}

/**
 * Creates a new Form.
 *
 * This task invokes the Symfony console command to generate a new form class.
 */
#[AsTask(description: 'Create new Form', aliases: ['make:form'], namespace: 'maker')]
function makeForm(): void
{
    io()->title('Creating new Form');
    run('symfony console make:form');
}

/**
 ** Database
 */

/**
 * Creates a new database.
 *
 * This task creates the database if it does not exist, using Symfony's Doctrine command.
 */
#[AsTask(description: 'Create new Database', aliases: ['db:create'], namespace: 'database')]
function createDatabase(): void
{
    io()->title('Creating new Database');
    run('symfony console doctrine:database:create --if-not-exists');
}

/**
 * Drops the current database.
 *
 * This task forcefully drops the database using Symfony's Doctrine command.
 */
#[AsTask(description: 'Drop Database', aliases: ['db:drop'], namespace: 'database')]
function dropDatabase(): void
{
    io()->title('Dropping Database');
    run('symfony console doctrine:database:drop --force');
}

/**
 * Creates a new migration.
 *
 * This task generates a new migration file based on the current mapping information.
 */
#[AsTask(description: 'Create new Migration', aliases: ['db:migration'], namespace: 'database')]
function createMigration(): void
{
    io()->title('Creating new Migration');
    run('symfony console make:migration --no-interaction');
}

/**
 * Runs pending migrations.
 *
 * This task applies any pending Doctrine migrations.
 */
#[AsTask(description: 'Run Migrations', aliases: ['db:migrate'], namespace: 'database')]
function runMigrations(): void
{
    io()->title('Running Migrations');
    run('symfony console doctrine:migrations:migrate --no-interaction');
}

/**
 * Initializes the database.
 *
 * This task creates the database (if it doesn't exist), creates a migration,
 * applies migrations, and optionally loads fixtures.
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
 * Resets the database.
 *
 * This task drops the current database, recreates it, applies migrations,
 * and optionally loads fixtures.
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

/**
 ** Fixtures
 */

/**
 * Installs the Doctrine Fixtures Bundle.
 *
 * This task installs the fixtures bundle for loading test data and, optionally,
 * installs FakerPHP to assist in generating fake data.
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
 * Loads database fixtures.
 *
 * This task runs the Symfony console command to load fixtures into the database.
 */
#[AsTask(description: 'Load Fixtures', aliases: ['fixt:load'], namespace: 'fixtures')]
function loadFixtures(): void
{
    io()->title('Loading Fixtures');
    run('symfony console doctrine:fixtures:load --no-interaction');
}
