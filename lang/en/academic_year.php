<?php

declare(strict_types=1);

return [
    'name' => [
        'required' => 'Academic year name is required.',
        'unique' => 'This academic year name already exists.',
    ],
    'start_date' => [
        'required' => 'Start date is required.',
        'date' => 'Start date must be a valid date.',
    ],
    'end_date' => [
        'required' => 'End date is required.',
        'date' => 'End date must be a valid date.',
        'after' => 'End date must be after start date.',
    ],
];
