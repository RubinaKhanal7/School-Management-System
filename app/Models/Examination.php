<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Examination extends Model
{
    use HasFactory;
    protected $table = 'examinations';
    protected $fillable = ['exam', 'is_publish', 'exam_type', 'is_rank_generated', 'description', 'is_active', 'session_id', 'school_id'];

    public function examSchedules()
    {
        return $this->hasMany(ExamSchedule::class);
    }

    public function finalTerminalExaminations()
    {
        return $this->belongsToMany(Examination::class, 'final_term_examinations', 'final_examination_id', 'terminal_examination_id');
    }
    public function subjectByRoutine()
    {
        return $this->hasManyThrough(
            Subject::class, 
            ExamSchedule::class, 
            'examination_id',
            'id', 
            'id',
            'subject_id' 
        )->distinct('subjects.id');
    }

    public function academicSession()
    {
        return $this->belongsTo(AcademicSession::class, 'session_id');
    }

    public function getSessionNameAttribute()
    {
        return $this->academicSession ? $this->academicSession->session : null;
    }
}