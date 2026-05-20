# Audit & Compliance

Orbit's Audit & Compliance section provides security scanning, data retention management, and access logging to help you maintain control over your AI operations.

## Access

Navigate to `/ai-orbit/audit`.

## PII Detection

The PII Detector scans message content for sensitive information that should not be sent to external AI providers.

### Detected Patterns

| Pattern | Example |
|:---|:---|
| **Email Addresses** | `user@example.com` |
| **Phone Numbers** | `+1 (555) 123-4567` |
| **Social Security Numbers** | `123-45-6789` |
| **Credit Card Numbers** | `4111-1111-1111-1111` |
| **IP Addresses** | `192.168.1.1` |

### Scanning a Single Message

Enter text in the PII scanner and click **Scan**:

```
Input: "Contact john.doe@company.com or call 555-123-4567"

Results:
- Email: john.doe@company.com
- Phone: 555-123-4567
```

### Scanning Conversations

The audit dashboard scans recent conversations automatically and highlights any with detected PII. Review these conversations to ensure sensitive data isn't being leaked to AI providers.

### Programmatic Access

```php
use Ashrafic\AiOrbit\Services\PiiDetector;

$detector = app(PiiDetector::class);

// Scan a string
$result = $detector->scan('Contact john@example.com');
// Returns: has_pii = true, detections = ['email' => ['john@example.com']]

// Scan all messages in a conversation
$result = $detector->scanConversation($messages);
// Returns: has_pii, count, detections
```

### Best Practices

1. **Scan before sending** — Use the PII detector before sending user input to AI providers
2. **Review flagged conversations** — Check the audit dashboard regularly for PII leaks
3. **Sanitize inputs** — Strip or mask PII before including it in prompts
4. **Educate your team** — Ensure developers know not to include PII in agent instructions

## Data Retention

Manage how long conversation data is kept. Orbit provides both dry-run previews and actual purging.

### Retention Policy

The default retention period is 90 days, configurable in `config/ai-orbit.php`:

```php
'audit' => [
    'retention_days' => 90,
],
```

Or via `.env`:

```env
ORBIT_RETENTION_DAYS=90
```

### Dry Run

Before deleting anything, preview what would be affected:

1. Enter the retention period (in days)
2. Click **Dry Run**
3. Review the list of conversations that would be deleted

This shows:
- Number of conversations affected
- Conversation IDs and creation dates

### Purge

After reviewing the dry run, click **Purge** to permanently delete:

1. Conversations older than the retention period
2. All associated messages
3. Stale sandbox sessions (older than 24 hours with user_id = 0)

### Sandbox Cleanup

Orbit automatically cleans up sandbox sessions (conversations with `user_id = 0`) that are older than 24 hours. This prevents the sandbox from filling up your database.

### Programmatic Access

```php
use Ashrafic\AiOrbit\Services\DataRetention;

$retention = app(DataRetention::class);

// Preview what would be deleted
$preview = $retention->dryRun(retentionDays: 90);
// Returns: count, conversations

// Actually delete
$deletedCount = $retention->purge(retentionDays: 90);

// Clean up stale sandbox sessions only
$sandboxDeleted = $retention->purgeStaleSandboxSessions();
```

### Best Practices

1. **Set a retention policy** that matches your compliance requirements
2. **Run dry runs first** — always preview before purging
3. **Schedule regular cleanup** — Add `DataRetention::purge()` to a nightly cron
4. **Back up before purging** — Export important conversations before deletion
5. **Monitor sandbox growth** — Sandbox sessions auto-clean, but verify they're being removed

## Access Audit

Orbit logs all dashboard access attempts via the `Authorize` middleware. Review the audit log to see:

- Who accessed the dashboard
- When they accessed it
- Whether access was granted or denied

### Programmatic Access

Access logs are stored in the Laravel AI SDK tables. Query them directly:

```php
DB::table('agent_conversations')
    ->where('created_at', '>=', now()->subDays(7))
    ->orderByDesc('created_at')
    ->get();
```

## Compliance Checklist

Use this checklist to ensure your AI operations are compliant:

- [ ] PII scanning is enabled and regularly reviewed
- [ ] Data retention policy is defined and enforced
- [ ] Access to the dashboard is restricted to authorized users
- [ ] Audit logs are reviewed periodically
- [ ] Sensitive data is sanitized before sending to AI providers
- [ ] Sandbox sessions are cleaned up regularly
- [ ] Budget alerts are configured to prevent unexpected costs

## Disabling Audit Features

To disable PII scanning and data retention features:

```php
// config/ai-orbit.php
'audit' => [
    'enabled' => false,
],
```

Or via `.env`:

```env
ORBIT_AUDIT_ENABLED=false
```

## Customization

Override the audit views:

```bash
php artisan vendor:publish --tag=ai-orbit-views
```

Then edit:
- `resources/views/vendor/laravel-ai-orbit/audit/index.blade.php` — Audit layout
- `resources/views/vendor/laravel-ai-orbit/livewire/audit-dashboard.blade.php` — Audit dashboard component
