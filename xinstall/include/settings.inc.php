<?php

	error_reporting(0);
	error_reporting(E_ERROR | E_WARNING | E_PARSE);

    // -------------------------------------------------------------------------
    // 1. GENERAL SETTINGS
    // -------------------------------------------------------------------------
    
    // Version number of Social Auto Poster
    define('SAP_VERSION', '4.2.2');            

    // Check for PHP minimum version number (true, false)
    define('SAP_CHECK_PHP_MINIMUM_VERSION', true);
    
    // Checks if a minimum required version of PHP runs on a server    
    define('SAP_PHP_MINIMUM_VERSION', '5.0.0');  
    
    // -------------------------------------------------------------------------
    // 2. DATABASE SETTINGS
    // -------------------------------------------------------------------------       

    // *** define database type
    // *** to check installed drivers use: print_r(PDO::getAvailableDrivers());
    //     mysql          - MySql,
    //     pgsql          - PostgreSQL
    //     sqlite/sqlite2 - SQLite 
    //     oci            - Oracle
    //     cubrid         - Cubrid
    //     firebird       - Firebird/Interbase 6
    //     dblib          - FreeTDS / MSSQL / Sybase
    //     sqlsrv         - Microsoft SQL Server 
    //     ibm            - IBM DB2
    //     informix       - IBM Informix Dynamic Server
    //     odbc           - ODBC v3 (IBM DB2, unixODBC and win32 ODBC)
    define('SAP_DATABASE_TYPE', 'mysql');

    // *** check for database engine minimum version number (true, false) -
    //     checks if a minimum required version of database engine runs on a server
    define('SAP_CHECK_DB_MINIMUM_VERSION', true);
    define('SAP_DB_MINIMUM_VERSION', '4.0.0');
    
    // -------------------------------------------------------------------------
    // 3. CONFIG PARAMETERS & Templates
    // -------------------------------------------------------------------------
    
    // *** config file directory - directory, where config file must be created
    //     for ex.: '../common/' or 'common/' - according to directory hierarchy and relatively to index.php file
    define('SAP_CONFIG_FILE_DIRECTORY', '../');
    
    // config file name - output file with config parameters (database, username etc.)
    define('SAP_CONFIG_FILE_NAME', 'mingle-config.php');

    // according to directory hierarchy (you may add/remove '../' before SAP_CONFIG_FILE_DIRECTORY)
    define('SAP_CONFIG_FILE_PATH', SAP_CONFIG_FILE_DIRECTORY.SAP_CONFIG_FILE_NAME);
    
    // config file name - config template file name
    define('SAP_CONFIG_FILE_TEMPLATE', 'config.tpl');
    
    // *** sql dump file - file that includes SQL statements for instalation
    define('SAP_SQL_DUMP_FILE_CREATE', 'sql_dump/create.sql');

    // *** defines using of utf-8 encoding and collation for SQL dump file
    define('SAP_USE_ENCODING', true);
    define('SAP_DUMP_FILE_ENCODING', 'utf8');
    define('SAP_DUMP_FILE_COLLATION', 'utf8_unicode_ci');                     
    
    // -------------------------------------------------------------------------
    // 6. APPLICATION PARAMETERS
    // -------------------------------------------------------------------------
    
    // *** default start file name - application start file
    define('SAP_APPLICATION_START_FILE', 'index.php');    

    define('SAP_NAME', 'Media Suite');
?>