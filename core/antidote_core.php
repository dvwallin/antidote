<?php

/*
 * Is the client using a terminal? Otherwise exit.
*/
    if ( PHP_SAPI !== 'cli' )
    {
        echo ( 'Not Running from CLI' );
        exit ( 1 );
    }

/**
 * Core class of Antidote which actually performs most
 * of the important tasks.
 */
class Antidote
{
    /**
     * Used to store allowed arguments which a user can pass
     * @var array
     */
        var $array_allowed_arguments;

    /**
     * Used to store required arguments which a user must supply
     * @var array
     */
        var $array_required_arguments;

    /**
     * The actual database connection will be stored in this variable
     * @var object
     */
        var $object_dbs;

    /**
     * A flag to see if we should utf8 decode our queries or not
     * depending on how we actually run antidote.
     * @var boolean
     */
        var $bool_no_decode = FALSE;

    /**
     * Used to store the arguments actually sent by a user
     * @var array
     */
        var $array_arguments;

    /*
     * __construct
     *
     * populate flag arrays with content from respective functions
    */
        function __construct ( )
        {
        }

        public function initialize ( $argv )
        {
            $this->array_allowed_arguments = $this->return_allowed_arguments ( );
            $this->array_required_arguments = $this->return_required_arguments ( $argv );
            $this->__init_folder_structure ( );
            $this->array_arguments = $this->check_for_argv ( [ 'array_argv' => $argv ] );
            return $this->array_arguments;
        }

    /*
     *
     * @brief Initialize the database interaction
     *
    */
        public function initialize_db ( )
        {

            /*
             * Loop through database attributes
             * and open the connection using
             * supplied parameters.
            */
            if ( array_key_exists ( 'database', $this->array_arguments ) || array_key_exists ( 'preset', $this->array_arguments ) )
            {
                /* Open database connection and assign it to our dbs object */
                $this->object_dbs = $this->open_database_connection ( $this->array_arguments );

                /* verify the existance of vital tables. */
                $this->vital_table_exists ( $this->array_arguments );
            }
        }

    /*
     *
     * @brief Echo a given message
     *
     * Echo a given message with appropriate linebreaks before and after
     *
     * @access public
     * @param array_args: string_msg, int_start_eol, int_end_eol
     * @return none
     *
    */
        function show_msg ( $array_args )
        {
            $array_args+= [ 'string_msg' => NULL, 'int_start_eol' => 1, 'int_end_eol' => 1 ];
            extract ( $array_args );
            elog ( $string_msg );
            for ( $s = 0; $s < $int_start_eol; $s++ )
            {
                echo ( PHP_EOL );
            }
            echo $string_msg;
            for ( $e = 0; $e < $int_end_eol; $e++ )
            {
                echo ( PHP_EOL );
            }
        }

    /**
     * Check if any arguments were passed. If not, post a warning.
     * @param  array $array_args
     * @return array
     */
        function check_for_argv ( $array_args )
        {
            $array_args+= [ 'array_argv' => array ( ) ];
            extract ( $array_args );

            if ( isset ( $array_argv ) && is_array ( $array_argv ) && count ( $array_argv ) > 1 )
            {
                $array_arguments = $this->build_argument_array ( $array_argv );
                if ( count ( $array_arguments ) < 1 )
                {
                    $this->show_msg ( [ 'string_msg' => 'Please run ' . SCRIPT_NAME . ' --help to see what arguments can, and must, be provided.' ] );
                    exit ( 1 );
                }
            } else
            {
                $this->show_msg ( [ 'string_msg' => 'Please run ' . SCRIPT_NAME . ' --help to see what arguments can, and must, be provided.' ] );
                exit ( 1 );
            }
            return $array_arguments;
        }

    /**
     * Returns an array of what arguments are a minimum requirement
     * to run Antidote.
     * @return array
     */
        function return_required_arguments ( $argv = array ( ) )
        {
            $array_required_arguments = array (
                '--database',
                '--branch',
                '--database_type'
            );
            preg_match ( "/database_type=(.+)/i", implode ( ' ', $argv ), $output_array );
            if ( count ( $output_array ) > 0 )
            {
                if ( $output_array [ 1 ] == "pgsql" )
                {
                    $array_required_arguments [ ] = '--database_schema';
                    $array_required_arguments [ ] = '--database_port';
                }
            }
            foreach ( $argv as $int_key => $string_value )
            {
                $array_temporary_exploded_string_value = explode ( '=', $string_value );
                $int_temporary_search_key = array_search ( $array_temporary_exploded_string_value [ 0 ] , $array_required_arguments );
                if ( $int_temporary_search_key !== FALSE )
                {
                    unset ( $array_required_arguments [ $int_temporary_search_key ] );
                }
            }
            if ( is_array ( $argv ) && count ( $argv ) > 0 )
            {
                foreach ( $argv as $string_argument )
                {
                    if ( preg_match ( "/--help/i", $string_argument ) )
                    {
                        return $array_required_arguments;
                        exit ( 0 );
                    }
                    if ( preg_match ( " /--preset/i", $string_argument ) )
                    {
                        return array ( );
                        exit ( 0 );
                    }
                }
                return $array_required_arguments;
                exit ( 0 );
            } else
            {
                return $array_required_arguments;
                exit ( 0 );
            }
        }

    /**
     * A raw array containing allowed arguments to be passed and a brief
     * description of each one.
     * @return array
     */
        function return_raw_allowed_arguments ( )
        {
            return array (
                array (
                    '--database',
                    'Set which database to use to connect to'
                ) ,
                array (
                    '--branch',
                    'Which branch do you want to handle? When listing you can use --branch=! to list all branchs'
                ) ,
                array (
                    '--goto',
                    'Which revision do you want to be the active one?'
                ) ,
                array (
                    '--reset',
                    'Undo all revisions of a branch'
                ) ,
                array (
                    '--reset_locks',
                    'Remove all lock files'
                ) ,
                array (
                    '--fastforward',
                    'Move to a revision without applying or undoing that revision or revisions on the way'
                ) ,
                array (
                    '--list',
                    'List revisions. If you give --branch=! you see a list per branch, if you give --branch=TableName you only see that branch'
                ) ,
                array (
                    '--export',
                    'Export a revision into ' . EXPORT_PATH
                ) ,
                array (
                    '--create',
                    'Create a new revision from a template. This will be in the order for the branch you supply'
                ) ,
                array (
                    '--nodecode',
                    'Don\'t ut8-decode the queries before running'
                ) ,
                array (
                    '--help',
                    'View this information'
                ) ,
                array (
                    '--about',
                    'View information about the project'
                ) ,
                array (
                    '--database_type',
                    'Database type ( mysql, pgsql ) '
                ) ,
                array (
                    '--database_schema',
                    'Database schema when pgsql '
                ) ,
                array (
                    '--database_host',
                    'Database hostname'
                ) ,
                array (
                    '--database_port',
                    'Database port'
                ) ,
                array (
                    '--database_username',
                    'Database username'
                ) ,
                array (
                    '--database_password',
                    'Database password'
                ) ,
                array (
                    '--noheader',
                    'Removes the headers of antidote. Useful for integration'
                ) ,
                array (
                    '--chain',
                    'Chain commands to run dividing them with a comma -sign'
                ) ,
                array (
                    '--nocolour',
                    'Remove all coloured output from Antidote'
                ) ,
                array (
                    '--noclear',
                    'Disable the terminal system clear'
                ) ,
                array (
                    '--notify',
                    'Notify on chains by emailing a chain log'
                ) ,
                array (
                    '--bench',
                    'Print benchmark numbers'
                ) ,
                array (
                    '--preset',
                    'Use a preset from presets.ini. Example --preset=MyPreset - This will override any command line arguments sent in.'
                ) ,
            );
        }

    /**
     * returns a structured version of our raw array of allowed arguments.
     * @return array
     */
        function return_allowed_arguments ( )
        {
            $raw_arguments = $this->return_raw_allowed_arguments ( );
            $array_flags = array ( );
            foreach ( $raw_arguments as $key => $value )
            {
                $array_flags [ ] = $value [ 0 ];
            }
            return $array_flags;
        }

    /**
     * Build the actual argument array for usage
     * @param  array $argv
     * @return array
     */
        function build_argument_array ( $argv )
        {
            $array_arguments = array ( );
            foreach ( $argv as $int_key => $string_value )
            {
                $string_value = strtr ( $string_value, array (
                    '\'' => '',
                    '" ' => '',
                    '|' => ''
                ) );
                $array_exploded_value = explode ( '=', $string_value );
                if ( in_array ( $array_exploded_value [ 0 ] , $this->array_allowed_arguments ) )
                {
                    $array_arguments [ ] = $array_exploded_value;
                } else
                {
                    $array_sub_exploded_value = explode ( '/', $array_exploded_value [ 0 ] );
                    if ( strtr ( $array_exploded_value [ 0 ] , array (
                        '/' => '',
                        '.' => '',
                        APP_PATH => ''
                    ) ) !== SCRIPT_NAME && end ( $array_sub_exploded_value ) !== SCRIPT_NAME )
                    {
                        $this->show_msg ( [ 'string_msg' => 'Please run ' . SCRIPT_NAME . ' --help to see what arguments can, and must, be provided.' ] );
                        exit ( 1 );
                    }
                }
            }
            if ( count ( $array_arguments ) > 0 && is_array ( $array_arguments ) )
            {
                return $this->restructure_argument_array ( $array_arguments );
            }
            return array ( );
        }

    /**
     * The supplied arguments needs to be restructured for
     * Antidote to handle them easier.
     * @param  array $array_arguments
     * @return array
     */
        function restructure_argument_array ( $array_arguments )
        {
            foreach ( $array_arguments as $key => $value )
            {
                if ( array_key_exists ( 1, $value ) )
                {
                    $string_check_against = $value [ 1 ];
                } else
                {
                    $string_check_against = NULL;
                }
                $array_returnation_array [ strtr ( $value [ 0 ] , array (
                    '--' => '',
                    '-' => ''
                ) ) ] = $string_check_against;
                if ( !array_key_exists ( DEF_PREFIX . strtr ( $value [ 0 ] , array (
                    '--' => '',
                    '-' => ''
                ) ) , get_defined_constants ( TRUE ) [ 'user' ] ) )
                {
                    define ( DEF_PREFIX . strtr ( $value [ 0 ] , array (
                        '--' => '',
                        '-' => ''
                    ) ) , $string_check_against );
                }
            }
            return $this->verify_required_arguments ( $array_returnation_array );
        }

    /**
     * Loops through supplied arguments and compare them to allowed and required.
     * @param  array $array_arguments
     * @return array
     */
        function verify_required_arguments ( $array_arguments )
        {
            if ( array_key_exists ( 'help', $array_arguments ) || array_key_exists ( 'about', $array_arguments ) )
            {
                return $array_arguments;
            }
            foreach ( $this->array_required_arguments as $key => $value )
            {
                if ( !array_key_exists ( strtr ( $value, array (
                    '--' => '',
                    '-' => ''
                ) ) , $array_arguments ) )
                {
                    $this->show_msg ( [ 'string_msg' => 'The following arguments have to be set:' . PHP_EOL . implode ( PHP_EOL, $this->array_required_arguments ) ] );
                    exit ( 1 );
                }
            }
            return $array_arguments;
        }

    /**
     * In order for Antidote to run properly it needs
     * certain pre-created directories. This is where we
     * check what folders are needed.
     * @return none
     */
        private function __init_folder_structure ( )
        {
            $array_dirs = array (
                VAULT_PATH,
                VAULT_PATH . 'rev',
                VAULT_PATH . 'verify',
                VAULT_PATH . 'exports',
                REV_PATH . 'generic',
                APP_PATH . 'logs',
            );
            if ( count ( $array_dirs ) < 1 )
            {
                $this->show_msg ( [ 'string_msg' => 'The array of vital directories is empty. Antidote can not run without certain directories.' ] );
                exit ( 1 );
            }
            foreach ( $array_dirs as $string_dir )
            {
                $this->vital_dir_exists ( $string_dir );
            }
        }

    /**
     * Checks if a folder exist. If it doesn't it
     * tries to create it. If that fails lets drop
     * a line to the user about it.
     * @param  string $dir
     * @return bool
     */
        function vital_dir_exists ( $dir )
        {
            if ( !is_dir ( $dir ) )
            {
                if ( !mkdir ( $dir, 0700 ) )
                {
                    $this->show_msg ( [ 'string_msg' => 'Apparently I cannot create " ' . $dir . '" which is critical for the function of Antidote' ] );
                    exit ( 1 );
                }
            }
            return TRUE;
        }

    /**
     * Antidote stores the current revision of a branch it a table
     * inside that very schema/database. This function checks for it
     * and creates it if it doesn't exist.
     * @return NULL or none
     */
        function vital_table_exists ( )
        {
            if ( array_key_exists ( 'help', $this->array_arguments ) )
            {
                return NULL;
            }
            $string_db_type = $this->array_arguments [ 'database_type' ];
            switch ( $string_db_type )
            {
                case 'sqlite':
                    $this->execute_queries ( [ 'array_queries_to_execute' => array (
                        'CREATE TABLE IF NOT EXISTS __dote (
                                    dote_id INTEGER PRIMARY KEY NOT NULL , 
                                    branch TEXT NOT NULL ,
                                    rev_id INTEGER NOT NULL ); '
                ) ] );
                break;
                case 'mysql':
                    $this->execute_queries ( [ 'array_queries_to_execute' => array (
                        'CREATE TABLE IF NOT EXISTS __dote (
                                    dote_id INT ( 11 ) UNSIGNED NOT NULL AUTO_INCREMENT,
                                    PRIMARY KEY ( dote_id ) ,
                                    branch VARCHAR ( 128 ) NOT NULL,
                                    rev_id INT ( 11 ) UNSIGNED NOT NULL ); '
                    ) ] );
                break;
                case 'pgsql':
                    $this->execute_queries ( [ 'array_queries_to_execute' => array (
                        'CREATE TABLE IF NOT EXISTS __dote (
                                    dote_id SERIAL,
                                    PRIMARY KEY ( dote_id ) ,
                                    branch CHARACTER VARYING ( 128 ) NOT NULL,
                                    rev_id BIGINT NOT NULL
                                ); '
                    ) ] );
                break;
                default:
                    $this->show_msg ( [ 'string_msg' => 'Unsupported db type ' . $string_db_type ] );
                    exit ( 1 );
            }
        }

    /*
     * @brief Set the SQL type
    */
        function sql_type ( )
        {
            if ( array_key_exists ( 'database_type', $this->array_arguments ) )
            {
                return $this->array_arguments [ 'database_type' ];
            }
            elog ( 'sql_type ( ) reports that database_type has not been set.' );
            return 'unknown';
        }

    /**
     * Open up a connection to the database requested by the user.
     * @param  array  $array_args
     * @return object
     */
        function open_database_connection ( $array_args = array ( ) )
        {
            if ( $this->array_arguments [ 'database_type' ] === 'pgsql' && ( !isset ( $this->array_arguments [ 'database_schema' ] ) || $this->array_arguments [ 'database_schema' ] === NULL ) )
            {
                $this->show_msg ( [ 'string_msg' => 'You have to set --database_schema if you are using postgresql.' ] );
                exit ( 1 );
            }
            if ( $this->sql_type ( ) === 'pgsql' )
            {
                $array_args+= [ 'database' => $this->array_arguments [ 'database' ] , 'database_type' => $this->array_arguments [ 'database_type' ] , 'database_schema' => $this->array_arguments [ 'database_schema' ] , 'database_host' => $this->array_arguments [ 'database_host' ] , 'database_username' => $this->array_arguments [ 'database_username' ] , 'database_password' => $this->array_arguments [ 'database_password' ] , ];
            } elseif ( $this->sql_type ( ) == 'mysql' )
            {
                $array_args+= [ 'database' => $this->array_arguments [ 'database' ] , 'database_type' => $this->array_arguments [ 'database_type' ] ,  'database_host' => $this->array_arguments [ 'database_host' ] , 'database_username' => $this->array_arguments [ 'database_username' ] , 'database_password' => $this->array_arguments [ 'database_password' ] , ];
            } elseif ( $this->sql_type ( ) == 'sqlite' )
            {
                $array_args+= [ 'database' => $this->array_arguments [ 'database' ] , 'database_type' => $this->array_arguments [ 'database_type' ] , ];
            }
            extract ( $array_args );
            $string_pdo_port = '';
            if ( !array_key_exists ( 'database_port', $this->array_arguments ) )
            {
                if ( $this->sql_type ( ) === 'pgsql' )
                {
                    $string_pdo_port = 'port=5432; ';
                } elseif ( $this->sql_type ( ) === 'mysql' )
                {
                    $string_pdo_port = 'port=3306; ';
                }
            } else
            {
                if ( $this->sql_type ( ) != 'sqlite' )
                {
                    $string_pdo_port = 'port=' . $this->array_arguments [ 'database_port' ] . '; ';
                }
            }
            try
            {
                if ( $this->sql_type ( ) == 'sqlite' )
                {
                    $object_dbh = new PDO ( 'sqlite://' . $database );
                } else {
                    $object_dbh = new PDO ( $database_type . ':host=' . $database_host . '; ' . $string_pdo_port . 'dbname=' . $database, $database_username, $database_password );
                }
            }
            catch ( Exception $e )
            {
                $this->show_msg ( [ 'string_msg' => 'Failed: ' . $e->getMessage ( ) ] );
                exit ( 1 );
            }
            return $object_dbh;
        }

    /**
     * Searches for a specif ic revision id in a certain branch.
     * @param  array $array_args
     * @return TRRUE or FALSE
     */
        function check_for_requested_revision ( $array_args )
        {
            $array_args+= [ 'string_branch' => NULL, 'int_revision_id' => 0, ];
            extract ( $array_args );

            if ( $string_branch === NULL )
            {
                $this->show_msg ( [ 'string_msg' => 'Could not find an argument for branch.' ] );
                exit ( 1 );
            }
            if ( $string_branch === '!' )
            {
                $string_branch = 'generic';
            }
            if ( file_exists ( REV_PATH . $string_branch . DS . $int_revision_id . '_' . $string_branch . REV_SUFFIX ) )
            {
                return TRUE;
            }
            return FALSE;
        }

    /**
     * Are we going up from a revision to another or down?
     * @param  array $array_args
     * @return string
     */
        function return_step_type ( $array_args )
        {
            $array_args+= [ 'string_branch' => NULL, 'int_requested_revision' => 0, ];
            extract ( $array_args );
            $current_revision = $this->return_current_revision_id ( [ 'string_branch_name' => $string_branch ] );
            $all_revision_files = $this->return_revision_files ( [ 'string_branch_name' => $string_branch ] );
            $steps_to_take = array ( );
            if ( $int_requested_revision < $current_revision )
            {
                $string_direction = 'undo';
            } elseif ( $current_revision === FALSE )
            {
                $string_direction = 'apply';
            } elseif ( $int_requested_revision > $current_revision )
            {
                $string_direction = 'apply';
            } else
            {
                $string_direction = 'none';
            }
            return $string_direction;
        }

    /**
     * What revisions do we want to apply or undo.
     * @param  array $array_args
     * @return array
     */
        function return_revisions_between ( $array_args )
        {
            $array_args+= [ 'string_branch' => NULL, 'int_requested_revision' => 0, ];
            extract ( $array_args );
            $current_revision = $this->return_current_revision_id ( [ 'string_branch_name' => $string_branch ] );
            $all_revision_files = $this->return_revision_files ( [ 'string_branch_name' => $string_branch ] );
            $all_revision_files = $all_revision_files [ $string_branch ];
            $steps_to_take = array ( );
            if ( $int_requested_revision < $current_revision )
            {
                $string_direction = 'down';
            } elseif ( $current_revision === FALSE )
            {
                $string_direction = 'up';
            } elseif ( $int_requested_revision > $current_revision )
            {
                $string_direction = 'up';
            } else
            {
                $string_direction = 'none';
            }
            switch ( $string_direction )
            {
                case 'up':
                    foreach ( $all_revision_files as $revision )
                    {
                        if ( $revision != $current_revision && $revision > $current_revision && $revision <= $int_requested_revision )
                        {
                            $steps_to_take [ ] = $revision;
                        }
                    }
                break;
                case 'down':
                    foreach ( $all_revision_files as $revision )
                    {
                        if ( $revision != $int_requested_revision && $revision <= $current_revision && $revision > $int_requested_revision )
                        {
                            $steps_to_take [ ] = $revision;
                        }
                    }
                    krsort ( $steps_to_take );
                break;
                case 'none':
                    return FALSE;
                break;
            }
            return $steps_to_take;
        }

    /**
     * Used to execute sql queries through a PDO transactions
     * @param  array $array_args
     * @return TRUE
     */
        function execute_queries ( $array_args )
        {
            $array_args+= [ 'array_queries_to_execute' => array ( ) , ];
            extract ( $array_args );
            if ( isset ( $string_last_query ) )
            {
                unset ( $string_last_query );
            }
            try
            {
                $this->object_dbs->setAttribute ( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
                $this->object_dbs->beginTransaction ( );
                if ( $this->array_arguments [ 'database_type' ] == 'pgsql' && $this->array_arguments [ 'database_schema' ] )
                {
                    array_unshift ( $array_queries_to_execute, 'set search_path to "' . $this->array_arguments [ 'database_schema' ] . '"; ' );
                }
                foreach ( $array_queries_to_execute as $string_query )
                {
                    $string_last_query = $string_query;
                    if ( $this->bool_no_decode )
                    {
                        $this->object_dbs->exec ( $string_query );
                    } else
                    {
                        $this->object_dbs->exec ( utf8_decode ( $string_query ) );
                    }
                }
                $this->object_dbs->commit ( );
            }
            catch ( Exception $e )
            {
                $this->object_dbs->rollBack ( );
                $this->show_msg ( [ 'string_msg' => 'Failed: ' . $e->getMessage ( ) . PHP_EOL . PHP_EOL . 'Query: ' . $string_last_query ] );
                exit ( 1 );
            }
            return TRUE;
        }

    /**
     * Retrieve which the highest revision ID is amongst every given branch
     * @param  array $array_args
     * @return int
     */
        function get_highest_revision_number ( $array_args )
        {
            $array_args+= [ 'string_branch_name' => 'generic', ];
            extract ( $array_args );
            $array_all_revision_files = glob ( REV_PATH . $string_branch_name . DS . '*.php' );
            if ( count ( $array_all_revision_files ) == 0 )
            {
                $int_revision_id_to_use = 1;
            } else
            {
                foreach ( $array_all_revision_files as $file_key => $file_value )
                {
                    $rev_value = str_replace ( array (
                        REV_PATH,
                        REV_PREFIX,
                        REV_SUFFIX,
                        $string_branch_name,
                        DS,
                        '_'
                    ) , '', $file_value );
                    if ( !ctype_digit ( $rev_value ) )
                    {
                        $this->show_msg ( [ 'string_msg' => 'Could not get the proper revision ID to use.' ] );
                        exit ( 1 );
                    }
                    $array_all_revision_files [ $file_key ] = $rev_value;
                }
                sort ( $array_all_revision_files );
                $int_revision_id_to_use = end ( $array_all_revision_files ) + 1;
            }
            return $int_revision_id_to_use;
        }

    /**
     * Used to find what steps to take when doing a reset
     * @param  array $array_args
     * @return array
     */
        function return_reset_revisions ( $array_args )
        {
            $array_args+= [ 'string_branch_name' => 'generic', ];
            extract ( $array_args );
            $array_returnation_content = array ( );
            if ( $string_branch_name == NULL )
            {
                $this->show_msg ( [ 'string_msg' => 'Branch need to be set.' ] );
                exit ( 1 );
            } else
            {
                if ( is_dir ( REV_PATH . $string_branch_name ) )
                {
                    $array_all_revision_files = $this->find_all_files ( [ 'string_directory' => REV_PATH . $string_branch_name ] );
                } else
                {
                    $this->show_msg ( [ 'string_msg' => 'That branch does not have any revisions as of yet.' ] );
                    exit ( 1 );
                }
            }
            if ( count ( $array_all_revision_files ) < 1 )
            {
                return FALSE;
            }
            $current_revision = $this->return_current_revision_id ( [ 'string_branch_name' => $string_branch_name ] );
            foreach ( $array_all_revision_files as $key => $value )
            {
                $exploded_value = explode ( '_', $value );
                if ( $exploded_value [ 0 ] <= $current_revision )
                {
                    $array_returnation_content [ ] = $exploded_value [ 0 ];
                }
            }
            if ( count ( $array_returnation_content ) > 0 )
            {
                rsort ( $array_returnation_content );
            }
            return $array_returnation_content;
        }

    /**
     * Fetches revision files of selected branch
     * @param  array $array_args
     * @return array
     */
        function return_revision_files ( $array_args )
        {
            $array_args+= [ 'string_branch_name' => NULL, ];
            extract ( $array_args );

            if ( $string_branch_name === NULL )
            {
                $array_all_revision_files = $this->find_all_files ( [ 'string_directory' => REV_PATH ] );
            } else
            {
                if ( is_dir ( REV_PATH . $string_branch_name ) )
                {
                    $array_all_revision_files = $this->find_all_files ( [ 'string_directory' => REV_PATH . $string_branch_name ] );
                } else
                {
                    $this->show_msg ( [ 'string_msg' => 'That branch does not have any revisions as of yet.' ] );
                    exit ( 1 );
                }
            }
            if ( count ( $array_all_revision_files ) < 1 )
            {
                return FALSE;
            }
            foreach ( $array_all_revision_files as $key => $value )
            {
                $exploded_value = explode ( '_', $value );
                $array_returnation_content [ $exploded_value [ 1 ] ] [ ] = $exploded_value [ 0 ];
            }
            ksort ( $array_returnation_content );
            return $array_returnation_content;
        }

    /**
     * Used to return the content of a single revision file
     * @param  array $array_args
     * @return array
     */
        function return_single_revision_file ( $array_args )
        {
            $array_args+= [ 'string_branch_name' => NULL, 'int_revision_id' => 0, ];
            extract ( $array_args );
            if ( $string_branch_name === NULL )
            {
                $this->show_msg ( [ 'string_msg' => 'Could not find an argument for branch.' ] );
                exit ( 1 );
            }
            if ( $string_branch_name === '!' )
            {
                $string_branch_name = 'generic';
            }
            if ( !file_exists ( REV_PATH . $string_branch_name . DS . $int_revision_id . '_' . $string_branch_name . REV_SUFFIX ) )
            {
                $this->show_msg ( [ 'string_msg' => 'That revision cannot be found in ' . REV_PATH ] );
                exit ( 1 );
            }

            /**
             * Include the selected revision ( s ) file ( s )
             */
            require_once ( REV_PATH . $string_branch_name . DS . $int_revision_id . '_' . $string_branch_name . REV_SUFFIX );

            $class_name = 'Migration_' . $string_branch_name . '_' . $int_revision_id;
            $revision_to_apply = New $class_name ( $this->object_dbs );
            $array_returnation = array (
                'apply' => $revision_to_apply->apply ( ) ,
                'undo' => $revision_to_apply->undo ( )
            );
            if ( method_exists ( $revision_to_apply, 'created' ) )
            {
                $array_returnation [ 'created' ] = $revision_to_apply->created ( );
            }
            if ( method_exists ( $revision_to_apply, 'desc' ) )
            {
                $array_returnation [ 'desc' ] = $revision_to_apply->created ( );
            }
            $array_returnation [ 'rev_object' ] = & $revision_to_apply;

            return $array_returnation;
        }

    /**
     * Retrieve an array of all the files inside a directory. This runs as a recursive function.
     * @param  array $array_args
     * @return array
     */
        function find_all_files ( $array_args )
        {
            $array_args+= [ 'string_directory' => NULL, ];
            extract ( $array_args );
            $string_root = scandir ( $string_directory );
            foreach ( $string_root as $string_value )
            {
                if (    $string_value === '.' ||
                        $string_value === '..' ||
                        $string_value === '.rev_lock.dote' ||
                        substr ( $string_value, 0, 1 ) === '.' ||
                        substr ( str_replace ( array ( REV_PATH, REV_PREFIX, REV_SUFFIX ) , '', $string_value ) , -4, 4 ) === '.php' )
                {
                    continue;
                }
                if ( is_file ( $string_directory . '/' . $string_value ) )
                {
                    $array_result [ ] = str_replace ( array ( REV_PATH, REV_PREFIX, REV_SUFFIX ) , '', $string_value );
                    continue;
                }
                foreach ( $this->find_all_files ( [ 'string_directory' => $string_directory . '/' . $string_value ] ) as $string_value )
                {
                    if ( $string_value != '.' && substr ( $string_value, 0, 1 ) != '.' && ctype_digit ( substr ( $string_value, 0, 1 ) ) )
                    {
                        $array_result [ ] = str_replace ( array ( REV_PATH, REV_PREFIX, REV_SUFFIX ) , '', $string_value );
                    }
                }
            }
            if ( isset ( $array_result ) && is_array ( $array_result ) && count( $array_result ) > 0 )
            {
                return $array_result;
            }
            return array ( );
        }

    /**
     * Used to execute validation queries
     * @param  array $array_args
     * @return array
     */
        function retrieve_results ( $array_args )
        {
            $array_args+= [ 'string_query' => NULL, ];
            extract ( $array_args );
            $array_returnation = array ( );
            foreach ( $this->object_dbs->query ( $string_query ) as $key => $array_value )
            {
                foreach ( $array_value as $subkey => $subvalue )
                {
                    if ( !ctype_digit ( $subkey ) )
                    {
                        $array_returnation [ $key ] [ $subkey ] = $subvalue;
                    }
                }
            }
            return $array_returnation;
        }

    /**
     * Get us the current revision of a certain branch
     * @param  array $array_args
     * @return int
     */
        function return_current_revision_id ( $array_args )
        {
            $array_args+= [ 'string_branch_name' => NULL, ];
            extract ( $array_args );
            $int_stored_revision_id = $this->retrieve_results ( [ 'string_query' => 'SELECT DISTINCT * FROM __dote WHERE branch=\'' . $string_branch_name . '\' LIMIT 1' ] );
            if ( count ( $int_stored_revision_id ) != 0 )
            {
                return $int_stored_revision_id [ 0 ] [ 'rev_id' ];
            } else
            {
                return 0;
            }
        }

    /**
     * If we send the flag not to utf-8 decode a query, set it here
     * @param bool $bool_no_decode
     */
        function set_no_decode ( $bool_no_decode )
        {
            $this->bool_no_decode = $bool_no_decode;
        }

    /**
     * @brief inserts tabs. foundation for multiplatform support
     */
        function insert_tab ( $int_quantity = 1 )
        {
            $string_return = "";
            for ( $int_count=0; $int_count < $int_quantity; $int_count++ )
            {
                $string_return .= "\t";
            }
            return $string_return;
        }

    /**
     * Used to create a new revision template based on the incremental
     * number of that branch.
     * @param  array $array_args
     * @return string
     */
        function create_new_revision_file ( $array_args )
        {
            $array_args+= [ 'string_branch_name' => NULL, ];
            extract ( $array_args );
            if ( $string_branch_name == NULL )
            {
                $this->show_msg ( [ 'string_msg' => 'Could not find an argument for branch.' ] );
                exit ( 1 );
            } elseif ( $string_branch_name === '!' )
            {
                $string_branch_name = 'generic';
            } else
            {
                $string_branch_name = $string_branch_name;
            }
            $this->vital_dir_exists ( REV_PATH . $string_branch_name );
            $int_revision_id_to_use = $this->get_highest_revision_number ( [ 'string_branch_name' => $string_branch_name ] );
            $filename = $int_revision_id_to_use . '_' . $string_branch_name . REV_SUFFIX;
            $file = REV_PATH . $string_branch_name . DS . $filename;
            if ( file_exists ( $file ) )
            {
                $this->show_msg ( [ 'string_msg' => $file . ' already exist.' ] );
                exit ( 1 );
            }
            $depend_on_older_id = $int_revision_id_to_use - 1;
            $content = " <?php if ( !defined ( 'SCRIPT_NAME' ) )
            { die ( \" Direct access to this file is useless\" ); } ";
            $content.= PHP_EOL . PHP_EOL . " class Migration_" . $string_branch_name . "_" . $int_revision_id_to_use . " extends Antidote_migration";
            $content.= PHP_EOL . "
            { ";
            $content.= PHP_EOL . $this->insert_tab ( ) . "protected \$object_dbh; ";
            $content.= PHP_EOL . PHP_EOL . $this->insert_tab ( ) . "public function apply ( ) ";
            $content.= PHP_EOL . $this->insert_tab ( ) . "
            { ";
            $content.= PHP_EOL . $this->insert_tab ( 2 ) . "return array ( ";
            $content.= PHP_EOL . $this->insert_tab ( 3 ) . "\" apply query here\" ";
            $content.= PHP_EOL . $this->insert_tab ( 2 ) . " ); ";
            $content.= PHP_EOL . $this->insert_tab ( ) . " } ";
            $content.= PHP_EOL . $this->insert_tab ( ) . " public function undo ( ) ";
            $content.= PHP_EOL . $this->insert_tab ( ) . "
            { ";
            $content.= PHP_EOL . $this->insert_tab ( 2 ) . " return array ( ";
            $content.= PHP_EOL . $this->insert_tab ( 3 ) . " \" undo query here\" ";
            $content.= PHP_EOL . $this->insert_tab ( 2 ) . " ); ";
            $content.= PHP_EOL . $this->insert_tab ( ) . " } ";
            $content.= PHP_EOL . $this->insert_tab ( ) . " public function post_hook_apply ( ) ";
            $content.= PHP_EOL . $this->insert_tab ( ) . "
            { ";
            $content.= PHP_EOL . $this->insert_tab ( 2 ) . " // hook called after successful apply";
            $content.= PHP_EOL . $this->insert_tab ( ) . " } ";
            $content.= PHP_EOL . $this->insert_tab ( ) . " public function post_hook_undo ( ) ";
            $content.= PHP_EOL . $this->insert_tab ( ) . "
            { ";
            $content.= PHP_EOL . $this->insert_tab ( 2 ) . " // hook called after successful undo";
            $content.= PHP_EOL . $this->insert_tab ( ) . " } ";
            $content.= PHP_EOL . $this->insert_tab ( ) . " public function desc ( ) ";
            $content.= PHP_EOL . $this->insert_tab ( ) . "
            { ";
            $content.= PHP_EOL . $this->insert_tab ( 2 ) . " return array ( ";
            $content.= PHP_EOL . $this->insert_tab ( 3 ) . " // 'Put a description within these single quotes and uncomment this line.'";
            $content.= PHP_EOL . $this->insert_tab ( 2 ) . " ); ";
            $content.= PHP_EOL . $this->insert_tab ( ) . " } ";
            $content.= PHP_EOL . $this->insert_tab ( ) . " public function created ( ) ";
            $content.= PHP_EOL . $this->insert_tab ( ) . "
            { ";
            $content.= PHP_EOL . $this->insert_tab ( 2 ) . " return array ( " . time ( ) . " ); ";
            $content.= PHP_EOL . $this->insert_tab ( ) . " } ";
            $content.= PHP_EOL . PHP_EOL . " } ";
            if ( !file_put_contents ( $file, $content ) )
            {
                $this->show_msg ( [ 'string_msg' => 'Could not write to ' . $file ] );
            }
            return $file;
        }
}
