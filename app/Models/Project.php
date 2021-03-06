<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Project extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'process_number',
        'agreement_number',
        'action',
        'start_date',
        'end_date',
        'city',
        'resume',
    ];

    protected $casts = [
        'process_number' => 'string',
        'agreement_number' => 'string',
        'city' => 'string',
        'resume' => 'string',
    ];

    public function translateAction()
    {
        $actions = [
            'MSD' => 'Melhorias sanitárias domiciliares',
            'MH' => 'Melhoria habitacional',
            'SAA' => 'Sistema de abastecimento de água',
            'SES' => 'Sistema de esgotamento sanitário',
            'RES' => 'Resíduos',
            'DRE' => 'Drenagem',
        ];

        return $actions[$this->action];
    }

    public function projectStatus()
    {
        return $this->hasMany(ProjectStatus::class);
    }

    public function users()
    {
        return $this->belongsToMany(User::class);
    }
}
