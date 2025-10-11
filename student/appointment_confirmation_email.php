<?php
require_once __DIR__ . '/../phpmailer/vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

function sendAppointmentConfirmationEmail($email, $studentName, $appointmentDetails) {
    $mail = new PHPMailer(true);
    
    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'chowxinnlu@gmail.com';
        $mail->Password   = 'dkrnuziewiqvdewe'; 
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;
        
        $mail->setFrom('chowxinnlu@gmail.com', 'Evergreen Academy - Guidance Office');
        $mail->addAddress($email, $studentName);
        
        $mail->isHTML(true);
        $mail->Subject = 'Appointment Confirmation - Evergreen Academy';
        
        // Format date and time
        $appointmentDate = date("F j, Y", strtotime($appointmentDetails['date']));
        $appointmentTime = date("h:i A", strtotime($appointmentDetails['time']));
        
        // Urgency badge color
        $urgencyColors = [
            'Low' => '#28a745',
            'Medium' => '#ffc107',
            'High' => '#dc3545'
        ];
        $urgencyColor = $urgencyColors[$appointmentDetails['urgency_level']] ?? '#6c757d';
        
        $mail->Body = "
            <html>
            <head>
                <style>
                    body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; margin: 0; padding: 0; }
                    .container { max-width: 600px; margin: 0 auto; padding: 20px; background-color: #ffffff; }
                    .header { background: linear-gradient(135deg, #28a745, #20c997); color: white; padding: 30px 20px; text-align: center; border-radius: 10px 10px 0 0; }
                    .header h1 { margin: 0; font-size: 24px; }
                    .content { background-color: #f9f9f9; padding: 30px; border-radius: 0 0 10px 10px; }
                    .greeting { font-size: 18px; font-weight: bold; margin-bottom: 15px; color: #333; }
                    .appointment-box { background-color: white; padding: 20px; border-radius: 8px; margin: 20px 0; border-left: 4px solid #28a745; }
                    .detail-row { display: flex; padding: 10px 0; border-bottom: 1px solid #e0e0e0; }
                    .detail-row:last-child { border-bottom: none; }
                    .detail-label { font-weight: bold; width: 150px; color: #666; }
                    .detail-value { color: #333; flex: 1; }
                    .urgency-badge { display: inline-block; padding: 5px 15px; border-radius: 20px; font-size: 12px; font-weight: bold; color: white; background-color: " . $urgencyColor . "; }
                    .info-box { background-color: #e7f3ff; padding: 20px; border-radius: 8px; border-left: 4px solid #0066cc; margin: 20px 0; }
                    .info-title { font-weight: bold; color: #0066cc; margin-bottom: 10px; font-size: 16px; }
                    .status-pending { background-color: #fff3cd; color: #856404; padding: 10px 20px; border-radius: 8px; text-align: center; font-weight: bold; margin: 20px 0; }
                    .footer { text-align: center; padding: 20px; font-size: 12px; color: #777; border-top: 1px solid #e0e0e0; margin-top: 30px; }
                </style>
            </head>
            <body>
                <div class='container'>
                    <div class='header'>
                        <h1>📅 Appointment Confirmation</h1>
                    </div>
                    <div class='content'>
                        <p class='greeting'>Hello, {$studentName}!</p>
                        <p>Your appointment request has been successfully submitted. Here are the details:</p>
                        
                        <div class='appointment-box'>
                            <div class='detail-row'>
                                <div class='detail-label'>Date:</div>
                                <div class='detail-value'><strong>{$appointmentDate}</strong></div>
                            </div>
                            <div class='detail-row'>
                                <div class='detail-label'>Time:</div>
                                <div class='detail-value'><strong>{$appointmentTime}</strong></div>
                            </div>
                            <div class='detail-row'>
                                <div class='detail-label'>Purpose:</div>
                                <div class='detail-value'>{$appointmentDetails['purpose']}</div>
                            </div>
                            <div class='detail-row'>
                                <div class='detail-label'>Urgency Level:</div>
                                <div class='detail-value'>
                                    <span class='urgency-badge'>{$appointmentDetails['urgency_level']}</span>
                                </div>
                            </div>
                            <div class='detail-row'>
                                <div class='detail-label'>Counselor:</div>
                                <div class='detail-value'>{$appointmentDetails['counselor_name']}</div>
                            </div>
                        </div>
                        
                        <div class='status-pending'>
                            ⏳ Status: PENDING APPROVAL
                        </div>
                        
                        <div class='info-box'>
                            <div class='info-title'>📌 Important Information:</div>
                            <ul style='margin: 10px 0; padding-left: 20px;'>
                                <li>Your appointment is <strong>pending approval</strong> from your counselor</li>
                                <li>You will receive another email once your appointment is approved or declined</li>
                                <li>Please arrive 5 minutes before your scheduled time</li>
                                <li>If you need to cancel, please do so at least 24 hours in advance</li>
                            </ul>
                        </div>
                        
                        <p style='margin-top: 20px; font-size: 14px; color: #666;'>
                            You can view and manage your appointments by logging into the student portal.
                        </p>
                    </div>
                    <div class='footer'>
                        <p><strong>Evergreen Academy</strong></p>
                        <p>Guidance Office</p>
                        <p>&copy; 2025 All Rights Reserved</p>
                    </div>
                </div>
            </body>
            </html>
        ";
        
        $mail->AltBody = "Hello {$studentName},\n\n"
                       . "Your appointment has been confirmed:\n\n"
                       . "Date: {$appointmentDate}\n"
                       . "Time: {$appointmentTime}\n"
                       . "Purpose: {$appointmentDetails['purpose']}\n"
                       . "Urgency: {$appointmentDetails['urgency_level']}\n"
                       . "Counselor: {$appointmentDetails['counselor_name']}\n\n"
                       . "Status: PENDING APPROVAL\n\n"
                       . "You will receive another email once approved.";
        
        $mail->send();
        return ['success' => true, 'message' => 'Appointment confirmation email sent'];
        
    } catch (Exception $e) {
        return ['success' => false, 'message' => "Email error: {$mail->ErrorInfo}"];
    }
}
?>