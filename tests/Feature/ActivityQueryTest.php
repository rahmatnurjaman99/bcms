<?php

use Spatie\Activitylog\Models\Activity;

test('can filter activities by createdFrom and createdTo', function () {
    // Create activities with different dates
    $pastActivity = Activity::create([
        'log_name' => 'default',
        'description' => 'Past Activity',
        'created_at' => now()->subDays(10),
    ]);

    $targetActivity = Activity::create([
        'log_name' => 'default',
        'description' => 'Target Activity',
        'created_at' => now()->subDays(5),
    ]);

    $futureActivity = Activity::create([
        'log_name' => 'default',
        'description' => 'Future Activity',
        'created_at' => now(),
    ]);

    // Define the date range to capture only the target activity
    $createdFrom = now()->subDays(6)->format('Y-m-d');
    $createdTo = now()->subDays(4)->format('Y-m-d');

    $response = $this->graphQL(
    /** @lang GraphQL */
    '
        query ($createdFrom: Date, $createdTo: Date) {
            activities(createdFrom: $createdFrom, createdTo: $createdTo) {
                data {
                    id
                    description
                    created_at
                }
            }
        }
    ', [
        'createdFrom' => $createdFrom,
        'createdTo' => $createdTo,
    ]);

    $response->assertJsonCount(1, 'data.activities.data');
    $response->assertJsonPath('data.activities.data.0.id', (string) $targetActivity->id);
    $response->assertJsonPath('data.activities.data.0.description', 'Target Activity');
});
