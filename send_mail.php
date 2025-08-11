<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/PHPMailer.php';
require 'PHPMailer/SMTP.php';
require 'PHPMailer/Exception.php';

function sendMail($to, $name, $subject, $body) {
    $mail = new PHPMailer(true);
    try {
        // Configuration SMTP Gmail
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'kyliyanisse@gmail.com';
        $mail->Password = 'benw vbiu hhrw mjgi';
        $mail->SMTPSecure = 'tls';
        $mail->Port = 587;
        $mail->SMTPDebug = 0; // Désactiver le débogage en production

        // Infos expéditeur et destinataire
        $mail->setFrom('kyliyanisse@gmail.com', 'Patrimoine Plus');
        $mail->addAddress($to, $name);

        // Contenu de l’email
        $mail->isHTML(true);
        $mail->CharSet = 'UTF-8';
        $mail->Subject = $subject;
        $mail->Body = $body;

        $mail->send();
        return ['success' => true];
    } catch (Exception $e) {
        error_log('Erreur d’envoi de mail : ' . $mail->ErrorInfo);
        return ['success' => false, 'error' => $mail->ErrorInfo];
    }
}
?>