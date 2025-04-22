<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage; // Import Storage

class Payment extends Model
{
    use HasFactory;

    // Tambahkan status, signature_type, signature_path, approved_at, approved_by
    protected $fillable = [
        'project_id',
        'user_id',
        'payment_type', // Akan berisi 'task', 'termin', 'full', 'other'
        'payment_term_id', // --- BARU --- Foreign key ke payment_terms (nullable)
        'payment_name',
        'bank_account',
        'amount',
        'proof_image', // Tetap ada, mungkin berguna untuk tipe 'other' atau referensi lama
        'notes',
        'status', // 'draft', 'approved'
        'signature_type', // 'digital', 'scanned'
        'signature_path',
        'approved_at',
        'approved_by',
    ];

    protected $casts = [
        'amount' => 'decimal:2', // Casting amount jika perlu
        'approved_at' => 'datetime',
    ];

    // Definisikan konstanta untuk status jika diinginkan
    public const STATUS_DRAFT = 'draft';
    public const STATUS_APPROVED = 'approved';

    // Definisikan konstanta untuk tipe signature jika diinginkan
    public const SIGNATURE_DIGITAL = 'digital';
    public const SIGNATURE_SCANNED = 'scanned';

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function user() // Pekerja yang menerima pembayaran
    {
        return $this->belongsTo(User::class);
    }

    public function approver() // User yang menyetujui (PM)
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    // --- BARU: Relasi ke Payment Term ---
    /**
     * Get the payment term associated with this payment (if any).
     */
    public function paymentTerm()
    {
        return $this->belongsTo(PaymentTerm::class);
    }
    // --- END BARU ---

    public function tasks()
    {
        // Hanya relevan jika payment_type = 'task' atau 'termin'
        return $this->hasMany(Task::class);
    }

    // Accessor untuk mendapatkan URL signature jika ada
    public function getSignatureUrlAttribute()
    {
        if ($this->signature_path && Storage::disk('public')->exists($this->signature_path)) {
            return Storage::disk('public')->url($this->signature_path);
        }
        return null;
    }

     // Accessor untuk URL proof image (jika masih digunakan)
     public function getProofImageUrlAttribute()
     {
         if ($this->proof_image && Storage::disk('public')->exists($this->proof_image)) {
             return Storage::disk('public')->url($this->proof_image);
         }
         return null;
     }

    // Helper untuk cek apakah sudah disetujui
    public function isApproved(): bool
    {
        return $this->status === self::STATUS_APPROVED;
    }
}