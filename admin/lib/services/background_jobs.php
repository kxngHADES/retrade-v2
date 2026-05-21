<?php
namespace Lib\services;


class background_jobs{


    # mass email
    public function triggerMassBroadcast(string $subject, string $content): array {
        $apiUrl = $_ENV['BACKEND_INTERNAL_URL'] . '/admin/broadcast/send';
        $data = [
            'subject' => $subject,
            'content' => $content,
            'batch_size' => 20
        ];

        $ch = curl_init($apiUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        
        curl_setopt($ch, CURLOPT_TIMEOUT, 10); 
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);

        $result = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        if ($result === FALSE) {
            error_log("Broadcast API Error: " . $error);
            return [
                'success' => false,
                'message' => 'Network error while contacting engine: ' . $error
            ];
        }

        if ($httpCode >= 400) {
            return [
                'success' => false,
                'message' => "Server error ($httpCode) from broadcast engine."
            ];
        }

        $response = json_decode($result, true);
        return [
            'success' => true,
            'message' => $response['message'] ?? 'Broadcast queued successfully.'
        ];
    }


}