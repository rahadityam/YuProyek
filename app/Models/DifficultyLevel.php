<?php
    namespace App\Models;
    
    use Illuminate\Database\Eloquent\Factories\HasFactory;
    use Illuminate\Database\Eloquent\Model;
    
    class DifficultyLevel extends Model
    {
        use HasFactory;
        protected $fillable = [
            'project_id',
            'name',
            'value',
            'color', // Tambahkan color
            'display_order' // Tambahkan display_order
        ];
    
        // ... relasi project()
        public function project()
        {
            return $this->belongsTo(Project::class);
        }
    }