<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class GradesMigrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_grades_table_has_expected_columns(): void
    {
        $this->assertTrue(Schema::hasTable('grades'));

        $columns = [
            'id',
            'user_id',
            'course_id',
            'assignment_id',
            'grade',
            'grade_letter',
            'comments',
            'created_at',
            'updated_at',
        ];

        foreach ($columns as $column) {
            $this->assertTrue(
                Schema::hasColumn('grades', $column),
                "grades table should have {$column} column"
            );
        }
    }

    public function test_grades_table_has_foreign_keys(): void
    {
        $this->assertTrue(Schema::hasTable('grades'));

        // Check foreign key constraints
        $this->assertTrue(
            Schema::hasColumn('grades', 'user_id'),
            'grades table should have user_id foreign key'
        );
        $this->assertTrue(
            Schema::hasColumn('grades', 'course_id'),
            'grades table should have course_id foreign key'
        );
        $this->assertTrue(
            Schema::hasColumn('grades', 'assignment_id'),
            'grades table should have assignment_id foreign key'
        );
    }

    public function test_grades_table_has_indexes(): void
    {
        $this->assertTrue(Schema::hasTable('grades'));

        $indexes = ['user_id', 'course_id', 'assignment_id'];
        $connection = Schema::getConnection();
        $tablePrefix = $connection->getTablePrefix();
        $tableName = $tablePrefix . 'grades';

        foreach ($indexes as $index) {
            $indexName = 'grades_' . $index . '_index';
            $indexExists = collect($connection->getDoctrineSchemaManager()->listTableIndexes($tableName))
                ->has($indexName);

            $this->assertTrue(
                $indexExists,
                "grades table should have index on {$index}"
            );
        }
    }
}