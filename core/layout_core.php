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
 * The layout class is used for most layout presentation in Antidotes cli interface.
 */
class Layout extends Antidote
{
        function __construct ( )
        {
        }

    /**
     * Get the line width of the terminal and replace that width with characters
     * @param  array  $array_args: int_minus, string_char, string_line, bool_half
     * @return string
     */
        function get_line ( array $array_args )
        {
            $array_args+= [ 'int_minus' => 0, 'string_char' => '-', 'string_line' => '', 'bool_half' => FALSE ];
            extract ( $array_args );

            if ( exec ( 'tput cols' ) != 0 && exec ( 'tput cols' ) != FALSE )
            {
                $int_cols = exec ( 'tput cols' );
            } else
            {
                $int_cols = 25;
            }
            if ( $bool_half === TRUE )
            {
                $int_cols = $int_cols / 2;
            } else
            {
                $int_cols = $int_cols;
            }
            for ( $i = 0; $i < ( $int_cols - $int_minus ); $i++ )
            {
                $string_line.= $string_char [ 0 ];
            }
            return $string_line;
        }

    /*
     *
     * @brief Print given content
     *
     * If we supply the no-colour flag we should not use colours for printing content
     *
     * @access public
     * @param array_args: string_msg, string_colour
     * @return none
     *
    */
        public function return_formatted_content ( $array_args )
        {
            $array_args+= [ 'string_msg' => NULL, 'string_colour' => 1, 'int_mark' => 0 ];
            extract ( $array_args );
            if ( array_key_exists ( DEF_PREFIX . 'nocolour', get_defined_constants ( TRUE ) [ 'user' ] ) )
            {
                if ( $int_mark === 1 )
                {
                    return '-' . $string_msg . '-';
                } else
                {
                    return $string_msg;
                }
            } else
            {
                if ( class_exists ( 'cli\Colors' ) )
                {
                    return \cli\Colors::colorize ( $string_colour . $string_msg . '%n', TRUE );
                }
                return $string_msg;
            }
        }

    /**
     * @brief header draws up the header-part of antidote when in cli mode
     * @param  array $argv
     * @return string
     * @access public
     */
        function header ( $argv )
        {
            if ( !array_key_exists ( DEF_PREFIX . 'noclear', get_defined_constants ( TRUE ) [ 'user' ] ) )
            {
                system ( 'clear' );
            }
            $string_line = $this->get_line ( [ 'int_minus' => strlen ( $this->return_ascii_logotype ( ) . "\nVersion " . VERSION ) , 'string_char' => ' ', 'bool_half' => TRUE ] );
            $string_returnation = $string_line;
            $string_returnation.= $this->return_formatted_content ( [ 'string_msg' => $this->return_ascii_logotype ( ) . "Version " . VERSION, 'string_colour' => '%Y' ] );
            $string_returnation.= $string_line . PHP_EOL;
            $string_returnation.= $this->return_formatted_content ( [ 'string_msg' => $this->get_line ( [ 'int_minus' => 1, 'string_char' => '-' ] ) , 'string_colour' => '%R' ] );
            return $string_returnation . PHP_EOL;
        }

    /**
     * footer draws up the end-part of the antidote cli interface
     * @return string
     * @access public
     */
        function footer ( )
        {
            return $this->return_formatted_content ( [ 'string_msg' => $this->get_line ( [ 'int_minus' => 1, 'string_char' => '-' ] ) , 'string_colour' => '%R' ] ) . PHP_EOL;
        }

    /**
     * Simply returns the Antidote cli logotype as graphics cannot be presented
     * @return string
     * @access public
     */
        function return_ascii_logotype ( )
        {
            $string_logotype = "          ___    __   __  ___  ___" . PHP_EOL;
            $string_logotype.= " /\  |\ |  |  | |  \ /  \  |  |__  " . PHP_EOL;
            $string_logotype.= "/~~\ | \|  |  | |__/ \__/  |  |___ " . PHP_EOL . PHP_EOL;
            return $string_logotype;
        }
}
