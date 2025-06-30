<?php

namespace App\Models\CMS;

use App\Entities\CMS\NotificationEntity;
use CodeIgniter\I18n\Time;

class NotificationModel extends BaseModel
{
    protected $table = 'cms_notifications';
    protected $primaryKey = 'id';
    protected $returnType = NotificationEntity::class;
    protected $allowedFields = [
        'user_id', 'type', 'title', 'message',
        'data', 'read_at', 'action_url'
    ];

    protected $validationRules = [
        'user_id' => 'required|numeric',
        'type' => 'required|string',
        'title' => 'required|string',
        'message' => 'required|string',
        'data' => 'permit_empty' // Allow null/empty
    ];

    protected array $casts = [
        'data' => 'json-array'
    ];

    // Disable activity logging for notifications
    protected $enableActivityLog = false;

    /**
     * Send notification to user
     */
    public function notify(int $userId, string $type, string $title, string $message, array $options = []): bool
    {
        $data = [
            'user_id' => $userId,
            'type' => $type,
            'title' => $title,
            'message' => $message,
            'action_url' => $options['action_url'] ?? null,
            'data' => !empty($options['data']) ? $options['data'] : [],
            'created_at' => date('Y-m-d H:i:s')
        ];

        $result = $this->insert($data);

        // Trigger real-time notification if enabled
        if ($result && ($options['realtime'] ?? true)) {
            $notification = $this->find($result);
            if ($notification) {
                $this->sendRealtimeNotification($userId, $notification);
            }
        }

        // Send email if enabled
        if ($result && ($options['email'] ?? false)) {
            $this->sendEmailNotification($userId, $type, $title, $message);
        }

        return (bool) $result;
    }

    /**
     * Send notification to multiple users
     */
    public function notifyMultiple(array $userIds, string $type, string $title, string $message, array $options = []): int
    {
        $sent = 0;

        foreach ($userIds as $userId) {
            if ($this->notify($userId, $type, $title, $message, $options)) {
                $sent++;
            }
        }

        return $sent;
    }

    /**
     * Send notification to users with specific role/permission
     */
    public function notifyByPermission(string $permission, string $type, string $title, string $message, array $options = []): int
    {
        // Get users with permission from Shield
        $users = model('UserModel')
            ->select('users.id')
            ->join('auth_groups_users', 'auth_groups_users.user_id = users.id')
            ->join('auth_permissions_users', 'auth_permissions_users.user_id = users.id', 'left')
            ->groupStart()
            ->where('auth_permissions_users.permission', $permission)
            ->orWhereIn('auth_groups_users.group', function($builder) use ($permission) {
                // Get groups with this permission
                return $builder->select('group')
                    ->from('auth_groups_permissions')
                    ->where('permission', $permission);
            })
            ->groupEnd()
            ->groupBy('users.id')
            ->findAll();

        $userIds = array_column($users, 'id');

        return $this->notifyMultiple($userIds, $type, $title, $message, $options);
    }

    /**
     * Get unread notifications for user
     */
    public function getUnread(int $userId, int $limit = 10): array
    {
        return $this->where('user_id', $userId)
            ->where('read_at', null)
            ->orderBy('created_at', 'DESC')
            ->limit($limit)
            ->findAll();
    }

    /**
     * Get all notifications for user
     */
    public function getByUser(int $userId, array $options = []): array
    {
        $query = $this->where('user_id', $userId);

        if (isset($options['type'])) {
            $query->where('type', $options['type']);
        }

        if (isset($options['unread']) && $options['unread']) {
            $query->where('read_at', null);
        }

        $limit = $options['limit'] ?? 20;
        $offset = $options['offset'] ?? 0;

        return $query->orderBy('created_at', 'DESC')
            ->limit($limit, $offset)
            ->findAll();
    }

    /**
     * Mark notification as read
     */
    public function markAsRead(int $id): bool
    {
        return $this->update($id, ['read_at' => Time::now()]);
    }

    /**
     * Mark multiple as read
     */
    public function markMultipleAsRead(array $ids, int $userId): bool
    {
        return $this->whereIn('id', $ids)
            ->where('user_id', $userId)
            ->set(['read_at' => Time::now()])
            ->update();
    }

    /**
     * Mark all as read for user
     */
    public function markAllAsRead(int $userId): bool
    {
        return $this->where('user_id', $userId)
            ->where('read_at', null)
            ->set(['read_at' => Time::now()])
            ->update();
    }

    /**
     * Get unread count
     */
    public function getUnreadCount(int $userId): int
    {
        return $this->where('user_id', $userId)
            ->where('read_at', null)
            ->countAllResults();
    }

    /**
     * Delete old notifications
     */
    public function cleanOld(int $days = 30): int
    {
        return $this->where('created_at <', Time::now()->subDays($days))
            ->where('read_at IS NOT NULL')
            ->delete();
    }

    /**
     * Send realtime notification (implement with WebSocket/SSE)
     */
    private function sendRealtimeNotification(int $userId, NotificationEntity $notification): void
    {
        // Implement with your realtime service
        // Example: Pusher, Socket.io, etc.
        Events::trigger('notification_sent', $userId, $notification);
    }

    /**
     * Send email notification
     */
    private function sendEmailNotification(int $userId, string $type, string $title, string $message): void
    {
        $user = model('UserModel')->find($userId);

        if (!$user || !$user->email) {
            return;
        }

        $email = \Config\Services::email();
        $email->setTo($user->email);
        $email->setSubject($title);
        $email->setMessage(view('emails/notification', [
            'user' => $user,
            'type' => $type,
            'title' => $title,
            'message' => $message
        ]));

        $email->send();
    }
}