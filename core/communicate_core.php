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
 * The communicate class is used for notification functions and third-party interactions.
 */
class Communicate extends Antidote
{
    function __construct ( )
    {
    }

    /*
     *
     * @brief Notify someone by email
     *
     * @access public
     * @param array_args: string_msg, int_start_eol, int_end_eol
     * @return none
     *
    */
    function notify ( $array_args )
    {
        $array_args+= [ 'string_target_email' => NULL, 'string_subject' => 'Chain execusion report ' . date ( 'l jS \of F Y h:i:s A' ) , 'string_message' => 'There were no message specified', 'string_outgoing_mailer' => EMAIL_SENDER, ];
        extract ( $array_args );
        $to = $string_target_email;
        $from = $string_outgoing_mailer;
        $name_from = 'Antidote @ ' . gethostname ( );
        $subject = $string_subject;
        $message = $string_message;
        if ( $string_target_email !== NULL )
        {
            if ( !is_file( CORE_PATH . 'phpmailer/class.phpmailer.php' ) )
            {
                elog ( 'Could not find ' . CORE_PATH . 'phpmailer/class.phpmailer.php and the notify function will therefor not work.' );
                exit( 1 );
            }
            require_once ( CORE_PATH . 'phpmailer/class.phpmailer.php' );
            $mail = new PHPMailer ( );
            $mail->IsSMTP ( );
            $mail->SMTPDebug = 0;
            $mail->SMTPAuth = TRUE;
            $mail->SMTPSecure = "tls";
            $mail->Host = SMTP_SERVER;
            $mail->Port = SMTP_PORT;
            $mail->Username = SMTP_USERNAME;
            $mail->Password = SMTP_PASSWORD;
            $mail->CharSet = 'utf-8';
            $mail->SetFrom ( $string_outgoing_mailer, 'Antidote @ ' . gethostname ( ) );
            $mail->Subject = $string_subject;
            $mail->ContentType = 'text/plain';
            $mail->IsHTML ( FALSE );
            $mail->Body = $string_message;
            $mail->AddAddress ( $string_target_email );
            if ( !$mail->Send ( ) )
            {
                $error_message = "Mailer Error: " . $mail->ErrorInfo;
            } else
            {
                $error_message = "Successfully sent!";
            }
        }
        return TRUE;
    }
}
