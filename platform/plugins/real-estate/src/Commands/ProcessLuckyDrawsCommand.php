<?php

namespace Botble\RealEstate\Commands;

use Botble\RealEstate\Services\LuckyDrawService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ProcessLuckyDrawsCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'lucky-draws:process {--force : Force execution even if not scheduled}';

    /**
     * The console command description.
     */
    protected $description = 'Process reward draws - activate upcoming draws and execute completed draws';

    /**
     * Reward Draw Service
     */
    protected $luckyDrawService;

    /**
     * Create a new command instance.
     */
    public function __construct(LuckyDrawService $luckyDrawService)
    {
        parent::__construct();
        $this->luckyDrawService = $luckyDrawService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🎯 Processing Reward Draws...');
        
        try {
            // Step 1: Auto-activate upcoming draws
            $this->info('📅 Checking for draws to activate...');
            $activated = $this->luckyDrawService->autoActivateDraws();
            
            if ($activated > 0) {
                $this->info("✅ Activated {$activated} draw(s)");
                Log::info("Reward Draws: Activated {$activated} draws");
            } else {
                $this->info("ℹ️  No draws to activate");
            }

            // Step 2: Auto-execute completed draws
            $this->info('🏆 Checking for draws to execute...');
            $executed = $this->luckyDrawService->autoExecuteDraws();
            
            if ($executed > 0) {
                $this->info("🎉 Executed {$executed} draw(s) and selected winners!");
                Log::info("Reward Draws: Executed {$executed} draws");
                
                // Show statistics
                $this->showStatistics();
            } else {
                $this->info("ℹ️  No draws ready for execution");
            }

            $this->info('✅ Reward Draw processing completed successfully!');
            
        } catch (\Exception $e) {
            $this->error('❌ Error processing reward draws: ' . $e->getMessage());
            Log::error('Reward Draw Command Error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            
            return 1; // Exit with error code
        }

        return 0; // Success
    }

    /**
     * Show current statistics
     */
    private function showStatistics()
    {
        $stats = $this->luckyDrawService->getDrawStatistics();
        
        $this->info('📊 Current Statistics:');
        $this->table(
            ['Metric', 'Value'],
            [
                ['Total Draws', $stats['total_draws']],
                ['Active Draws', $stats['active_draws']],
                ['Completed Draws', $stats['completed_draws']],
                ['Total Participants', $stats['total_participants']],
                ['Total Revenue', '₹' . number_format($stats['total_revenue'], 2)],
                ['Real Winners', $stats['real_winners']],
                ['Dummy Winners', $stats['dummy_winners']],
                ['Net Profit/Loss', '₹' . number_format($stats['total_profit'] + $stats['total_loss'], 2)],
            ]
        );
    }
}