<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Migration extends Model {
    public $timestamps = true;

    protected $fillable = ['name', 'batch'];
    
    public function scopeMigrated($query, $migration)
    {
        return $query->where('name', '=', $migration);
    }
}
