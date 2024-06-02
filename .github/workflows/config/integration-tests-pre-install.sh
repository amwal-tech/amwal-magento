#!/bin/bash
echo "Adding dev packages for integration test"
composer require tddwizard/magento2-fixtures:^1.1 --no-update
composer require mockery/mockery:^1.6.11 --no-update

echo "Adding xdebug extension"
PHP_VERSION=$(php -r 'echo PHP_VERSION;')
if ! php -m | grep -i xdebug > /dev/null; then
    if [[ "$PHP_VERSION" == 7.4.* ]]; then
        echo "PHP version is 7.4.x, installing xdebug-2.9.8"
        pecl install xdebug-2.9.8
    else
        echo "PHP version is $PHP_VERSION, installing the latest xdebug version"
        pecl install xdebug
    fi
    docker-php-ext-enable xdebug
else
    echo "Xdebug is already installed"
fi

# Check if xdebug configuration exists
if ! grep -q "zend_extension=xdebug.so" /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini; then
    echo "Configuring xdebug"
    echo "
[xdebug]
zend_extension=xdebug.so
xdebug.mode=coverage
xdebug.start_with_request=yes
xdebug.discover_client_host=true
xdebug.client_host=host.docker.internal
xdebug.client_port=9003
xdebug.idekey=PHPSTORM
" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini
else
    echo "Xdebug configuration already exists"
fi

echo "export XDEBUG_MODE=coverage" >> ~/.bashrc

echo "Create coverage directory"
mkdir -p /home/coverage
ls -la /home/coverage

echo "current directory"
pwd

echo "Updating entrypoint.sh and phpunit.xml to include code coverage reporting..."
sed -i 's|-c phpunit.xml|-c phpunit.xml --coverage-cobertura=cobertura.xml|' ../../../entrypoint.sh
cp local-source/__extdn_github-actions-m2/.dev-tools/tests/integration/phpunit.xml ../../../docker-files/phpunit.xml
