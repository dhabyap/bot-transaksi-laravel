<?php

namespace App\Console\Commands;

use App\Services\TelegramService;
use Illuminate\Console\Command;

class TelegramManageCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'telegram:manage {action=info} {--url=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Manage Telegram Bot configuration and health checks';

    protected TelegramService $telegram;

    public function __construct(TelegramService $telegram)
    {
        parent::__construct();
        $this->telegram = $telegram;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $action = $this->argument('action');

        switch ($action) {
            case 'info':
                $this->showBotInfo();
                break;
            case 'set-webhook':
                $this->setWebhook();
                break;
            case 'status':
                $this->showWebhookStatus();
                break;
            default:
                $this->error("Unknown action: {$action}. Available: info, set-webhook, status");
                break;
        }
    }

    protected function showBotInfo()
    {
        $this->info("Checking Bot Information...");
        $result = $this->telegram->getMe();

        if (!$result || !isset($result['ok']) || !$result['ok']) {
            $this->error("Failed to get bot info. Please check your TELEGRAM_BOT_TOKEN in .env");
            if ($result) $this->line(json_encode($result, JSON_PRETTY_PRINT));
            return;
        }

        $bot = $result['result'];
        $this->table(['Field', 'Value'], [
            ['ID', $bot['id']],
            ['First Name', $bot['first_name']],
            ['Username', '@' . $bot['username']],
            ['Can Join Groups', $bot['can_join_groups'] ? 'Yes' : 'No'],
            ['Supports Inline Queries', $bot['supports_inline_queries'] ? 'Yes' : 'No']
        ]);
    }

    protected function setWebhook()
    {
        $url = $this->option('url');

        if (!$url) {
            $appUrl = config('app.url');
            if ($appUrl === 'http://localhost') {
                $this->warn("APP_URL is set to localhost. Webhooks require a public URL.");
            }
            $url = "{$appUrl}/api/webhook/telegram";
        }

        if (!$this->confirm("Set webhook to: {$url}?", true)) {
            return;
        }

        $this->info("Setting webhook...");
        $result = $this->telegram->setWebhook($url);

        if ($result && isset($result['ok']) && $result['ok']) {
            $this->info("Success: " . $result['description']);
        } else {
            $this->error("Failed to set webhook.");
            $this->line(json_encode($result, JSON_PRETTY_PRINT));
        }
    }

    protected function showWebhookStatus()
    {
        $this->info("Checking Webhook Status...");
        $result = $this->telegram->getWebhookInfo();

        if (!$result || !isset($result['ok']) || !$result['ok']) {
            $this->error("Failed to get webhook info.");
            return;
        }

        $info = $result['result'];
        $this->table(['Field', 'Value'], [
            ['URL', $info['url'] ?: 'NONE'],
            ['Has Custom Certificate', $info['has_custom_certificate'] ? 'Yes' : 'No'],
            ['Pending Update Count', $info['pending_update_count']],
            ['Last Error Date', isset($info['last_error_date']) ? date('Y-m-d H:i:s', $info['last_error_date']) : 'None'],
            ['Last Error Message', $info['last_error_message'] ?? 'None'],
            ['Max Connections', $info['max_connections'] ?? 'Default']
        ]);
    }
}
