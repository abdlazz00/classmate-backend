<?php

namespace App\Jobs;

use App\Models\Assignment;
use App\Models\WaGroup;
use App\Services\FonnteService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class BroadcastAssignmentToWa implements ShouldQueue
{
    use Queueable, InteractsWithQueue, SerializesModels;
    public $assignment;

    /**
     * Create a new job instance.
     */
    public function __construct(Assignment $assignment)
    {
        $this->assignment = $assignment;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $assignment = Assignment::with('course')->find($this->assignment->id);

        if (!$assignment) return;

        $course = $assignment->course;
        $kelas = $course->class_name;

        $group = WaGroup::where('class_name', $kelas)->first();
        if (!$group) return;

        $msg = "📝 *TUGAS BARU*\n";
        $msg .= "Mata Kuliah: *{$course->name}*\n";
        $msg .= "Judul: *{$assignment->title}*\n";
        $msg .= "Deadline: *" . $assignment->deadline->format('d M Y H:i') . "*\n";
        $msg .= "\n{$assignment->description}";

        FonnteService::sendMessage($group->group_code, $msg);
    }
}
