<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class ProjectFile extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'user_id',
        'file_name',
        'file_path',
        'mime_type',
        'size',
        'display_name',
        'description',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Accessor untuk mendapatkan URL file
    public function getUrlAttribute()
    {
        if ($this->file_path && Storage::disk('public')->exists($this->file_path)) {
            return Storage::disk('public')->url($this->file_path);
        }
        return null;
    }

    // Accessor untuk format ukuran file
    public function getFormattedSizeAttribute(): string
    {
        $bytes = $this->size ?? 0;
        if ($bytes >= 1073741824) return number_format($bytes / 1073741824, 2) . ' GB';
        if ($bytes >= 1048576) return number_format($bytes / 1048576, 2) . ' MB';
        if ($bytes >= 1024) return number_format($bytes / 1024, 2) . ' KB';
        if ($bytes > 1) return $bytes . ' bytes';
        if ($bytes == 1) return $bytes . ' byte';
        return '0 bytes';
    }

    // Helper untuk mengecek apakah file adalah gambar
    public function isImage(): bool
    {
        return $this->mime_type && str_starts_with($this->mime_type, 'image/');
    }

    // Helper untuk mengecek apakah file adalah PDF
    public function isPdf(): bool
    {
        return $this->mime_type === 'application/pdf';
    }

    // Tambahkan ke $appends jika ingin URL dan ukuran terformat selalu ada saat serialisasi
    protected $appends = ['url', 'formatted_size'];
}