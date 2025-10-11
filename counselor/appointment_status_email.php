<?php
require_once __DIR__ . '/../phpmailer/vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

function sendAppointmentStatusEmail($email, $studentName, $appointmentDetails, $status) {
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
        
        // Format date and time
        $appointmentDate = date("F j, Y", strtotime($appointmentDetails['date']));
        $appointmentTime = date("h:i A", strtotime($appointmentDetails['time']));
        
        // Different content based on status
        if ($status === 'approved') {
            $mail->Subject = 'Appointment Approved - Evergreen Academy';
            $headerColor = '#28a745';
            $headerIcon = '✅';
            $headerText = 'Appointment Approved';
            $messageText = 'Great news! Your appointment has been approved by your counselor.';
            $statusColor = '#d4edda';
            $statusTextColor = '#155724';
            $statusMessage = '✅ APPROVED';
            
        } elseif ($status === 'declined') {
            $mail->Subject = 'Appointment Declined - Evergreen Academy';
            $headerColor = '#dc3545';
            $headerIcon = '❌';
            $headerText = 'Appointment Declined';
            $messageText = 'We regret to inform you that your appointment request has been declined.';
            $statusColor = '#f8d7da';
            $statusTextColor = '#721c24';
            $statusMessage = '❌ DECLINED';
            
        } else { // rescheduled
            $mail->Subject = 'Appointment Rescheduled - Evergreen Academy';
            $headerColor = '#17a2b8';
            $headerIcon = '🔄';
            $headerText = 'Appointment Rescheduled';
            $messageText = 'Your appointment has been rescheduled. Please review the details below.';
            $statusColor = '#d1ecf1';
            $statusTextColor = '#0c5460';
            $statusMessage = '🔄 RESCHEDULED';
        }
        
        $mail->Body = "
            <html>
            <head>
                <style>
                    body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; margin: 0; padding: 0; }
                    .container { max-width: 600px; margin: 0 auto; padding: 20px; background-color: #ffffff; }
                    .header { background-color: {$headerColor}; color: white; padding: 30px 20px; text-align: center; border-radius: 10px 10px 0 0; }
                    .header h1 { margin: 0; font-size: 24px; }
                    .content { background-color: #f9f9f9; padding: 30px; border-radius: 0 0 10px 10px; }
                    .greeting { font-size: 18px; font-weight: bold; margin-bottom: 15px; color: #333; }
                    .appointment-box { background-color: white; padding: 20px; border-radius: 8px; margin: 20px 0; border-left: 4px solid {$headerColor}; }
                    .detail-row { display: flex; padding: 10px 0; border-bottom: 1px solid #e0e0e0; }
                    .detail-row:last-child { border-bottom: none; }
                    .detail-label { font-weight: bold; width: 150px; color: #666; }
                    .detail-value { color: #333; flex: 1; }
                    .status-box { background-color: {$statusColor}; color: {$statusTextColor}; padding: 15px 20px; border-radius: 8px; text-align: center; font-weight: bold; margin: 20px 0; font-size: 18px; }
                    .info-box { background-color: #fff3cd; padding: 20px; border-radius: 8px; border-left: 4px solid #ffc107; margin: 20px 0; }
                    .info-title { font-weight: bold; color: #856404; margin-bottom: 10px; font-size: 16px; }
                    .footer { text-align: center; padding: 20px; font-size: 12px; color: #777; border-top: 1px solid #e0e0e0; margin-top: 30px; }
                </style>
            </head>
            <body>
                <div class='container'>
                    <div class='header'>
                        <h1>{$headerIcon} {$headerText}</h1>
                    </div>
                    <div class='content'>
                        <p class='greeting'>Hello, {$studentName}!</p>
                        <p>{$messageText}</p>
                        
                        <div class='status-box'>
                            {$statusMessage}
                        </div>
                        
                        <div class='appointment-box'>
                            <h3 style='margin-top: 0; color: {$headerColor};'>Appointment Details</h3>
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
                                <div class='detail-label'>Counselor:</div>
                                <div class='detail-value'>{$appointmentDetails['counselor_name']}</div>
                            </div>
                        </div>";
        
        // Additional messages based on status
        if ($status === 'approved') {
            $mail->Body .= "
                        <div class='info-box'>
                            <div class='info-title'>📌 Important Reminders:</div>
                            <ul style='margin: 10px 0; padding-left: 20px; color: #856404;'>
                                <li>Please arrive <strong>5 minutes before</strong> your scheduled time</li>
                                <li>Bring any relevant documents or materials</li>
                                <li>If you need to cancel, please notify us at least 24 hours in advance</li>
                                <li>You can view your appointment details in the student portal</li>
                            </ul>
                        </div>";
        } elseif ($status === 'declined') {
            $mail->Body .= "
                        <div class='info-box'>
                            <div class='info-title'>ℹ️ Next Steps:</div>
                            <ul style='margin: 10px 0; padding-left: 20px; color: #856404;'>
                                <li>You may book another appointment at a different time</li>
                                <li>Contact your counselor directly if you have urgent concerns</li>
                                <li>Check the student portal for available time slots</li>
                            </ul>
                        </div>";
        } else {
            $mail->Body .= "
                        <div class='info-box'>
                            <div class='info-title'>📌 Please Note:</div>
                            <ul style='margin: 10px 0; padding-left: 20px; color: #856404;'>
                                <li>Your appointment has been moved to a new date/time</li>
                                <li>Please confirm the new schedule in the student portal</li>
                                <li>Contact your counselor if you have any questions</li>
                            </ul>
                        </div>";
        }
        
        $mail->Body .= "
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
        
        // Plain text version
        $statusText = $status === 'approved' ? 'APPROVED' : ($status === 'declined' ? 'DECLINED' : 'RESCHEDULED');
        $mail->AltBody = "Hello {$studentName},\n\n"
                       . "Your appointment has been {$statusText}.\n\n"
                       . "Appointment Details:\n"
                       . "Date: {$appointmentDate}\n"
                       . "Time: {$appointmentTime}\n"
                       . "Purpose: {$appointmentDetails['purpose']}\n"
                       . "Counselor: {$appointmentDetails['counselor_name']}\n\n"
                       . "For more information, please check the student portal.";
        
        $mail->send();
        return ['success' => true, 'message' => 'Status notification email sent'];
        
    } catch (Exception $e) {
        return ['success' => false, 'message' => "Email error: {$mail->ErrorInfo}"];
    }
}
?>