<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\AcademicCalendarEvent;

class AcademicCalendarSeeder extends Seeder
{
    public function run(): void
    {
        // Create academic calendar events based on frontend constants
        $calendarEvents = [
            [
                'id' => 'ACE001',
                'title' => 'event_semester_start',
                'start_date' => $this->getYearDate(2024, 9, 2),
                'end_date' => null,
                'category' => 'academic',
                'description' => 'Mulai Semester Ganjil Tahun Ajaran 2024/2025',
            ],
            [
                'id' => 'ACE002',
                'title' => 'event_mid_terms',
                'start_date' => $this->getYearDate(2024, 10, 21),
                'end_date' => $this->getYearDate(2024, 10, 25),
                'category' => 'exam',
                'description' => 'Ujian Tengah Semester Ganjil 2024/2025',
            ],
            [
                'id' => 'ACE003',
                'title' => 'event_final_terms',
                'start_date' => $this->getYearDate(2024, 12, 16),
                'end_date' => $this->getYearDate(2024, 12, 20),
                'category' => 'exam',
                'description' => 'Ujian Akhir Semester Ganjil 2024/2025',
            ],
            [
                'id' => 'ACE004',
                'title' => 'event_eid_al_fitr',
                'start_date' => $this->getYearDate(2024, 4, 10),
                'end_date' => $this->getYearDate(2024, 4, 11),
                'category' => 'holiday',
                'description' => 'Libur Idul Fitri 1445 H',
            ],
            [
                'id' => 'ACE005',
                'title' => 'event_eid_al_adha',
                'start_date' => $this->getYearDate(2024, 6, 17),
                'end_date' => null,
                'category' => 'holiday',
                'description' => 'Libur Idul Adha 1445 H',
            ],
            [
                'id' => 'ACE006',
                'title' => 'event_new_year',
                'start_date' => $this->getYearDate(2024, 7, 7),
                'end_date' => null,
                'category' => 'holiday',
                'description' => 'Libur Tahun Baru Islam 1446 H',
            ],
            [
                'id' => 'ACE007',
                'title' => 'event_registration',
                'start_date' => $this->getYearDate(2024, 8, 19),
                'end_date' => $this->getYearDate(2024, 8, 30),
                'category' => 'registration',
                'description' => 'Pendaftaran Mahasiswa Baru Tahun 2024',
            ]
        ];

        foreach ($calendarEvents as $eventData) {
            AcademicCalendarEvent::updateOrCreate(
                ['id' => $eventData['id']],
                $eventData
            );
        }
    }

    private function getYearDate($year, $month, $day)
    {
        $date = now();
        $date->year = $year;
        $date->month = $month;
        $date->day = $day;
        $date->hour = 0;
        $date->minute = 0;
        $date->second = 0;
        return $date;
    }
}