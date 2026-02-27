<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Quiz;
use App\Models\QuizQuestion;
use App\Models\Scenario;
use App\Models\Tutorial;
use Illuminate\Database\Seeder;

class LearningSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedScenarios();
        $this->seedTutorials();
        $this->seedQuizzes();
    }

    private function seedScenarios(): void
    {
        $scenarios = [
            [
                'title' => 'Walk-in Guest Check-In',
                'description' => 'Practice handling a walk-in guest from reservation creation to check-in.',
                'module' => 'fo',
                'difficulty' => 'beginner',
                'instructions' => '<p>In this scenario, you will practice the complete walk-in guest check-in process:</p><ol><li>Create a new reservation for a walk-in guest</li><li>Assign a room to the reservation</li><li>Process the check-in</li><li>Verify the guest appears in the resident guest list</li></ol>',
                'objectives' => [
                    ['objective' => 'Create a reservation with walk-in source'],
                    ['objective' => 'Assign an available room'],
                    ['objective' => 'Complete the check-in process'],
                    ['objective' => 'Verify guest in resident list'],
                ],
                'is_active' => true,
            ],
            [
                'title' => 'Group Reservation',
                'description' => 'Handle a group reservation with multiple rooms and special arrangements.',
                'module' => 'fo',
                'difficulty' => 'intermediate',
                'instructions' => '<p>Process a group reservation for a corporate event:</p><ol><li>Create a group reservation for 5 rooms</li><li>Assign appropriate room categories</li><li>Apply a group arrangement/rate</li><li>Check-in the group</li></ol>',
                'objectives' => [
                    ['objective' => 'Create a group reservation'],
                    ['objective' => 'Assign 5 rooms with correct categories'],
                    ['objective' => 'Apply group arrangement'],
                    ['objective' => 'Process group check-in'],
                ],
                'is_active' => true,
            ],
            [
                'title' => 'Guest Check-Out with Bill Settlement',
                'description' => 'Practice the full check-out process including bill review and settlement.',
                'module' => 'fo',
                'difficulty' => 'intermediate',
                'instructions' => '<p>Complete a guest check-out with proper bill handling:</p><ol><li>Review guest charges and postings</li><li>Print the invoice preview</li><li>Settle the bill</li><li>Process the check-out</li></ol>',
                'objectives' => [
                    ['objective' => 'Review all guest charges'],
                    ['objective' => 'Print invoice preview'],
                    ['objective' => 'Settle the bill with correct payment'],
                    ['objective' => 'Process check-out'],
                ],
                'is_active' => true,
            ],
            [
                'title' => 'Room Status Management',
                'description' => 'Learn to manage room statuses through the housekeeping workflow.',
                'module' => 'hk',
                'difficulty' => 'beginner',
                'instructions' => '<p>Practice the housekeeping room status workflow:</p><ol><li>View current room status board</li><li>Assign yourself to a dirty room</li><li>Update room status through cleaning stages</li><li>Mark room as inspected and ready</li></ol>',
                'objectives' => [
                    ['objective' => 'View room status board'],
                    ['objective' => 'Accept a housekeeping task'],
                    ['objective' => 'Update room status to clean'],
                    ['objective' => 'Complete room inspection'],
                ],
                'is_active' => true,
            ],
            [
                'title' => 'Guest Profile Management',
                'description' => 'Create and manage guest profiles with contacts and segments.',
                'module' => 'sales',
                'difficulty' => 'beginner',
                'instructions' => '<p>Practice managing guest profiles:</p><ol><li>Create a new guest profile</li><li>Add contact information</li><li>Assign market segments</li><li>Create a membership card</li></ol>',
                'objectives' => [
                    ['objective' => 'Create a new guest profile'],
                    ['objective' => 'Add at least 2 contact methods'],
                    ['objective' => 'Assign a market segment'],
                    ['objective' => 'Create a membership card'],
                ],
                'is_active' => true,
            ],
            [
                'title' => 'Call Log Management',
                'description' => 'Practice logging and managing telephone calls.',
                'module' => 'telop',
                'difficulty' => 'beginner',
                'instructions' => '<p>Practice the telephone operator workflow:</p><ol><li>Log an incoming external call</li><li>Log a room-to-room internal call</li><li>View the guest list for call routing</li></ol>',
                'objectives' => [
                    ['objective' => 'Log an external incoming call'],
                    ['objective' => 'Log an internal room-to-room call'],
                    ['objective' => 'Use the guest list for call routing'],
                ],
                'is_active' => true,
            ],
        ];

        foreach ($scenarios as $data) {
            Scenario::create($data);
        }
    }

    private function seedTutorials(): void
    {
        $tutorials = [
            [
                'title' => 'Creating a Reservation',
                'module' => 'fo',
                'description' => 'Step-by-step guide to creating a new hotel reservation.',
                'target_page' => '/fo/reservations/create',
                'steps' => [
                    ['title' => 'Open Create Reservation', 'content' => 'Click the "New Reservation" button on the Reservations page to begin.', 'element' => null, 'placement' => 'center'],
                    ['title' => 'Fill Guest Details', 'content' => 'Select or create a guest profile. Fill in the guest name and contact information.', 'element' => null, 'placement' => 'bottom'],
                    ['title' => 'Set Dates', 'content' => 'Choose the arrival and departure dates. The system will calculate the number of nights automatically.', 'element' => null, 'placement' => 'bottom'],
                    ['title' => 'Select Room Category', 'content' => 'Choose the desired room category. Check availability for your selected dates.', 'element' => null, 'placement' => 'bottom'],
                    ['title' => 'Apply Arrangement', 'content' => 'Select the rate arrangement. This determines pricing and included services.', 'element' => null, 'placement' => 'bottom'],
                    ['title' => 'Save Reservation', 'content' => 'Review all details and click "Create" to save the reservation.', 'element' => null, 'placement' => 'center'],
                ],
                'is_active' => true,
                'sort_order' => 1,
            ],
            [
                'title' => 'Processing Check-In',
                'module' => 'fo',
                'description' => 'How to check in a guest with a confirmed reservation.',
                'target_page' => '/fo/check-in',
                'steps' => [
                    ['title' => 'Open Check-In Page', 'content' => 'Navigate to the Check-In page from the navigation menu.', 'element' => null, 'placement' => 'center'],
                    ['title' => 'Find Reservation', 'content' => 'Search for the reservation by guest name, reservation number, or date.', 'element' => null, 'placement' => 'bottom'],
                    ['title' => 'Assign Room', 'content' => 'Select an available room from the list. The room must match the reserved category.', 'element' => null, 'placement' => 'bottom'],
                    ['title' => 'Complete Check-In', 'content' => 'Confirm the details and process the check-in. The guest is now a resident.', 'element' => null, 'placement' => 'center'],
                ],
                'is_active' => true,
                'sort_order' => 2,
            ],
            [
                'title' => 'Room Status Workflow',
                'module' => 'hk',
                'description' => 'Understanding the housekeeping room status lifecycle.',
                'target_page' => '/hk',
                'steps' => [
                    ['title' => 'Room Status Board', 'content' => 'The dashboard shows all rooms with their current status: Dirty, Clean, Inspected, or Occupied.', 'element' => null, 'placement' => 'center'],
                    ['title' => 'Accept a Task', 'content' => 'Click on a room with "Dirty" status and accept the cleaning task assigned to you.', 'element' => null, 'placement' => 'bottom'],
                    ['title' => 'Update Status', 'content' => 'After cleaning, update the room status to "Clean" to notify the supervisor.', 'element' => null, 'placement' => 'bottom'],
                    ['title' => 'Supervisor Inspection', 'content' => 'The supervisor inspects the room and marks it as "Inspected" — ready for guests.', 'element' => null, 'placement' => 'center'],
                ],
                'is_active' => true,
                'sort_order' => 1,
            ],
            [
                'title' => 'Managing Guest Profiles',
                'module' => 'sales',
                'description' => 'How to create and maintain guest profiles.',
                'target_page' => '/sales/guests/create',
                'steps' => [
                    ['title' => 'Guest List', 'content' => 'View all registered guests from the Guest List in the Sales panel.', 'element' => null, 'placement' => 'center'],
                    ['title' => 'Create New Guest', 'content' => 'Click "New Guest" to create a profile. Fill in name, nationality, and ID information.', 'element' => null, 'placement' => 'bottom'],
                    ['title' => 'Add Contacts', 'content' => 'In the Contacts tab, add email, phone, and other contact methods.', 'element' => null, 'placement' => 'bottom'],
                    ['title' => 'Assign Segments', 'content' => 'Assign market segments to categorize the guest for marketing and reporting.', 'element' => null, 'placement' => 'bottom'],
                ],
                'is_active' => true,
                'sort_order' => 1,
            ],
            [
                'title' => 'Logging Calls',
                'module' => 'telop',
                'description' => 'How to log incoming and outgoing calls.',
                'target_page' => '/telop/call-logs/create',
                'steps' => [
                    ['title' => 'Open Call Log', 'content' => 'Navigate to Call Logs in the Telephone Operator panel.', 'element' => null, 'placement' => 'center'],
                    ['title' => 'Create New Log', 'content' => 'Click "New Call Log" to record a new call.', 'element' => null, 'placement' => 'bottom'],
                    ['title' => 'Fill Call Details', 'content' => 'Select direction (incoming/outgoing), type (internal/external), and fill in caller/receiver information.', 'element' => null, 'placement' => 'bottom'],
                    ['title' => 'Save the Log', 'content' => 'Add any notes and save the call log record.', 'element' => null, 'placement' => 'center'],
                ],
                'is_active' => true,
                'sort_order' => 1,
            ],
        ];

        foreach ($tutorials as $data) {
            Tutorial::create($data);
        }
    }

    private function seedQuizzes(): void
    {
        // ── Front Office Quiz ──
        $foQuiz = Quiz::create([
            'title' => 'Front Office Fundamentals',
            'module' => 'fo',
            'description' => 'Test your knowledge of front office operations including reservations, check-in/out, and billing.',
            'passing_score' => 70,
            'time_limit_minutes' => 15,
            'is_active' => true,
        ]);

        $foQuestions = [
            [
                'question' => 'What is the first step when a walk-in guest arrives at the hotel?',
                'type' => 'multiple_choice',
                'options' => [
                    ['option' => 'Process check-in immediately'],
                    ['option' => 'Create a reservation first'],
                    ['option' => 'Ask guest to wait'],
                    ['option' => 'Assign a room number'],
                ],
                'correct_answer' => 'Create a reservation first',
                'explanation' => 'Even for walk-in guests, a reservation record must be created first to track the booking in the system.',
                'sort_order' => 1,
            ],
            [
                'question' => 'A reservation must have an assigned room before check-in.',
                'type' => 'true_false',
                'options' => null,
                'correct_answer' => 'true',
                'explanation' => 'A room must be assigned during the check-in process to know which room the guest will occupy.',
                'sort_order' => 2,
            ],
            [
                'question' => 'What does VIP status on a reservation indicate?',
                'type' => 'multiple_choice',
                'options' => [
                    ['option' => 'The guest gets a discount'],
                    ['option' => 'The guest requires special attention and amenities'],
                    ['option' => 'The guest has a long-term booking'],
                    ['option' => 'The guest is a travel agent'],
                ],
                'correct_answer' => 'The guest requires special attention and amenities',
                'explanation' => 'VIP status flags the reservation for special treatment, such as room upgrades, welcome amenities, or manager attention.',
                'sort_order' => 3,
            ],
            [
                'question' => 'Which report shows all currently checked-in guests?',
                'type' => 'multiple_choice',
                'options' => [
                    ['option' => 'Reservation List'],
                    ['option' => 'Resident Guest List'],
                    ['option' => 'History List'],
                    ['option' => 'Available Room List'],
                ],
                'correct_answer' => 'Resident Guest List',
                'explanation' => 'The Resident Guest List shows all guests who are currently checked in and occupying rooms.',
                'sort_order' => 4,
            ],
            [
                'question' => 'A guest can check out without settling their bill.',
                'type' => 'true_false',
                'options' => null,
                'correct_answer' => 'false',
                'explanation' => 'All charges must be settled or transferred before a guest can check out.',
                'sort_order' => 5,
            ],
        ];

        foreach ($foQuestions as $q) {
            $foQuiz->questions()->create($q);
        }

        // ── Housekeeping Quiz ──
        $hkQuiz = Quiz::create([
            'title' => 'Housekeeping Basics',
            'module' => 'hk',
            'description' => 'Test your understanding of housekeeping procedures and room status management.',
            'passing_score' => 70,
            'time_limit_minutes' => 10,
            'is_active' => true,
        ]);

        $hkQuestions = [
            [
                'question' => 'What is the correct room status after a guest checks out?',
                'type' => 'multiple_choice',
                'options' => [
                    ['option' => 'Clean'],
                    ['option' => 'Dirty'],
                    ['option' => 'Inspected'],
                    ['option' => 'Occupied'],
                ],
                'correct_answer' => 'Dirty',
                'explanation' => 'After check-out, the room automatically changes to Dirty status and needs to be cleaned.',
                'sort_order' => 1,
            ],
            [
                'question' => 'A room attendant can mark a room as "Inspected" after cleaning.',
                'type' => 'true_false',
                'options' => null,
                'correct_answer' => 'false',
                'explanation' => 'Only a supervisor can mark a room as Inspected after verifying the cleaning quality.',
                'sort_order' => 2,
            ],
            [
                'question' => 'What does OOO status mean for a room?',
                'type' => 'multiple_choice',
                'options' => [
                    ['option' => 'Out of Order — room cannot be sold'],
                    ['option' => 'Out of Office — staff is away'],
                    ['option' => 'Oversold Order — extra booking'],
                    ['option' => 'On Official Occupancy'],
                ],
                'correct_answer' => 'Out of Order — room cannot be sold',
                'explanation' => 'OOO means the room is Out of Order, typically due to maintenance issues, and cannot be assigned to guests.',
                'sort_order' => 3,
            ],
        ];

        foreach ($hkQuestions as $q) {
            $hkQuiz->questions()->create($q);
        }

        // ── Sales Quiz ──
        $salesQuiz = Quiz::create([
            'title' => 'Sales & Marketing Essentials',
            'module' => 'sales',
            'description' => 'Test your knowledge of guest management, segments, and marketing concepts.',
            'passing_score' => 70,
            'time_limit_minutes' => 10,
            'is_active' => true,
        ]);

        $salesQuestions = [
            [
                'question' => 'What is the purpose of market segmentation in hotel sales?',
                'type' => 'multiple_choice',
                'options' => [
                    ['option' => 'To organize room cleaning schedules'],
                    ['option' => 'To categorize guests for targeted marketing and analysis'],
                    ['option' => 'To set room prices'],
                    ['option' => 'To manage staff schedules'],
                ],
                'correct_answer' => 'To categorize guests for targeted marketing and analysis',
                'explanation' => 'Market segments help the hotel understand their guest demographics and tailor marketing efforts.',
                'sort_order' => 1,
            ],
            [
                'question' => 'A guest can belong to multiple market segments.',
                'type' => 'true_false',
                'options' => null,
                'correct_answer' => 'true',
                'explanation' => 'Guests can be tagged with multiple segments (e.g., Corporate and Repeater).',
                'sort_order' => 2,
            ],
            [
                'question' => 'What benefit does a membership card provide?',
                'type' => 'multiple_choice',
                'options' => [
                    ['option' => 'Room key access'],
                    ['option' => 'Loyalty rewards, discounts, and special recognition'],
                    ['option' => 'Free breakfast only'],
                    ['option' => 'Staff identification'],
                ],
                'correct_answer' => 'Loyalty rewards, discounts, and special recognition',
                'explanation' => 'Membership cards are part of loyalty programs offering various benefits to repeat guests.',
                'sort_order' => 3,
            ],
        ];

        foreach ($salesQuestions as $q) {
            $salesQuiz->questions()->create($q);
        }

        // ── TelOp Quiz ──
        $telopQuiz = Quiz::create([
            'title' => 'Telephone Operator Basics',
            'module' => 'telop',
            'description' => 'Test your understanding of telephone operator duties and call handling.',
            'passing_score' => 70,
            'time_limit_minutes' => 10,
            'is_active' => true,
        ]);

        $telopQuestions = [
            [
                'question' => 'What information must be recorded for every telephone call?',
                'type' => 'multiple_choice',
                'options' => [
                    ['option' => 'Only the room number'],
                    ['option' => 'Direction, type, caller/receiver, and duration'],
                    ['option' => 'Only the guest name'],
                    ['option' => 'Only the time of call'],
                ],
                'correct_answer' => 'Direction, type, caller/receiver, and duration',
                'explanation' => 'A complete call log includes direction (in/out), type (internal/external), parties involved, and duration.',
                'sort_order' => 1,
            ],
            [
                'question' => 'Internal calls between rooms do not need to be logged.',
                'type' => 'true_false',
                'options' => null,
                'correct_answer' => 'false',
                'explanation' => 'All calls should be logged for billing accuracy and record-keeping purposes.',
                'sort_order' => 2,
            ],
            [
                'question' => 'Where can you find which guest is in which room for call routing?',
                'type' => 'multiple_choice',
                'options' => [
                    ['option' => 'Reservation List'],
                    ['option' => 'Guest List (TelOp panel)'],
                    ['option' => 'Call Log History'],
                    ['option' => 'Room Category List'],
                ],
                'correct_answer' => 'Guest List (TelOp panel)',
                'explanation' => 'The TelOp Guest List shows current residents with their room assignments for call routing.',
                'sort_order' => 3,
            ],
        ];

        foreach ($telopQuestions as $q) {
            $telopQuiz->questions()->create($q);
        }
    }
}
