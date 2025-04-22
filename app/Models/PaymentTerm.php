<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentTerm extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'name', // Nama Termin (e.g., "Termin 1", "Fase A")
        'start_date',
        'end_date',
        // 'sequence', // Jika diperlukan urutan manual
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    /**
     * Get the project that owns the payment term.
     */
    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * Get the payments associated with this term.
     * Note: A term might not have a payment yet, or a payment might not be linked to a specific term.
     */
    public function payments()
    {
        return $this->hasMany(Payment::class);
    }
}