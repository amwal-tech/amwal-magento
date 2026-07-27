<?php
declare(strict_types=1);

require_once dirname(__DIR__, 4) . '/dev/tests/unit/framework/bootstrap.php';

// Override Magento's strict error handler to ignore deprecation warnings on newer PHP versions
set_error_handler(
    function ($errNo, $errStr, $errFile, $errLine) {
        $ignoredErrors = [E_DEPRECATED, E_USER_DEPRECATED];
        if (PHP_VERSION_ID < 80400 && defined('E_STRICT')) {
            $ignoredErrors[] = E_STRICT;
        }
        if (in_array($errNo, $ignoredErrors)) {
            return true; // Ignore deprecations
        }
        $errLevel = error_reporting();
        if (($errLevel & $errNo) !== 0) {
            $errorNames = [
                E_ERROR => 'Error',
                E_WARNING => 'Warning',
                E_PARSE => 'Parse',
                E_NOTICE => 'Notice',
                E_CORE_ERROR => 'Core Error',
                E_CORE_WARNING => 'Core Warning',
                E_COMPILE_ERROR => 'Compile Error',
                E_COMPILE_WARNING => 'Compile Warning',
                E_USER_ERROR => 'User Error',
                E_USER_WARNING => 'User Warning',
                E_USER_NOTICE => 'User Notice',
                E_RECOVERABLE_ERROR => 'Recoverable Error',
            ];
            $errName = $errorNames[$errNo] ?? 'Unknown Error';
            throw new \PHPUnit\Framework\Exception(
                sprintf("%s: %s in %s:%s.", $errName, $errStr, $errFile, $errLine),
                $errNo
            );
        }
        return false;
    }
);
