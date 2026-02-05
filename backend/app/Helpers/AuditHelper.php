<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Log;

class AuditHelper
{
    /**
     * Log an audit trail
     *
     * @param string $userId
     * @param string $action (create, update, delete, approve, reject, etc.)
     * @param string $entityType (user, attendance, visitor, etc.)
     * @param string $entityId
     * @param array|null $before
     * @param array|null $after
     * @param string|null $ipAddress
     * @param string|null $userAgent
     * @return void
     */
    public static function log(
        string $userId,
        string $action,
        string $entityType,
        string $entityId,
        ?array $before = null,
        ?array $after = null,
        ?string $ipAddress = null,
        ?string $userAgent = null
    ): void {
        // In production, this would insert into audit_logs table
        // For now, log to Laravel logs
        Log::channel('audit')->info('Audit Log', [
            'user_id' => $userId,
            'action' => $action,
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'changes' => [
                'before' => $before,
                'after' => $after,
            ],
            'ip_address' => $ipAddress ?? request()->ip(),
            'user_agent' => $userAgent ?? request()->userAgent(),
            'timestamp' => now()->toISOString(),
        ]);
    }

    /**
     * Log a creation action
     *
     * @param string $userId
     * @param string $entityType
     * @param string $entityId
     * @param array $data
     * @return void
     */
    public static function logCreate(string $userId, string $entityType, string $entityId, array $data): void
    {
        self::log($userId, 'create', $entityType, $entityId, null, $data);
    }

    /**
     * Log an update action
     *
     * @param string $userId
     * @param string $entityType
     * @param string $entityId
     * @param array $before
     * @param array $after
     * @return void
     */
    public static function logUpdate(string $userId, string $entityType, string $entityId, array $before, array $after): void
    {
        self::log($userId, 'update', $entityType, $entityId, $before, $after);
    }

    /**
     * Log a delete action
     *
     * @param string $userId
     * @param string $entityType
     * @param string $entityId
     * @param array $data
     * @return void
     */
    public static function logDelete(string $userId, string $entityType, string $entityId, array $data): void
    {
        self::log($userId, 'delete', $entityType, $entityId, $data, null);
    }

    /**
     * Log an approval action
     *
     * @param string $userId
     * @param string $entityType
     * @param string $entityId
     * @return void
     */
    public static function logApprove(string $userId, string $entityType, string $entityId): void
    {
        self::log($userId, 'approve', $entityType, $entityId, ['status' => 'pending'], ['status' => 'approved']);
    }

    /**
     * Log a rejection action
     *
     * @param string $userId
     * @param string $entityType
     * @param string $entityId
     * @param string $reason
     * @return void
     */
    public static function logReject(string $userId, string $entityType, string $entityId, string $reason): void
    {
        self::log(
            $userId,
            'reject',
            $entityType,
            $entityId,
            ['status' => 'pending'],
            ['status' => 'rejected', 'reason' => $reason]
        );
    }

    /**
     * Mask sensitive data in audit logs
     *
     * @param array $data
     * @param array $sensitiveFields
     * @return array
     */
    public static function maskSensitiveData(array $data, array $sensitiveFields = ['password', 'token', 'secret']): array
    {
        foreach ($sensitiveFields as $field) {
            if (isset($data[$field])) {
                $data[$field] = '***MASKED***';
            }
        }

        return $data;
    }
}
