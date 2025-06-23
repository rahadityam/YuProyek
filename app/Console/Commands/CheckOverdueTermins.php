<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\PaymentTerm;
use App\Models\Payment;
use App\Models\User;
use App\Notifications\OverdueTerminPaymentNotification;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

class CheckOverdueTermins extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:check-overdue-termins';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Checks for project payment terms that are 2 days overdue and notifies the CEO if not paid.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        Log::info('[CheckOverdueTermins] Running command...');
        $this->info('Checking for overdue payment terms...');

        // 1. Cari semua CEO
        $ceos = User::where('role', 'ceo')->get();

        if ($ceos->isEmpty()) {
            Log::info('[CheckOverdueTermins] No CEO found. Exiting.');
            $this->info('No CEO found. Exiting.');
            return 0;
        }

        // 2. Cari semua termin yang tanggal akhirnya adalah 2 hari yang lalu
        $targetDate = now()->subDays(2)->toDateString();
        $overdueTerms = PaymentTerm::whereDate('end_date', $targetDate)->with('project')->get();

        Log::info('[CheckOverdueTermins] Found ' . $overdueTerms->count() . ' terms that ended 2 days ago.');
        $this->info('Found ' . $overdueTerms->count() . ' terms that ended on ' . $targetDate);

        if ($overdueTerms->isEmpty()) {
            return 0; // Tidak ada yang perlu dicek
        }

        foreach ($overdueTerms as $term) {
            $this->line("Checking Term '{$term->name}' for Project '{$term->project->name}'...");

            // 3. Cek apakah ada pembayaran yang sudah disetujui untuk termin ini
            $isPaid = Payment::where('payment_term_id', $term->id)
                             ->where('status', Payment::STATUS_APPROVED)
                             ->exists();

            // 4. Jika tidak ada pembayaran yang disetujui, kirim notifikasi
            if (!$isPaid) {
                Log::warning("[CheckOverdueTermins] Term ID {$term->id} is OVERDUE and UNPAID. Sending notification to CEO(s).");
                $this->warn("Term '{$term->name}' is OVERDUE. Notifying CEO(s).");
                Notification::send($ceos, new OverdueTerminPaymentNotification($term));
            } else {
                Log::info("[CheckOverdueTermins] Term ID {$term->id} is PAID. No notification sent.");
                $this->info("Term '{$term->name}' is PAID. No notification needed.");
            }
        }

        $this->info('Overdue check complete.');
        Log::info('[CheckOverdueTermins] Command finished.');
        return 0;
    }
}