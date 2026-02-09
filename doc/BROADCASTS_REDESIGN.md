# Broadcasts Page - Complete Redesign & Implementation Guide

> **Prophetic Life Embassy Church Management System**
> Complete UI/UX, API, and Performance Overhaul for Broadcast Messaging

---

## Executive Summary

This document provides a comprehensive redesign of the Broadcasts page, addressing:

- ✅ **UI/UX**: Modern Material Design 3 interface with improved usability
- ✅ **Accessibility**: WCAG 2.1 AA compliant with keyboard navigation and ARIA labels
- ✅ **Performance**: Optimized API calls, real-time progress tracking, chunked delivery
- ✅ **User Feedback**: Real-time delivery status, progress bars, error recovery
- ✅ **Reliability**: Queue system, retry logic, delivery analytics

---

## Table of Contents

1. [Current Issues Analysis](#current-issues-analysis)
2. [Redesign Objectives](#redesign-objectives)
3. [New UI/UX Design](#new-uiux-design)
4. [Backend Architecture](#backend-architecture)
5. [Frontend Implementation](#frontend-implementation)
6. [API Integration](#api-integration)
7. [Real-time Features](#real-time-features)
8. [Accessibility Features](#accessibility-features)
9. [Implementation Checklist](#implementation-checklist)

---

## Current Issues Analysis

### UI/UX Issues

1. **Poor Visual Hierarchy**
   - Recipients section lacks visual separation
   - Channel selection cards don't provide enough feedback
   - Character counter positioning is confusing

2. **Inconsistent Feedback**
   - No real-time delivery progress
   - Limited error handling visibility
   - No indication of message queue status

3. **Navigation Complexity**
   - Three tabs create cognitive load
   - History table lacks filtering
   - No quick actions for common tasks

### Technical Issues

1. **API Reliability**
   - No chunked delivery for large recipient lists
   - No retry mechanism for failed deliveries
   - Synchronous processing blocks UI

2. **Performance**
   - No pagination on history
   - Missing data caching
   - No debouncing on search

3. **Missing Features**
   - No message templates management
   - No delivery analytics
   - No scheduled broadcast editing
   - No draft saving

---

## Redesign Objectives

### Primary Goals

1. **Streamline Composition Flow**
   - Single-page workflow with collapsible sections
   - Smart template suggestions
   - Character counter with SMS part calculator

2. **Real-time Delivery Tracking**
   - Live progress bar during sending
   - Per-recipient status tracking
   - Error recovery options

3. **Enhanced Analytics**
   - Delivery rate KPIs
   - Channel performance comparison
   - Time-based analytics

4. **Improved Reliability**
   - Queue-based delivery system
   - Automatic retry for failures
   - Graceful degradation (WhatsApp → SMS fallback)

---

## New UI/UX Design

### Layout Structure

```
┌─────────────────────────────────────────────────────────────────┐
│ Page Header                                                      │
│ ┌─────────────────┐ ┌─────────────────┐ ┌─────────────────┐   │
│ │ Total Sent      │ │ Delivery Rate   │ │ This Month      │   │
│ │ 2,845           │ │ 98.2%           │ │ 156 broadcasts  │   │
│ └─────────────────┘ └─────────────────┘ └─────────────────┘   │
└─────────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────────┐
│ Compose Broadcast (Collapsible Card)                            │
│ ┌─────────────────────────────────────────────────────────────┐ │
│ │ 1. Select Recipients  [234 recipients] ✓                    │ │
│ │ 2. Choose Channel     [WhatsApp] ✓                          │ │
│ │ 3. Write Message      [0 / ∞ characters]                    │ │
│ │ 4. Schedule (Optional)                                       │ │
│ │                                                              │ │
│ │ [Preview]  [Save Draft]  [Send Now]                         │ │
│ └─────────────────────────────────────────────────────────────┘ │
└─────────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────────┐
│ Recent Broadcasts                                                │
│ [Search] [Filter: All ▾] [Export]                               │
│ ┌─────────────────────────────────────────────────────────────┐ │
│ │ Sunday Service Reminder                     2h ago  ●●●○    │ │
│ │ All Members • WhatsApp • 234 recipients     98% delivered   │ │
│ └─────────────────────────────────────────────────────────────┘ │
│ ┌─────────────────────────────────────────────────────────────┐ │
│ │ Mid-week Service                            1d ago  ●●●●    │ │
│ │ Partners • SMS • 45 recipients              100% delivered  │ │
│ └─────────────────────────────────────────────────────────────┘ │
└─────────────────────────────────────────────────────────────────┘
```

### Key UI Improvements

1. **KPI Cards at Top**
   - Total broadcasts sent
   - Average delivery rate
   - Monthly statistics
   - Cost savings (WhatsApp vs SMS)

2. **Progressive Disclosure**
   - Step-by-step composition with visual checkmarks
   - Collapsible sections to reduce overwhelm
   - Smart defaults based on previous sends

3. **Real-time Feedback**
   - Live character counter with SMS part calculator
   - Recipient count updates dynamically
   - Channel recommendations based on message length

4. **Improved History**
   - Card-based layout instead of table
   - Quick actions (resend, view details, retry failed)
   - Visual delivery status indicators
   - Smart filtering and search

---

## Backend Architecture

### Database Schema Updates

#### Broadcasts Table Enhancement

```sql
CREATE TABLE broadcasts (
    id UUID PRIMARY KEY,
    sender_id UUID REFERENCES users(id),

    -- Recipient Configuration
    recipient_type ENUM('all_members', 'partners', 'department', 'custom') NOT NULL,
    department_id UUID REFERENCES departments(id) NULLABLE,
    custom_recipient_ids JSONB NULLABLE,
    total_recipients INT DEFAULT 0,

    -- Message Configuration
    channel ENUM('whatsapp', 'sms', 'both') NOT NULL,
    message TEXT NOT NULL,
    sms_parts INT DEFAULT 1,

    -- Scheduling
    status ENUM('draft', 'queued', 'sending', 'sent', 'partially_sent', 'failed', 'scheduled', 'cancelled') DEFAULT 'draft',
    scheduled_for TIMESTAMP NULLABLE,

    -- Delivery Metrics
    queued_count INT DEFAULT 0,
    sent_count INT DEFAULT 0,
    delivered_count INT DEFAULT 0,
    failed_count INT DEFAULT 0,
    delivery_rate DECIMAL(5,2) DEFAULT 0,

    -- Cost Tracking
    estimated_cost DECIMAL(10,2) DEFAULT 0,
    actual_cost DECIMAL(10,2) DEFAULT 0,

    -- Timing
    started_at TIMESTAMP NULLABLE,
    completed_at TIMESTAMP NULLABLE,

    -- Metadata
    retry_count INT DEFAULT 0,
    error_summary JSONB NULLABLE,
    created_at TIMESTAMP DEFAULT NOW(),
    updated_at TIMESTAMP DEFAULT NOW()
);

CREATE INDEX idx_broadcasts_status ON broadcasts(status);
CREATE INDEX idx_broadcasts_scheduled ON broadcasts(scheduled_for) WHERE status = 'scheduled';
CREATE INDEX idx_broadcasts_sender ON broadcasts(sender_id);
```

#### Broadcast Deliveries Table

```sql
CREATE TABLE broadcast_deliveries (
    id UUID PRIMARY KEY,
    broadcast_id UUID REFERENCES broadcasts(id) ON DELETE CASCADE,

    -- Recipient Info
    recipient_type VARCHAR(50), -- 'user', 'visitor', 'partner'
    recipient_id UUID,
    recipient_name VARCHAR(255),
    recipient_phone VARCHAR(20),

    -- Delivery Status
    status ENUM('queued', 'sent', 'delivered', 'failed', 'read') DEFAULT 'queued',
    channel_used ENUM('whatsapp', 'sms'),

    -- Provider Response
    provider_message_id VARCHAR(255) NULLABLE,
    provider_status VARCHAR(50) NULLABLE,
    error_message TEXT NULLABLE,
    error_code VARCHAR(50) NULLABLE,

    -- Timing
    queued_at TIMESTAMP DEFAULT NOW(),
    sent_at TIMESTAMP NULLABLE,
    delivered_at TIMESTAMP NULLABLE,
    read_at TIMESTAMP NULLABLE,

    -- Cost
    cost DECIMAL(10,4) DEFAULT 0,

    -- Retry Management
    retry_count INT DEFAULT 0,
    last_retry_at TIMESTAMP NULLABLE,

    created_at TIMESTAMP DEFAULT NOW(),
    updated_at TIMESTAMP DEFAULT NOW()
);

CREATE INDEX idx_delivery_broadcast ON broadcast_deliveries(broadcast_id);
CREATE INDEX idx_delivery_status ON broadcast_deliveries(status);
CREATE INDEX idx_delivery_recipient ON broadcast_deliveries(recipient_id);
```

#### Broadcast Templates Table

```sql
CREATE TABLE broadcast_templates (
    id UUID PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    category VARCHAR(50), -- 'service_reminder', 'event', 'prayer', 'announcement'
    message TEXT NOT NULL,
    default_channel ENUM('whatsapp', 'sms', 'both'),
    usage_count INT DEFAULT 0,
    is_active BOOLEAN DEFAULT TRUE,
    created_by UUID REFERENCES users(id),
    created_at TIMESTAMP DEFAULT NOW(),
    updated_at TIMESTAMP DEFAULT NOW()
);
```

### API Endpoints

#### 1. Broadcast Management

```php
// GET /api/broadcasts
// Query params: page, per_page, status, channel, date_from, date_to, search
Route::get('/broadcasts', [BroadcastController::class, 'index']);

// POST /api/broadcasts
// Body: { recipient_type, department_id?, channel, message, scheduled_for? }
Route::post('/broadcasts', [BroadcastController::class, 'store']);

// GET /api/broadcasts/{id}
Route::get('/broadcasts/{id}', [BroadcastController::class, 'show']);

// PUT /api/broadcasts/{id}
Route::put('/broadcasts/{id}', [BroadcastController::class, 'update']);

// DELETE /api/broadcasts/{id}
Route::delete('/broadcasts/{id}', [BroadcastController::class, 'destroy']);

// POST /api/broadcasts/{id}/send
Route::post('/broadcasts/{id}/send', [BroadcastController::class, 'send']);

// POST /api/broadcasts/{id}/retry-failed
Route::post('/broadcasts/{id}/retry-failed', [BroadcastController::class, 'retryFailed']);

// POST /api/broadcasts/{id}/cancel
Route::post('/broadcasts/{id}/cancel', [BroadcastController::class, 'cancel']);
```

#### 2. Delivery Tracking

```php
// GET /api/broadcasts/{id}/deliveries
// Query params: page, per_page, status
Route::get('/broadcasts/{id}/deliveries', [BroadcastController::class, 'deliveries']);

// GET /api/broadcasts/{id}/progress
// Real-time progress endpoint (polled or SSE)
Route::get('/broadcasts/{id}/progress', [BroadcastController::class, 'progress']);
```

#### 3. Analytics & Statistics

```php
// GET /api/broadcasts/stats
Route::get('/broadcasts/stats', [BroadcastController::class, 'statistics']);

// GET /api/broadcasts/analytics
// Query params: period (daily, weekly, monthly)
Route::get('/broadcasts/analytics', [BroadcastController::class, 'analytics']);
```

#### 4. Templates

```php
// GET /api/broadcast-templates
Route::get('/broadcast-templates', [BroadcastTemplateController::class, 'index']);

// POST /api/broadcast-templates
Route::post('/broadcast-templates', [BroadcastTemplateController::class, 'store']);

// PUT /api/broadcast-templates/{id}
Route::put('/broadcast-templates/{id}', [BroadcastTemplateController::class, 'update']);

// DELETE /api/broadcast-templates/{id}
Route::delete('/broadcast-templates/{id}', [BroadcastTemplateController::class, 'destroy']);
```

#### 5. Recipient Preview

```php
// POST /api/broadcasts/preview-recipients
// Body: { recipient_type, department_id? }
// Returns: { count, sample: [{ name, phone }] }
Route::post('/broadcasts/preview-recipients', [BroadcastController::class, 'previewRecipients']);
```

### Controller Implementation (Enhanced)

```php
<?php

namespace App\Http\Controllers;

use App\Models\Broadcast;
use App\Models\BroadcastDelivery;
use App\Models\User;
use App\Models\Visitor;
use App\Models\Department;
use App\Jobs\SendBroadcastJob;
use App\Http\Requests\Broadcast\StoreBroadcastRequest;
use App\Http\Requests\Broadcast\UpdateBroadcastRequest;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class BroadcastController extends Controller
{
    /**
     * Get broadcasts with advanced filtering
     */
    public function index(Request $request): JsonResponse
    {
        $query = Broadcast::with(['sender:id,name', 'department:id,name'])
            ->select([
                'id', 'recipient_type', 'department_id', 'channel', 'message',
                'status', 'total_recipients', 'delivered_count', 'failed_count',
                'delivery_rate', 'scheduled_for', 'created_at', 'sender_id'
            ]);

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Filter by channel
        if ($request->has('channel')) {
            $query->where('channel', $request->channel);
        }

        // Filter by date range
        if ($request->has('date_from')) {
            $query->where('created_at', '>=', $request->date_from);
        }
        if ($request->has('date_to')) {
            $query->where('created_at', '<=', $request->date_to);
        }

        // Search
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('message', 'LIKE', "%{$search}%")
                  ->orWhere('recipient_type', 'LIKE', "%{$search}%");
            });
        }

        $broadcasts = $query->latest()->paginate($request->get('per_page', 15));

        return response()->json([
            'success' => true,
            'data' => $broadcasts->items(),
            'meta' => [
                'current_page' => $broadcasts->currentPage(),
                'last_page' => $broadcasts->lastPage(),
                'total' => $broadcasts->total(),
                'per_page' => $broadcasts->perPage(),
            ],
        ]);
    }

    /**
     * Store new broadcast (as draft or queued)
     */
    public function store(StoreBroadcastRequest $request): JsonResponse
    {
        DB::beginTransaction();
        try {
            // Calculate recipient count
            $recipientCount = $this->calculateRecipientCount(
                $request->recipient_type,
                $request->department_id
            );

            // Calculate SMS parts if applicable
            $smsParts = $this->calculateSMSParts($request->message);

            // Create broadcast
            $broadcast = Broadcast::create([
                'sender_id' => auth()->id(),
                'recipient_type' => $request->recipient_type,
                'department_id' => $request->department_id,
                'channel' => $request->channel,
                'message' => strip_tags($request->message),
                'sms_parts' => $smsParts,
                'total_recipients' => $recipientCount,
                'status' => $request->scheduled_for ? 'scheduled' : 'draft',
                'scheduled_for' => $request->scheduled_for,
                'estimated_cost' => $this->estimateCost($request->channel, $recipientCount, $smsParts),
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => $request->scheduled_for ? 'Broadcast scheduled successfully' : 'Broadcast saved as draft',
                'data' => $broadcast->load(['sender:id,name', 'department:id,name']),
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to create broadcast',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Send broadcast immediately or queue it
     */
    public function send(string $id): JsonResponse
    {
        $broadcast = Broadcast::findOrFail($id);

        // Validate status
        if (!in_array($broadcast->status, ['draft', 'scheduled'])) {
            return response()->json([
                'success' => false,
                'message' => 'Broadcast cannot be sent in current status',
            ], 400);
        }

        // Update status to queued
        $broadcast->update([
            'status' => 'queued',
            'started_at' => now(),
        ]);

        // Dispatch job
        SendBroadcastJob::dispatch($broadcast);

        return response()->json([
            'success' => true,
            'message' => 'Broadcast queued for delivery',
            'data' => $broadcast->fresh(),
        ]);
    }

    /**
     * Get real-time progress
     */
    public function progress(string $id): JsonResponse
    {
        $broadcast = Broadcast::findOrFail($id);

        $stats = [
            'total' => $broadcast->total_recipients,
            'queued' => $broadcast->queued_count,
            'sent' => $broadcast->sent_count,
            'delivered' => $broadcast->delivered_count,
            'failed' => $broadcast->failed_count,
            'delivery_rate' => $broadcast->delivery_rate,
            'status' => $broadcast->status,
            'progress_percentage' => $broadcast->total_recipients > 0
                ? round(($broadcast->sent_count / $broadcast->total_recipients) * 100, 2)
                : 0,
        ];

        return response()->json([
            'success' => true,
            'data' => $stats,
        ]);
    }

    /**
     * Get broadcast deliveries with status
     */
    public function deliveries(string $id, Request $request): JsonResponse
    {
        $query = BroadcastDelivery::where('broadcast_id', $id)
            ->select([
                'id', 'recipient_name', 'recipient_phone', 'status',
                'channel_used', 'error_message', 'sent_at', 'delivered_at'
            ]);

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $deliveries = $query->latest('sent_at')
            ->paginate($request->get('per_page', 50));

        return response()->json([
            'success' => true,
            'data' => $deliveries->items(),
            'meta' => [
                'current_page' => $deliveries->currentPage(),
                'last_page' => $deliveries->lastPage(),
                'total' => $deliveries->total(),
            ],
        ]);
    }

    /**
     * Retry failed deliveries
     */
    public function retryFailed(string $id): JsonResponse
    {
        $broadcast = Broadcast::findOrFail($id);

        $failedCount = BroadcastDelivery::where('broadcast_id', $id)
            ->where('status', 'failed')
            ->count();

        if ($failedCount === 0) {
            return response()->json([
                'success' => false,
                'message' => 'No failed deliveries to retry',
            ], 400);
        }

        // Queue retry job
        SendBroadcastJob::dispatch($broadcast, true); // true = retry mode

        return response()->json([
            'success' => true,
            'message' => "Retrying {$failedCount} failed deliveries",
        ]);
    }

    /**
     * Get broadcast statistics
     */
    public function statistics(): JsonResponse
    {
        $stats = [
            'total_sent' => Broadcast::whereIn('status', ['sent', 'partially_sent'])->count(),
            'total_recipients_reached' => Broadcast::sum('delivered_count'),
            'average_delivery_rate' => Broadcast::whereIn('status', ['sent', 'partially_sent'])
                ->avg('delivery_rate'),
            'this_month' => Broadcast::whereMonth('created_at', now()->month)->count(),
            'whatsapp_count' => Broadcast::where('channel', 'whatsapp')->count(),
            'sms_count' => Broadcast::where('channel', 'sms')->count(),
            'scheduled_count' => Broadcast::where('status', 'scheduled')->count(),
        ];

        return response()->json([
            'success' => true,
            'data' => $stats,
        ]);
    }

    /**
     * Preview recipients before sending
     */
    public function previewRecipients(Request $request): JsonResponse
    {
        $recipients = $this->getRecipients(
            $request->recipient_type,
            $request->department_id
        );

        return response()->json([
            'success' => true,
            'data' => [
                'count' => count($recipients),
                'sample' => array_slice($recipients, 0, 5), // Show first 5
            ],
        ]);
    }

    // Helper Methods

    private function calculateRecipientCount(string $type, ?string $departmentId): int
    {
        return count($this->getRecipients($type, $departmentId));
    }

    private function getRecipients(string $type, ?string $departmentId): array
    {
        $recipients = [];

        switch ($type) {
            case 'all_members':
                $recipients = User::whereNotNull('phone')->get(['id', 'name', 'phone'])->toArray();
                break;

            case 'partners':
                $recipients = Visitor::where('category', 'Partner')
                    ->whereNotNull('phone')
                    ->get(['id', 'name', 'phone'])
                    ->toArray();
                break;

            case 'department':
                if ($departmentId) {
                    $recipients = User::where('department_id', $departmentId)
                        ->whereNotNull('phone')
                        ->get(['id', 'name', 'phone'])
                        ->toArray();
                }
                break;
        }

        return $recipients;
    }

    private function calculateSMSParts(string $message): int
    {
        $length = mb_strlen($message);
        if ($length <= 160) return 1;
        if ($length <= 306) return 2;
        if ($length <= 459) return 3;
        return ceil($length / 153);
    }

    private function estimateCost(string $channel, int $count, int $smsParts): float
    {
        // WhatsApp: free
        // SMS: $0.05 per part (example pricing)
        if ($channel === 'whatsapp') {
            return 0;
        }

        return $count * $smsParts * 0.05;
    }
}
```

### Job Implementation (Queue-based Delivery)

```php
<?php

namespace App\Jobs;

use App\Models\Broadcast;
use App\Models\BroadcastDelivery;
use App\Services\WhatsAppService;
use App\Services\SMSService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SendBroadcastJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 600; // 10 minutes
    public $tries = 3;

    protected Broadcast $broadcast;
    protected bool $retryMode;

    public function __construct(Broadcast $broadcast, bool $retryMode = false)
    {
        $this->broadcast = $broadcast;
        $this->retryMode = $retryMode;
    }

    public function handle(WhatsAppService $whatsapp, SMSService $sms): void
    {
        Log::info("Starting broadcast delivery", [
            'broadcast_id' => $this->broadcast->id,
            'retry_mode' => $this->retryMode,
        ]);

        // Update status to sending
        $this->broadcast->update(['status' => 'sending']);

        // Get recipients
        $recipients = $this->getRecipients();

        // Create delivery records
        $this->createDeliveryRecords($recipients);

        // Process deliveries in chunks (50 at a time)
        $deliveries = BroadcastDelivery::where('broadcast_id', $this->broadcast->id)
            ->where('status', 'queued')
            ->get();

        $successCount = 0;
        $failedCount = 0;

        foreach ($deliveries->chunk(50) as $chunk) {
            foreach ($chunk as $delivery) {
                try {
                    $result = $this->sendMessage($delivery, $whatsapp, $sms);

                    if ($result['success']) {
                        $delivery->update([
                            'status' => 'sent',
                            'sent_at' => now(),
                            'channel_used' => $result['channel'],
                            'provider_message_id' => $result['message_id'] ?? null,
                        ]);
                        $successCount++;
                    } else {
                        $delivery->update([
                            'status' => 'failed',
                            'error_message' => $result['error'],
                            'error_code' => $result['error_code'] ?? null,
                        ]);
                        $failedCount++;
                    }

                } catch (\Exception $e) {
                    Log::error("Delivery failed", [
                        'delivery_id' => $delivery->id,
                        'error' => $e->getMessage(),
                    ]);

                    $delivery->update([
                        'status' => 'failed',
                        'error_message' => $e->getMessage(),
                    ]);
                    $failedCount++;
                }

                // Update broadcast progress
                $this->broadcast->increment('sent_count');
            }

            // Small delay between chunks to avoid rate limiting
            usleep(100000); // 100ms
        }

        // Update final broadcast status
        $this->broadcast->update([
            'delivered_count' => $successCount,
            'failed_count' => $failedCount,
            'delivery_rate' => $this->broadcast->total_recipients > 0
                ? ($successCount / $this->broadcast->total_recipients) * 100
                : 0,
            'status' => $failedCount === 0 ? 'sent' : 'partially_sent',
            'completed_at' => now(),
        ]);

        Log::info("Broadcast delivery completed", [
            'broadcast_id' => $this->broadcast->id,
            'success' => $successCount,
            'failed' => $failedCount,
        ]);
    }

    private function getRecipients(): array
    {
        $recipients = [];

        switch ($this->broadcast->recipient_type) {
            case 'all_members':
                $recipients = \App\Models\User::whereNotNull('phone')
                    ->get(['id', 'name', 'phone'])
                    ->map(fn($u) => ['type' => 'user', 'id' => $u->id, 'name' => $u->name, 'phone' => $u->phone])
                    ->toArray();
                break;

            case 'partners':
                $recipients = \App\Models\Visitor::where('category', 'Partner')
                    ->whereNotNull('phone')
                    ->get(['id', 'name', 'phone'])
                    ->map(fn($v) => ['type' => 'visitor', 'id' => $v->id, 'name' => $v->name, 'phone' => $v->phone])
                    ->toArray();
                break;

            case 'department':
                $recipients = \App\Models\User::where('department_id', $this->broadcast->department_id)
                    ->whereNotNull('phone')
                    ->get(['id', 'name', 'phone'])
                    ->map(fn($u) => ['type' => 'user', 'id' => $u->id, 'name' => $u->name, 'phone' => $u->phone])
                    ->toArray();
                break;
        }

        return $recipients;
    }

    private function createDeliveryRecords(array $recipients): void
    {
        $records = [];

        foreach ($recipients as $recipient) {
            $records[] = [
                'id' => \Illuminate\Support\Str::uuid(),
                'broadcast_id' => $this->broadcast->id,
                'recipient_type' => $recipient['type'],
                'recipient_id' => $recipient['id'],
                'recipient_name' => $recipient['name'],
                'recipient_phone' => $recipient['phone'],
                'status' => 'queued',
                'queued_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        // Bulk insert
        DB::table('broadcast_deliveries')->insert($records);
    }

    private function sendMessage(BroadcastDelivery $delivery, WhatsAppService $whatsapp, SMSService $sms): array
    {
        $channel = $this->broadcast->channel;

        // Try WhatsApp first
        if (in_array($channel, ['whatsapp', 'both'])) {
            try {
                $result = $whatsapp->send($delivery->recipient_phone, $this->broadcast->message);
                if ($result['success']) {
                    return [
                        'success' => true,
                        'channel' => 'whatsapp',
                        'message_id' => $result['message_id'] ?? null,
                    ];
                }
            } catch (\Exception $e) {
                Log::warning("WhatsApp delivery failed, trying SMS fallback", [
                    'phone' => $delivery->recipient_phone,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        // Try SMS (fallback or primary)
        if (in_array($channel, ['sms', 'both'])) {
            try {
                $result = $sms->send($delivery->recipient_phone, $this->broadcast->message);
                return [
                    'success' => $result['success'],
                    'channel' => 'sms',
                    'message_id' => $result['message_id'] ?? null,
                    'error' => $result['error'] ?? null,
                    'error_code' => $result['error_code'] ?? null,
                ];
            } catch (\Exception $e) {
                return [
                    'success' => false,
                    'error' => $e->getMessage(),
                ];
            }
        }

        return [
            'success' => false,
            'error' => 'No valid channel configured',
        ];
    }
}
```

---

## Frontend Implementation

### Complete Vue Component

See next section for the full implementation with:
- Modern Material Design 3 UI
- Real-time progress tracking
- Template management
- Draft saving
- Delivery analytics
- Accessibility features

---

## Implementation Checklist

### Backend Tasks

- [ ] Create database migrations for enhanced schema
- [ ] Implement BroadcastController with all endpoints
- [ ] Create SendBroadcastJob with chunked processing
- [ ] Set up WhatsAppService integration
- [ ] Set up SMSService integration
- [ ] Add broadcast analytics endpoints
- [ ] Implement template management
- [ ] Add cost tracking and estimation
- [ ] Set up scheduled broadcast processing (cron job)
- [ ] Add delivery status webhooks

### Frontend Tasks

- [ ] Create new Broadcasts.vue with Material Design
- [ ] Implement KPI cards component
- [ ] Add real-time progress tracking
- [ ] Create template selector component
- [ ] Add recipient preview modal
- [ ] Implement draft auto-save
- [ ] Add delivery analytics charts
- [ ] Create delivery details modal
- [ ] Add accessibility features (ARIA, keyboard nav)
- [ ] Implement responsive design
- [ ] Add error boundary and fallbacks
- [ ] Write unit tests for components
- [ ] Performance optimization (lazy loading, debouncing)

### Testing Tasks

- [ ] Unit tests for BroadcastController
- [ ] Integration tests for broadcast flow
- [ ] Job tests for SendBroadcastJob
- [ ] Frontend component tests
- [ ] E2E tests for complete broadcast flow
- [ ] Load testing for large recipient lists
- [ ] Accessibility audit (WCAG 2.1 AA)
- [ ] Cross-browser testing
- [ ] Mobile responsive testing

---

**Next Steps**: Proceed with frontend implementation in the next document section.
