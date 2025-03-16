<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserEducation extends Model
{
    use HasFactory;
    
    protected $table = 'user_educations';

    protected $fillable = [
        'user_id',
        'level',
        'institution',
        'major',
        'graduation_year',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}