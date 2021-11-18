<?php

namespace Studio\Totem\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

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

        // If there is a venv setup in the python folder we want to use this (Generally used for local install)
        if (is_dir(base_path().'/python/venv')) {
            $output = exec("cd ".base_path()." && source python/venv/bin/activate && python python/scripts/icecatImporter.py {} {} 2>&1");
        }
        // otherwise assume python is currently configured on the server inline with the system requirements
        else {
            $output = exec("cd ".base_path()." && python python/scripts/icecatImporter.py {} {} 2>&1");
        }

        $time_elapsed_secs = microtime(true) - $started;

        // Add a result to the task results table for the task being run
        $this->task->results()->create([
            'ran_at'    => $startDate,
            'duration'  => $time_elapsed_secs * 1000,
            'result'    => $output,
        ]);
    }
}
