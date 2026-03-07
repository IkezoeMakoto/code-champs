<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Challenge extends Model
{
    use HasFactory, SoftDeletes;
    protected $fillable = [
        'title',
        'description',
        'period_start',
        'period_end',
    ];
    protected $casts = [
        'period_start' => 'datetime',
        'period_end' => 'datetime',
    ];

    /**
     * Check if the challenge is active based on the given date.
     *
     * @param \DateTimeInterface $date
     * @return bool
     */
    public function isActive(\DateTimeInterface $date): bool
    {
        return $date->gte($this->period_start) && $date->lte($this->period_end);
    }

    public function testCases()
    {
        return $this->hasMany(TestCase::class);
    }

    public function languages()
    {
        return $this->belongsToMany(Language::class, 'challenge_languages', 'challenge_id', 'language_id')
                    ->withPivot(['sample_code'])
                    ->withTimestamps();
    }

    public function submissions()
    {
        return $this->hasMany(Submission::class);
    }


}
