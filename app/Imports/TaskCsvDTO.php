<?php

namespace App\Imports;

use InvalidArgumentException;

class TaskCsvDTO {

    public $project_title;
    public $task_title;

    public function __construct(string $project_title, string $task_title)
    {
        $this->project_title = $project_title;
        $this->task_title = $task_title;
    }

    public function toArray(): array
    {
        return [
            'project_title' => $this->project_title,
            'task_title' => $this->task_title
        ];
    }
}