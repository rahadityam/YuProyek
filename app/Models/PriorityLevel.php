<?php
    namespace App\Models;

    use Illuminate\Database\Eloquent\Factories\HasFactory;
    use Illuminate\Database\Eloquent\Model;

    class PriorityLevel extends Model
    {
        use HasFactory;
        protected $fillable = [
            'project_id',
            'name',
            'value',
            'color', // Tambahkan color
            'display_order' // Tambahkan display_order
        ];

        public function project()
        {
            return $this->belongsTo(Project::class);
        }
    }