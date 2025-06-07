<?php
function sendEmail($name, $email, $message) {
    $config = require_once __DIR__ . '/../config/email.php';
    
    if (empty($config['smtp_password'])) {
        return array(
            'success' => false,
            'message' => 'Konfigurasi email belum lengkap. Silakan hubungi administrator.'
        );
    }

    $to = $config['smtp_from'];
    $subject = 'Pesan dari ' . $name;
    
    $headers = array();
    $headers[] = 'MIME-Version: 1.0';
    $headers[] = 'Content-type: text/html; charset=UTF-8';
    $headers[] = 'From: ' . $name . ' <' . $email . '>';
    $headers[] = 'Reply-To: ' . $email;
    $headers[] = 'X-Mailer: PHP/' . phpversion();
    
    $htmlMessage = '
    <html>
    <head>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 600px; margin: 20px auto; padding: 20px; border: 1px solid #ddd; border-radius: 5px; }
            .header { background: #2E7D32; color: white; padding: 20px; border-radius: 5px 5px 0 0; }
            .content { padding: 20px; }
            .footer { background: #f5f5f5; padding: 20px; border-radius: 0 0 5px 5px; font-size: 12px; }
        </style>
    </head>
    <body>
        <div class="container">
            <div class="header">
                <h2>Pesan Baru dari Website KebunKU</h2>
            </div>
            <div class="content">
                <p><strong>Nama:</strong> ' . htmlspecialchars($name) . '</p>
                <p><strong>Email:</strong> ' . htmlspecialchars($email) . '</p>
                <p><strong>Pesan:</strong></p>
                <p>' . nl2br(htmlspecialchars($message)) . '</p>
            </div>
            <div class="footer">
                <p>Email ini dikirim dari form kontak website KebunKU</p>
            </div>
        </div>
    </body>
    </html>';

    $context = stream_context_create([
        'ssl' => [
            'verify_peer' => false,
            'verify_peer_name' => false,
            'allow_self_signed' => true
        ]
    ]);

    $socket = stream_socket_client(
        'tcp://' . $config['smtp_host'] . ':' . $config['smtp_port'],
        $errno,
        $errstr,
        30,
        STREAM_CLIENT_CONNECT,
        $context
    );

    if (!$socket) {
        return array(
            'success' => false,
            'message' => 'Gagal terhubung ke server email. Silakan coba lagi nanti.'
        );
    }

    try {
        stream_set_timeout($socket, 30);

        $response = fgets($socket, 515);
        if (substr($response, 0, 3) !== '220') {
            throw new Exception('SMTP server not ready: ' . $response);
        }
        
        fputs($socket, "EHLO " . $_SERVER['SERVER_NAME'] . "\r\n");
        do {
            $response = fgets($socket, 515);
        } while (substr($response, 3, 1) === '-');
        
        fputs($socket, "STARTTLS\r\n");
        $response = fgets($socket, 515);
        if (substr($response, 0, 3) !== '220') {
            throw new Exception('STARTTLS failed: ' . $response);
        }
        
        if (!stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
            throw new Exception('Failed to enable TLS encryption');
        }
        
        fputs($socket, "EHLO " . $_SERVER['SERVER_NAME'] . "\r\n");
        do {
            $response = fgets($socket, 515);
        } while (substr($response, 3, 1) === '-');
        
        fputs($socket, "AUTH LOGIN\r\n");
        $response = fgets($socket, 515);
        if (substr($response, 0, 3) !== '334') {
            throw new Exception('AUTH LOGIN failed: ' . $response);
        }
        
        fputs($socket, base64_encode($config['smtp_username']) . "\r\n");
        $response = fgets($socket, 515);
        if (substr($response, 0, 3) !== '334') {
            throw new Exception('Username not accepted: ' . $response);
        }
        
        fputs($socket, base64_encode($config['smtp_password']) . "\r\n");
        $response = fgets($socket, 515);
        if (substr($response, 0, 3) !== '235') {
            throw new Exception('Authentication failed: ' . $response);
        }
        
        fputs($socket, "MAIL FROM:<" . $config['smtp_from'] . ">\r\n");
        $response = fgets($socket, 515);
        if (substr($response, 0, 3) !== '250') {
            throw new Exception('MAIL FROM not accepted: ' . $response);
        }
        
        fputs($socket, "RCPT TO:<$to>\r\n");
        $response = fgets($socket, 515);
        if (substr($response, 0, 3) !== '250') {
            throw new Exception('RCPT TO not accepted: ' . $response);
        }
        
        fputs($socket, "DATA\r\n");
        $response = fgets($socket, 515);
        if (substr($response, 0, 3) !== '354') {
            throw new Exception('DATA command not accepted: ' . $response);
        }
        
        fputs($socket, "Subject: $subject\r\n");
        foreach ($headers as $header) {
            fputs($socket, "$header\r\n");
        }
        fputs($socket, "\r\n" . $htmlMessage . "\r\n.\r\n");
        $response = fgets($socket, 515);
        if (substr($response, 0, 3) !== '250') {
            throw new Exception('Message not accepted: ' . $response);
        }
        
        fputs($socket, "QUIT\r\n");
        fclose($socket);
        
        return array(
            'success' => true,
            'message' => 'Pesan berhasil dikirim!'
        );
    } catch (Exception $e) {
        if ($socket) {
            fclose($socket);
        }
        return array(
            'success' => false,
            'message' => 'Gagal mengirim email: ' . $e->getMessage()
        );
    }
}
?> 