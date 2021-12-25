<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    protected $table = "employee";
    protected $fillable = ['nik', 'department', 'name', 'shift'];
    
    public function user(){
        return $this->hasOne(User::class);
    }
    public function attendance(){
        return $this->hasMany(Attendance::class);
    }
}
