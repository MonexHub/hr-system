<?php

/**
 * FirebaseChannel Service
 *
 * This implementation is inspired by the work of [David Haule] on their project.
 *
 * Credit: [dhsoft95](https://github.com/dhsoft95/notifications-microservice)
 *
 * This class provides the functionality to send notifications using Firebase Cloud Messaging.
 */

namespace App\Services;

use Kreait\Firebase\Factory;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;
use Illuminate\Support\Facades\Log;

class FirebaseNotificationService
{
    protected $firebase;
    protected $messaging;

    /**
     * FirebaseChannel constructor.
     */
    public function __construct()
    {
        $this->firebase = (new Factory)
            ->withServiceAccount(storage_path('app/monex-hr-auth.json'))
            ->withDatabaseUri(config('services.firebase.database_url'));

        $this->messaging = $this->firebase->createMessaging();
    }

    /**
     * Send a push notification to a specific device using its token
     *
     * @param array $data
     * @return array
     */
    public function send(array $data): array
    {
        try {
            if (!empty($data['token'])) {
                $message = CloudMessage::withTarget('token', $data['token'])
                    ->withNotification(Notification::create($data['title'], $data['body']))
                    ->withData(array_merge([
                        'message' => $data['body'],
                        'notification' => 'success'
                    ], $data['data'] ?? []));

                $this->messaging->send($message);

                Log::info('Firebase Push Notification Sent', ['DeviceToken' => $data['token']]);

                return [
                    'message' => 'Notification sent successfully',
                    'notification' => 'success',
                ];
            }

            return [
                'message' => 'Device token not provided',
                'notification' => 'failure',
            ];
        } catch (\Exception $e) {
            Log::error('Firebase Push Notification Error', ['Error' => $e->getMessage()]);
            return [
                'message' => 'Failed to send notification',
                'notification' => 'failure',
            ];
        }
    }

    /**
     * Send a global push notification to all subscribed devices
     *
     * @param string $title
     * @param string $body
     * @param array $data
     * @return array
     */
    public function sendGlobal(string $title, string $body, array $data = []): array
    {
        try {
            $message = CloudMessage::fromArray([
                'notification' => [
                    'title' => $title,
                    'body' => $body
                ],
                'data' => array_merge([
                    'message' => $body,
                    'notification' => 'success'
                ], $data),
                'topic' => 'global'
            ])->withNotification(Notification::create($title, $body))
                ->withData(array_merge([
                    'message' => $body,
                    'notification' => 'success'
                ], $data));

            $this->messaging->send($message);

            Log::info('Firebase Global Push Notification Sent');

            return [
                'message' => 'Global notification sent successfully',
                'notification' => 'success',
            ];
        } catch (\Exception $e) {
            Log::error('Firebase Global Push Notification Error', ['Error' => $e->getMessage()]);
            return [
                'message' => 'Failed to send global notification',
                'notification' => 'failure',
            ];
        }
    }

    /**
     * Send a push notification to multiple devices using FCM tokens.
     *
     * @param array $tokens
     * @param string $title
     * @param string $body
     * @param array $data
     * @return array
     */
    public function sendToMultiple(array $tokens, string $title, string $body, array $data = []): array
    {
        try {
            if (empty($tokens)) {
                return [
                    'message' => 'No device tokens provided',
                    'notification' => 'failure',
                ];
            }

            $message = CloudMessage::new()
                ->withNotification(Notification::create($title, $body))
                ->withData(array_merge([
                    'message' => $body,
                    'notification' => 'success'
                ], $data));

            $report = $this->messaging->sendMulticast($message, $tokens);

            Log::info('Firebase Multicast Notification Sent', [
                'successes' => $report->successes()->count(),
                'failures' => $report->failures()->count()
            ]);

            return [
                'message' => 'Multicast notification processed',
                'notification' => 'success',
                'successes' => $report->successes()->count(),
                'failures' => $report->failures()->count(),
            ];
        } catch (\Exception $e) {
            Log::error('Firebase Multicast Notification Error', ['Error' => $e->getMessage()]);
            return [
                'message' => 'Failed to send multicast notification',
                'notification' => 'failure',
            ];
        }
    }

    /**
     * Send a push notification to a specific device.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function sendPushNotification(Request $request)
    {
        // Validate request input
        $validator = Validator::make($request->all(), [
            'fcm_token' => 'required|string',
            'title' => 'required|string',
            'body' => 'required|string',
            'data' => 'sometimes|array',
        ]);

        // Return error message if validation fails
        if ($validator->fails()) {
            return response()->json([
                'notification' => 'failure',
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        // Prepare data for notification
        $data = [
            'token' => $request->fcm_token,
            'title' => $request->title,
            'body' => $request->body,
            'data' => $request->data ?? [],
        ];

        // Send notification
        $response = $this->send($data);

        return response()->json($response);
    }
}
