<?php

/*
 * Is the client using a terminal? Otherwise exit.
*/
if ( PHP_SAPI !== 'cli' )
{
    echo ( 'Not Running from CLI' );
    exit ( 1 );
}

/*
 * Set a default timezone in case the php.ini lacks this.
*/
date_default_timezone_set ( 'Europe/Berlin' );

/*
 * Set timeout limit to 1 hour for huge chained query-runs.
*/
set_time_limit ( 3600 );

/*
 * Instead of using a slash as directory separator
 * we wanna define DS to be the correct directory
 * separator for the specific OS used.
*/
$os = PHP_OS;
switch ( $os )
{
    case 'Linux':
        define ( 'DS', '/' );
        break;

    case 'Windows':
        define ( 'DS', '\\' );
        break;

    default:
        define ( 'DS', '/' );
        break;
}

/*
 * Directory definitions.
*/
define ( 'APP_PATH', rtrim ( str_replace ( 'config', '', __DIR__ ) , '/' ) . DS );
define ( 'ANTIDOTE_BIN', APP_PATH . 'antidote' );
define ( 'CONFIG_PATH', APP_PATH . 'config' . DS );
define ( 'CORE_PATH', APP_PATH . 'core' . DS );
define ( 'VAULT_PATH', APP_PATH . 'vault' . DS );
define ( 'REV_PATH', VAULT_PATH . 'rev' . DS );
define ( 'EXPORT_PATH', VAULT_PATH . 'exports' . DS );
define ( 'LOG_PATH', APP_PATH . 'logs' . DS );

/*
 * Prefix and Suffix of files.
*/
define ( 'REV_PREFIX', '' );
define ( 'REV_SUFFIX', '_dote.php' );
define ( 'DEF_PREFIX', 'AD_arg_' );
define ( 'PRESETS_FILE_NAME', 'presets.inc.ini' );

/*
 * Sets the version of Antidote.
*/
define ( 'VERSION', '1.1.' . sha1_file ( ANTIDOTE_BIN ) );

/*
 * Setting log-handling
*/
ini_set ( 'log_errors', 1 );
ini_set ( 'error_log', LOG_PATH . 'antidote.log' );
ini_set ( 'display_errors', 1 );

/*
 * Set email-addresses in this alias to inform av antidote run
*/
$email_targets = array (
    'sys@example.org'
);

define ( 'EMAIL_SENDER', 'antidote@example.org' );

define ( 'SMTP_SERVER', 'mail.example.org' );
define ( 'SMTP_PORT', '587' );
define ( 'SMTP_USERNAME', 'antidote@example.org' );
define ( 'SMTP_PASSWORD', 'p@55w0rd' );
define ( 'SMTP_TIMEOUT', 30 );

/*
 * Error logging function
*/
function elog ( $string_message = '', $exclude_error_message = 0 )
{
    if ( $exclude_error_message == 1 )
    {
        error_log ( $string_message, 0 );
    } else
    {
        error_log ( 'Antidote : ' . $string_message, 0 );
    }
    return $string_message;
}

