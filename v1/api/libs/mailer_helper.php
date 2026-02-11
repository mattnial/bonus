<?php
// ARCHIVO: api/libs/mailer_helper.php

// Apuntamos a la carpeta 'src' que viste en tu imagen
require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class Mailer {
    private $mail;

    public function __construct() {
        $this->mail = new PHPMailer(true);
        try {
            // --- TUS DATOS DEL SERVIDOR DE CORREO (SMTP) ---
            $this->mail->isSMTP();
            $this->mail->Host       = 'mail.vilcanet.com.ec'; // Tu Host (búscalo en cPanel > Email)
            $this->mail->SMTPAuth   = true;
            $this->mail->Username   = 'notificaciones@vilcanet.com.ec'; // Tu correo real
            $this->mail->Password   = 'macaN2522@'; // La contraseña de ese correo
            $this->mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS; // Usa SSL
            $this->mail->Port       = 465; // Puerto SSL estándar

            // Remitente que verá el cliente
            $this->mail->setFrom('notificaciones@vilcanet.com.ec', 'Soporte Vilcanet');
            $this->mail->CharSet = 'UTF-8';
            $this->mail->isHTML(true);

        } catch (Exception $e) {
            // Si falla la configuración, guardamos el error en un log silencioso
            error_log("Error Mailer Config: " . $this->mail->ErrorInfo);
        }
    }

    public function sendTicketCreated($toEmail, $clientName, $ticketId, $subject, $priority) {
        if (empty($toEmail)) return false; 

        try {
            $this->mail->addAddress($toEmail, $clientName);
            $this->mail->Subject = "Ticket #$ticketId Creado - Vilcanet";

            // Diseño del correo (HTML)
            $body = "
                <div style='font-family: Arial, sans-serif; max-width: 600px; margin: auto; border: 1px solid #e0e0e0; border-radius: 10px; overflow: hidden;'>
                    <div style='background-color: #1e3a8a; padding: 20px; text-align: center; color: white;'>
                        <h1 style='margin: 0;'>Nuevo Ticket de Soporte</h1>
                    </div>
                    <div style='padding: 20px;'>
                        <p>Estimado/a <strong>$clientName</strong>,</p>
                        <p>Hemos registrado su solicitud de soporte exitosamente.</p>
                        
                        <div style='background-color: #f3f4f6; padding: 15px; border-radius: 5px; margin: 20px 0; border-left: 4px solid #2563eb;'>
                            <p style='margin:5px 0'><strong>Ticket:</strong> #$ticketId</p>
                            <p style='margin:5px 0'><strong>Asunto:</strong> $subject</p>
                            <p style='margin:5px 0'><strong>Prioridad:</strong> $priority</p>
                        </div>

                        <p>Nuestro equipo técnico ya está trabajando en su caso.</p>
                    </div>
                    <div style='background-color: #f9fafb; padding: 15px; text-align: center; font-size: 12px; color: #6b7280;'>
                        Vilcanet ISP - Telecomunicaciones
                    </div>
                </div>
            ";

            $this->mail->Body = $body;
            $this->mail->send();
            return true;
        } catch (Exception $e) {
            error_log("Error enviando correo: " . $this->mail->ErrorInfo);
            return false;
        }
    }
}
?>