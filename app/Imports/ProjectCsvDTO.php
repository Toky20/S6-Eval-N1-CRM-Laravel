<?php

namespace App\Imports;

use InvalidArgumentException;

class ProjectCsvDTO {

    public $project_title;
    public $client_name;

    public function __construct(string $project_title, string $client_name)
    {
        $this->project_title = $project_title;
        $this->client_name = $client_name;
    }

    public function toArray(): array
    {
        return [
            'project_title' => $this->project_title,
            'client_name' => $this->client_name
        ];
    }
}

