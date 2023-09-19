<?php
// Path to your Magento installation (update this)
define('MAGENTO_ROOT', '/var/www/html');

// Include Magento bootstrap
require MAGENTO_ROOT . '/app/bootstrap.php';

// Initialize the Magento application
$bootstrap = \Magento\Framework\App\Bootstrap::create(BP, $_SERVER);
$app = $bootstrap->createApplication(\Magento\Framework\App\Http::class);
$bootstrap->run($app);

// Instantiate the cron job class
$pendingOrdersUpdate = $bootstrap->getObjectManager()->create(\Amwal\Payments\Cron\PendingOrdersUpdate::class);

// Execute the cron job
$pendingOrdersUpdate->execute();

echo "Cron job executed successfully.\n";
