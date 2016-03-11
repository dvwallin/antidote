<?php

/*
 * Is the client using a terminal? Otherwise exit.
*/
    if ( PHP_SAPI !== 'cli' )
    {
        echo ( 'Not Running from CLI' );
        exit ( 1 );
    }

class Tools extends Antidote
{

    public function __construct ( )
    {
    }

    /*
     * @brief Check if the given preset does exist
     *
    */
        public function check_for_given_preset ( $array_args )
        {
            $array_args+= [ 'preset' => '', 'array_preset_ini' => array ( ) ];
            extract ( $array_args );

            if ( !preg_match ( "/\//i", $preset ) )
            {
                return 'The formatting of the preset was not correct. Please make sure you call the preset as: --preset=PRESET_TYPE/PRESET_NAME i.e: --preset=chains/MyChain';
                exit ( 1 );
            }
            $array_exploded_preset = explode ( '/', $preset );

            if ( !array_key_exists ( $array_exploded_preset [ 0 ] , $array_preset_ini ) )
            {
                return 'Could not find a section called \'' . $array_exploded_preset [ 0 ] . '\' in ' . CONFIG_PATH . PRESETS_FILE_NAME;
                exit ( 1 );
            }

            foreach ( $array_preset_ini as $string_key => $array_value )
            {
                if ( array_key_exists ( $array_exploded_preset [ 1 ] , $array_value ) )
                {
                    return $array_value [ $array_exploded_preset [ 1 ] ];
                    exit ( 0 );
                }
            }
            return 'Could not find  \'' . $array_exploded_preset [ 0 ] . '/' . $array_exploded_preset [ 1 ] . '\' as a preset in ' . CONFIG_PATH . PRESETS_FILE_NAME;
            exit ( 1 );
        }


    /*
     * @brief Turn the preset array structure into a usable argv structure
     *
    */
        public function convert_array_to_argv_structure ( $array_args )
        {
            $array_args+= [ 'array_selected_preset' => array ( ) ];
            extract ( $array_args );

            if ( is_array ( $array_selected_preset ) && count ( $array_selected_preset ) > 0 )
            {
                $array_return_values = array ( );
                foreach ( $array_selected_preset as $string_key => $string_value )
                {
                    if ( $string_value == '' || strlen ( $string_value ) < 1 )
                    {
                        $array_return_values [ ] = '--' . $string_key;
                    } else
                    {
                        $array_return_values [ ] = '--' . $string_key . '=' . $string_value;
                    }
                }
                return $array_return_values;
            }
            return array ( );
        }


    /*
     * @brief Check if the given preset does exist
     *
    */
        public function check_preset_for_arg ( $array_args )
        {
            $array_args+= [ 'string_argument' => '', 'array_arguments' => array ( ) ];
            extract ( $array_args );

            if ( !array_key_exists ( 'preset', $array_arguments ) )
            {
                return FALSE;
            }

            if ( strlen ( $string_argument ) < 1 )
            {
                return FALSE;
            }

            $array_preset_ini = parse_ini_file ( CONFIG_PATH . PRESETS_FILE_NAME, TRUE );

            if ( !is_array ( $msg = $this->check_for_given_preset ( [ 'preset' => $array_arguments [ 'preset' ] , 'array_preset_ini' => $array_preset_ini ] ) ) )
            {
                return $msg;
                exit ( 1 );
            } else
            {
                $array_selected_preset = $msg;
                 // If the above if statement failed it means the return was not a fail message in form of a string but the actual preset array.

            }

            if ( array_key_exists ( $string_argument, $array_selected_preset ) )
            {
                return TRUE;
            }

            return FALSE;
        }


    /*
     * @brief Check if the given preset does exist
     *
    */
        public function preset_array_to_string ( $array_arguments )
        {
            if ( is_array ( $array_arguments ) && count ( $array_arguments ) > 0 )
            {
                $array_return_values = array ( );
                foreach ( $array_arguments as $string_key => $string_value )
                {
                    if ( $string_value == '' || strlen ( $string_value ) < 1 )
                    {
                        $array_return_values [ ] = '--' . $string_key . '=' . NULL;
                    } else
                    {
                        $array_return_values [ ] = '--' . $string_key . '=' . $string_value;
                    }
                }
                return implode ( ' ', $array_return_values );
            }
            return '';
        }
}
