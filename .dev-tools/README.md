<p align="center">
  <a href="https://amwal.tech/?utm_source=github&utm_medium=logo" target="_blank">
    <img src="https://uploads-ssl.webflow.com/62294ce746440b7bc08b4fc5/624352eb48193d537d329386_1-2-p-500.png" alt="Amwal" width="180" height="60">
  </a>
</p>

# Amwal dev tools

This repository contains the dev tools used by Amwal team.

## Plugins used:

- https://github.com/vimeo/psalm
- https://github.com/phpstan/phpstan
- https://github.com/php-cs-fixer/shim
- https://github.com/ergebnis/composer-normalize
- https://github.com/kubawerlos/composer-smaller-lock
- https://github.com/nektos/act

## Act for Phpstorm

### Setting Up External Tools:

1. Go to Settings > Tools > External Tools.
2. Click + to add a new tool.
3. Name it (`GitHub Actions (act)`).
4. Set Program to your `act` executable path or just act if it's in your system `PATH`.
5. Set Arguments to the commands you typically use, like `-P ubuntu-latest=catthehacker/ubuntu:act-latest`.
6. Optionally, set the Working directory to `$ProjectFileDir$/app/code/Amwal/Payments/`.
7. Now, you can run act from Tools > External Tools > GitHub Actions (act).


## Act for VSCode

### Setting Up Task Configuration:

1. Go to the` .vscode` directory in your project and create a `tasks.json` file if it doesn't exist.
2. Define a task for `act`. Here’s:

```json
{
    "version": "2.0.0",
    "tasks": [
      {
        "label": "Run GitHub Actions",
        "type": "shell",
        "command": "act",
        "args": ["-P ubuntu-latest=catthehacker/ubuntu:act-latest"],
        "problemMatcher": [],
        "group": {
          "kind": "build",
          "isDefault": true
        }
      }
    ]
  }
```
3. Run this task through Terminal > Run Task > Run GitHub Actions.


## Running unit test locally (through DDEV)

### Prerequisites
1. A local Magento setup running in DDEV
2. The Amwal payments plugin installed through composer

### Running the Unit test
To run the test navigate to the root of your project and run the following command:
```shell
ddev exec "vendor/bin/phpunit -c vendor/amwal/payments/.dev-tools/tests/unit/phpunit.xml"
```

## Running integration test locally (through DDEV)

### Prerequisites
1. A local Magento setup running in DDEV
2. The Amwal payments plugin installed through composer

### Copy required files
To ensure the right tests are executed and the configuration is set correctly please copy the following files:

```shell
cp vendor/amwal/payments/.dev-tools/tests/integration/phpunit.xml dev/tests/integration/
```

```shell
cp vendor/amwal/payments/.dev-tools/tests/integration/etc/install-config-mysql.php dev/tests/integration/etc/
```

### Running the Unit test
To run the test navigate to the root of your project and run the following command:
```shell
ddev exec "cd dev/tests/integration && ../../../vendor/bin/phpunit -c phpunit.xml"
```

#### Note
Running the integration test will drop your database and re-install Magento. If you would like to preserve your existing data create a separate database for testing and adjust the credentials in [install-config-mysql.php](tests%2Fintegration%2Fetc%2Finstall-config-mysql.php) before copying it to the dev/tests/integration/etc folder.
