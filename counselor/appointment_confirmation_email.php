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
        
        // Check if appointment is auto-approved
        $isApproved = isset($appointmentDetails['status']) && $appointmentDetails['status'] === 'approved';
        
        if ($isApproved) {
            $statusColor = '#28a745';
            $statusBgColor = '#d4edda';
            $statusText = '✅ APPROVED';
            $statusMessage = 'Your appointment has been created and approved!';
        } else {
            $statusColor = '#ffc107';
            $statusBgColor = '#fff3cd';
            $statusText = '⏳ PENDING APPROVAL';
            $statusMessage = 'Your appointment has been created and is pending counselor approval.';
        }
        
        $mail->Body = "
            <html>
            <head>
                <style>
                    body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; margin: 0; padding: 0; }
                    .container { max-width: 600px; margin: 0 auto; padding: 20px; background-color: #ffffff; }
                    .header { background-color: #646300ff; color: white; padding: 30px 20px; text-align: center; border-radius: 10px 10px 0 0; }
                    .header h1 { margin: 0; font-size: 24px; }
                    .content { background-color: #f9f9f9; padding: 30px; border-radius: 0 0 10px 10px; }
                    .greeting { font-size: 18px; font-weight: bold; margin-bottom: 15px; color: #333; }
                    .status-box { background-color: {$statusBgColor}; color: {$statusColor}; padding: 15px 20px; 
                                 border-radius: 8px; text-align: center; font-weight: bold; margin: 20px 0; 
                                 font-size: 18px; border-left: 4px solid {$statusColor}; }
                    .appointment-box { background-color: white; padding: 20px; border-radius: 8px; margin: 20px 0; 
                                      border-left: 4px solid #646300ff; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
                    .appointment-box h3 { margin-top: 0; color: #646300ff; font-size: 18px; }
                    .detail-row { display: flex; padding: 12px 0; border-bottom: 1px solid #e0e0e0; }
                    .detail-row:last-child { border-bottom: none; }
                    .detail-label { font-weight: bold; width: 150px; color: #666; }
                    .detail-value { color: #333; flex: 1; }
                    .urgency-badge { display: inline-block; padding: 4px 12px; border-radius: 15px; 
                                    font-size: 12px; font-weight: bold; }
                    .urgency-low { background-color: #d4edda; color: #155724; }
                    .urgency-medium { background-color: #fff3cd; color: #856404; }
                    .urgency-high { background-color: #f8d7da; color: #721c24; }
                    .info-box { background-color: #e7f3ff; padding: 20px; border-radius: 8px; 
                               border-left: 4px solid #0066cc; margin: 20px 0; }
                    .info-title { font-weight: bold; color: #0066cc; margin-bottom: 10px; font-size: 16px; }
                    .info-list { margin: 10px 0; padding-left: 20px; color: #333; }
                    .info-list li { margin: 8px 0; }
                    .footer { text-align: center; padding: 20px; font-size: 12px; color: #777; 
                             border-top: 1px solid #e0e0e0; margin-top: 30px; }
                </style>
            </head>
            <body>
                <div class='container'>
                    <div class='header'>
                        <h1>📅 Appointment Confirmation</h1>
                    </div>
                    <div class='content'>
                        <p class='greeting'>Hello, {$studentName}!</p>
                        <p>{$statusMessage}</p>
                        
                        <div class='status-box'>
                            {$statusText}
                        </div>
                        
                        <div class='appointment-box'>
                            <h3>📋 Appointment Details</h3>
                            <div class='detail-row'>
                                <div class='detail-label'>📅 Date:</div>
                                <div class='detail-value'><strong>{$appointmentDate}</strong></div>
                            </div>
                            <div class='detail-row'>
                                <div class='detail-label'>🕐 Time:</div>
                                <div class='detail-value'><strong>{$appointmentTime}</strong></div>
                            </div>
                            <div class='detail-row'>
                                <div class='detail-label'>📝 Purpose:</div>
                                <div class='detail-value'>{$appointmentDetails['purpose']}</div>
                            </div>
                            <div class='detail-row'>
                                <div class='detail-label'>⚠️ Urgency:</div>
                                <div class='detail-value'>";
        
        // Add urgency badge
        $urgencyClass = 'urgency-' . strtolower($appointmentDetails['urgency_level']);
        $urgencyIcon = $appointmentDetails['urgency_level'] === 'High' ? '🔴' : 
                      ($appointmentDetails['urgency_level'] === 'Medium' ? '🟡' : '🟢');
        
        $mail->Body .= "<span class='urgency-badge {$urgencyClass}'>{$urgencyIcon} {$appointmentDetails['urgency_level']}</span>
                                </div>
                            </div>
                            <div class='detail-row'>
                                <div class='detail-label'>👤 Counselor:</div>
                                <div class='detail-value'>{$appointmentDetails['counselor_name']}</div>
                            </div>
                        </div>";
        
        // Different info based on status
        if ($isApproved) {
            $mail->Body .= "
                        <div class='info-box'>
                            <div class='info-title'>📌 Important Reminders:</div>
                            <ul class='info-list'>
                                <li>Please arrive <strong>5 minutes before</strong> your scheduled time</li>
                                <li>Bring any relevant documents or materials</li>
                                <li>If you need to cancel, please notify us at least 24 hours in advance</li>
                                <li>You can view your appointment in the student portal</li>
                            </ul>
                        </div>";
        } else {
            $mail->Body .= "
                        <div class='info-box'>
                            <div class='info-title'>ℹ️ What Happens Next:</div>
                            <ul class='info-list'>
                                <li>Your counselor will review your appointment request</li>
                                <li>You will receive an email notification once approved</li>
                                <li>Check your student portal for status updates</li>
                                <li>Contact your counselor if you have urgent concerns</li>
                            </ul>
                        </div>";
        }
        
        $mail->Body .= "
                        <p style='text-align: center; margin-top: 30px; color: #666;'>
                            If you have any questions, please contact the Guidance Office.
                        </p>
                    </div>
                    <div class='footer'>
                        <p><strong>Evergreen Academy</strong></p>
                        <p>Guidance Office</p>
                        <p>&copy; 2025 All Rights Reserved</p>
                        <p style='margin-top: 10px;'>
                            <a href='#' style='color: #646300ff; text-decoration: none;'>Student Portal</a> | 
                            <a href='#' style='color: #646300ff; text-decoration: none;'>Contact Us</a>
                        </p>
                    </div>
                </div>
            </body>
            </html>
        ";
        
        // Plain text version
        $statusTextPlain = $isApproved ? 'APPROVED' : 'PENDING APPROVAL';
        $mail->AltBody = "Hello {$studentName},\n\n"
                       . "Your appointment has been created - Status: {$statusTextPlain}\n\n"
                       . "Appointment Details:\n"
                       . "Date: {$appointmentDate}\n"
                       . "Time: {$appointmentTime}\n"
                       . "Purpose: {$appointmentDetails['purpose']}\n"
                       . "Urgency: {$appointmentDetails['urgency_level']}\n"
                       . "Counselor: {$appointmentDetails['counselor_name']}\n\n"
                       . "Please check the student portal for more information.";
        
        $mail->send();
        return ['success' => true, 'message' => 'Confirmation email sent'];
        
    } catch (Exception $e) {
        return ['success' => false, 'message' => "Email error: {$mail->ErrorInfo}"];
    }
}
?>