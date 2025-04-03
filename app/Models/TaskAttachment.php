<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage; // Import Storage

class TaskAttachment extends Model
{
    use HasFactory;

    protected $fillable = [
        'task_id',
        'user_id',
        'file_path',
        'file_name',
        'file_type',
        'size',
    ];

    protected $appends = ['url']; // Tambahkan accessor untuk URL

    public function task()
    {
        return $this->belongsTo(Task::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Accessor untuk mendapatkan URL file
    public function getUrlAttribute()
    {
        // Pastikan disk 'public' terkonfigurasi dan symlink dibuat
        // php artisan storage:link
        if ($this->file_path && Storage::disk('public')->exists($this->file_path)) {
            return Storage::disk('public')->url($this->file_path);
        }
        return null; // Atau URL default jika file tidak ada
    }

     // Accessor untuk format ukuran file
     public function getFormattedSizeAttribute(): string
     {
         $bytes = $this->size ?? 0;
         if ($bytes >= 1073741824) {
             return number_format($bytes / 1073741824, 2) . ' GB';
         } elseif ($bytes >= 1048576) {
             return number_format($bytes / 1048576, 2) . ' MB';
         } elseif ($bytes >= 1024) {
             return number_format($bytes / 1024, 2) . ' KB';
         } elseif ($bytes > 1) {
             return $bytes . ' bytes';
         } elseif ($bytes == 1) {
             return $bytes . ' byte';
         } else {
             return '0 bytes';
         }
     }
}