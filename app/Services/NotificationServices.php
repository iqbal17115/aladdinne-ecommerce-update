<?php

namespace App\Services;

use App\Models\Notification as AppNotification;
use Illuminate\Support\Facades\Log;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification as FirebaseNotification;

class NotificationServices
{
    public static function sendNotification(string $body, array $tokens, $title = null)
    {
        $notification = FirebaseNotification::create($title, $body);
        $originalTokenCount = count($tokens);
        $tokens = self::sanitizeTokens($tokens);

        if (empty($tokens)) {
            // Log::warning('Push notification aborted: no valid tokens for customer notification.', [
            //     'title' => $title,
            //     'original_token_count' => $originalTokenCount,
            // ]);

            return [
                'success' => false,
                'message' => 'No valid device keys were found for the selected users.',
            ];
        }

        $firebaseCredentials = storage_path('app/public/firebase_credentials.json');

        if (! file_exists($firebaseCredentials)) {
            // Log::warning('Push notification aborted: Firebase credentials file missing.', [
            //     'title' => $title,
            //     'token_count' => count($tokens),
            //     'credentials_path' => $firebaseCredentials,
            // ]);

            return [
                'success' => false,
                'message' => 'Firebase notification can not be sent. Please provide valid firebase credentials file.',
            ];
        }

        $messaging = (new Factory)->withServiceAccount($firebaseCredentials)->createMessaging();

        $message = CloudMessage::new()->withNotification($notification);

        try {
            $messaging->sendMulticast($message, $tokens);
            // Log::info('Push notification sent successfully.', [
            //     'title' => $title,
            //     'token_count' => count($tokens),
            // ]);

            return [
                'success' => true,
                'message' => 'Notification sent successfully',
            ];
        } catch (\Throwable $e) {
            // Log::error('Push notification send failed.', [
            //     'title' => $title,
            //     'token_count' => count($tokens),
            //     'error' => $e->getMessage(),
            // ]);

            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    public static function sendAlertNotification(AppNotification $notification, array $tokens)
    {
        $originalTokenCount = count($tokens);
        $tokens = self::sanitizeTokens($tokens);

        if (empty($tokens)) {
            // Log::warning('Push notification aborted: no valid tokens for alert notification.', [
            //     'notification_id' => $notification->id,
            //     'title' => $notification->title,
            //     'original_token_count' => $originalTokenCount,
            // ]);

            return [
                'success' => false,
                'message' => 'No valid device keys were found for this user.',
            ];
        }

        $firebaseCredentials = storage_path('app/public/firebase_credentials.json');

        if (! file_exists($firebaseCredentials)) {
            // Log::warning('Push notification aborted: Firebase credentials file missing for alert notification.', [
            //     'notification_id' => $notification->id,
            //     'title' => $notification->title,
            //     'token_count' => count($tokens),
            //     'credentials_path' => $firebaseCredentials,
            // ]);

            return [
                'success' => false,
                'message' => 'Firebase notification can not be sent. Please provide valid firebase credentials file.',
            ];
        }

        $messaging = (new Factory)->withServiceAccount($firebaseCredentials)->createMessaging();

        $message = CloudMessage::new()->withNotification(
            FirebaseNotification::create($notification->title, $notification->content)
        );

        try {
            $messaging->sendMulticast($message, $tokens);
            // Log::info('Alert push notification sent successfully.', [
            //     'notification_id' => $notification->id,
            //     'title' => $notification->title,
            //     'token_count' => count($tokens),
            // ]);

            return [
                'success' => true,
                'message' => 'Notification sent successfully',
            ];
        } catch (\Throwable $e) {
            // Log::error('Alert push notification send failed.', [
            //     'notification_id' => $notification->id,
            //     'title' => $notification->title,
            //     'token_count' => count($tokens),
            //     'error' => $e->getMessage(),
            // ]);

            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    private static function sanitizeTokens(array $tokens): array
    {
        return array_values(array_unique(array_filter($tokens, function ($token) {
            return is_string($token) && trim($token) !== '';
        })));
    }
}
