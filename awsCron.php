<?php

include_once("aws.php");
include_once("PHPMailer/PHPMailerAutoload.php");

$response = checkForAlerts();
if ($response == null) {
    die();
}


$mail = new PHPMailer;
$mail->IsHTML(true);
$mail->isSMTP();
$mail->SMTPOptions = array(
'ssl' => array(
'verify_peer' => false,
'verify_peer_name' => false,
'allow_self_signed' => true
)
);
$mail->SMTPDebug = 0;
$mail->Host = 'smtp.gmail.com';  // Specify main and backup SMTP servers
$mail->SMTPAuth = true;                               // Enable SMTP authentication
$mail->Username = 'rybelllc@gmail.com';                 // SMTP username
$mail->Password = 'pzaswwaodepzefgw';                           // SMTP password
$mail->SMTPSecure = 'tls';                            // Enable TLS encryption, `ssl` also accepted
$mail->Port = 587;

$mail->From = 'rybelllc@gmail.com';
$mail->FromName = "Command Center";
$mail->addAddress('ryan.cobelli@gmail.com');

$mail->Subject = "URGENT: AWS Monitoring Alert";
$mail->Body = $response;
if (!$mail->send()) {
    echo $mail->ErrorInfo;
}