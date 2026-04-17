<?php

namespace App\Console\Commands;

use App\Http\Controllers\TelegramWebhookController;
use App\Services\TelegramService;
use Illuminate\Console\Command;
use Illuminate\Http\Request;

class TelegramPollCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'telegram:poll';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run the Telegram Bot locally using long polling';

    protected TelegramService $telegram;
    protected TelegramWebhookController $controller;

    public function __construct(TelegramService $telegram, TelegramWebhookController $controller)
    {
        parent::__construct();
        $this->telegram = $telegram;
        $this->controller = $controller;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info("Starting Telegram Bot Polling...");
        
        // Remove webhook first to use getUpdates
        $this->info("Deleting webhook if it exists...");
        $this->telegram->deleteWebhook();

        $offset = 0;
        
        $this->info("Polling updates... (Press Ctrl+C to stop)");

        while (true) {
            $updates = $this->telegram->getUpdates($offset, 30); // 30 seconds timeout
            
            if ($updates && isset($updates['ok']) && $updates['ok']) {
                foreach ($updates['result'] as $update) {
                    // Update offset to acknowledge receipt of this update
                    $offset = $update['update_id'] + 1;

                    // Only process messages
                    if (isset($update['message'])) {
                        $this->line("Received message from {$update['message']['from']['first_name']}: " . ($update['message']['text'] ?? ''));
                        
                        try {
                            // Create a dummy request to pass to the controller
                            $request = new Request([], $update);
                            
                            // Process the message using existing webhook controller logic
                            $this->controller->handle($request);
                            
                            $this->info("Processed message ID: {$update['message']['message_id']}");
                        } catch (\Exception $e) {
                            $this->error("Error processing message: " . $e->getMessage());
                        }
                    }
                }
            } else {
                if ($updates && isset($updates['description'])) {
                    $this->error("Telegram API Error: " . $updates['description']);
                }
            }

            // Small sleep to prevent CPU hogging if it returns immediately
            usleep(100000); 
        }
    }
}
