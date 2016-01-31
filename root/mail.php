<?php

require __DIR__ . '/vendor/autoload.php';

$mail = new PHPMailer;

//$mail->SMTPDebug = 3;                               // Enable verbose debug output

$mail->isSMTP();                                      // Set mailer to use SMTP
$mail->Host = 'email-smtp.us-east-1.amazonaws.com';   // Specify main and backup SMTP servers
$mail->SMTPAuth = true;                               // Enable SMTP authentication
$mail->Username = 'AKIAJV3FTVKKHHAHE4YQ';             // SMTP username
$mail->Password = 'ArnoeqtqrnOBa/q6fDICu+vYlwYHr3/bmCv6XnSMSvrP';                           // SMTP password
$mail->SMTPSecure = 'tls';                            // Enable TLS encryption, `ssl` also accepted
$mail->Port = 587;

$mail->setFrom('support@bearcatexchange.com', 'Bearcat Exchange');
$mail->addAddress('nferrara100@gmail.com', 'Nick');
//$mail->addReplyTo('info@example.com', 'Information');
$mail->isHTML(true);

$mail->Subject = 'Hey There';
$mail->Body    = 'This is the HTML message body <b>in bold!</b>';

if(!$mail->send()) {
    echo 'Message could not be sent.';
    echo 'Mailer Error: ' . $mail->ErrorInfo;
} else {
    echo 'Message has been sent';
}

?>
