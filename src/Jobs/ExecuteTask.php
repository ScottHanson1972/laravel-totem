<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;

class ExecuteTask implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $task;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($task)
    {
        $this->task = $task;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        // Get the date/time the jobs starts
        $started = microtime(true);
        $startDate = date('m/d/Y h:i:s a', time());
        $output = exec("cd ".public_path()." && source python/venv/bin/activate && python python/scripts/icecatImporter.py {} {} 2>&1");

        $time_elapsed_secs = microtime(true) - $started;

        DB::table('task_results')->insert([
            'task_id'   => $this->task->id,
            'ran_at'    => $startDate,
            'duration'  => $time_elapsed_secs * 1000,
            'result'    => $output,
            'created_at' => NOW(),
            'updated_at' => NOW(),
        ]);
    }
}
