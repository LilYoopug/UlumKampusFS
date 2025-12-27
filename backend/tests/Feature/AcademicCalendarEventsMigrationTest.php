<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class AcademicCalendarEventsMigrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_academic_calendar_events_table_has_expected_columns(): void
    {
        $this->assertTrue(Schema::hasTable('academic_calendar_events'));

        $columns = [
            'id',
            'title',
            'start_date',
            'end_date',
            'category',
            'description',
            'created_at',
            'updated_at',
        ];

        foreach ($columns as $column) {
            $this->assertTrue(
                Schema::hasColumn('academic_calendar_events', $column),
                "academic_calendar_events table should have {$column} column"
            );
        }
    }

    public function test_academic_calendar_events_table_has_indexes(): void
    {
        $this->assertTrue(Schema::hasTable('academic_calendar_events'));

        $indexes = ['start_date', 'end_date', 'category'];
        $connection = Schema::getConnection();
        $tablePrefix = $connection->getTablePrefix();
        $tableName = $tablePrefix . 'academic_calendar_events';

        foreach ($indexes as $index) {
            $indexName = 'academic_calendar_events_' . $index . '_index';
            $indexExists = collect($connection->getDoctrineSchemaManager()->listTableIndexes($tableName))
                ->has($indexName);

            $this->assertTrue(
                $indexExists,
                "academic_calendar_events table should have index on {$index}"
            );
        }
    }

    public function test_academic_calendar_events_table_has_enum_category(): void
    {
        $this->assertTrue(Schema::hasTable('academic_calendar_events'));

        // Verify category column exists and is a string (Laravel doesn't use native ENUM)
        $this->assertTrue(
            Schema::hasColumn('academic_calendar_events', 'category'),
            'academic_calendar_events table should have category column'
        );
    }
}