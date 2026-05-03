<?php

namespace Database\Seeders;

use App\Models\Stage;
use App\Models\Goal;
use App\Models\Activity;
use Illuminate\Database\Seeder;

class GuitarMasterySeeder extends Seeder
{
    public function run(): void
    {
        $stages = $this->getStages();

        foreach ($stages as $index => $stageData) {
            $stage = Stage::create([
                'name'        => $stageData['name'],
                'description' => $stageData['description'],
                'order'       => $index + 1,
            ]);

            // Create the goal for this stage
            $goal = Goal::create([
                'user_id'     => null,            // system default
                'stage_id'    => $stage->id,
                'name'        => $stageData['goal_name'],
                'description' => $stageData['goal_description'],
            ]);

            // Create activities (daily routine + exercises)
            foreach ($stageData['activities'] as $activityName) {
                Activity::create([
                    'user_id'     => null,
                    'goal_id'     => $goal->id,
                    'name'        => $activityName,
                    'description' => null,
                ]);
            }
        }
    }

    private function getStages(): array
    {
        return [
            [
                'name'             => '🟢 Stage 1: Beginner (0–3 months)',
                'description'      => 'Build foundational coordination, basic chords, and rhythm.',
                'goal_name'        => 'Stage 1: Beginner',
                'goal_description' => 'Build foundational coordination, basic chords, and rhythm.',
                'activities'       => [
                    'Warm-up: Finger stretches & 1-2-3-4 exercise (10 min)',
                    'Chord transitions: G ↔ C, D ↔ A, etc. (15 min)',
                    'Strumming patterns: Down-up basics (10 min)',
                    'Learn simple songs (15 min)',
                    'Play with a metronome (10 min)',
                    'Spider exercise (1-2-3-4 across strings)',
                    'Chord switching drill (1 chord per bar)',
                    'Basic strum: ↓ ↓ ↑ ↑ ↓ ↑',
                ],
            ],
            [
                'name'             => '🟡 Stage 2: Early Intermediate (3–9 months)',
                'description'      => 'Expand chord vocabulary and introduce lead playing.',
                'goal_name'        => 'Stage 2: Early Intermediate',
                'goal_description' => 'Expand chord vocabulary and introduce lead playing.',
                'activities'       => [
                    'Warm-ups + finger independence (10 min)',
                    'Barre chords + rhythm (15 min)',
                    'Pentatonic scale (all positions gradually) (15 min)',
                    'Simple solos / licks (10 min)',
                    'Song practice (10 min)',
                    'Minor pentatonic Box 1 (key of A)',
                    'Alternate picking drills',
                    'Barre chord endurance (hold + strum)',
                ],
            ],
            [
                'name'             => '🟠 Stage 3: Intermediate (9–24 months)',
                'description'      => 'Develop musicality, improvisation, and fretboard knowledge.',
                'goal_name'        => 'Stage 3: Intermediate',
                'goal_description' => 'Develop musicality, improvisation, and fretboard knowledge.',
                'activities'       => [
                    'Warm-ups (10 min)',
                    'Scales + technique (bends, vibrato) (20 min)',
                    'Improvisation with backing tracks (20 min)',
                    'Full song practice (20 min)',
                    'Theory application (10 min)',
                    '3-notes-per-string scales',
                    'Bending to pitch drill (use tuner)',
                    'Call-and-response improvisation',
                ],
            ],
            [
                'name'             => '🔵 Stage 4: Advanced (2–4 years)',
                'description'      => 'Master technique, expand theory, and develop a unique voice.',
                'goal_name'        => 'Stage 4: Advanced',
                'goal_description' => 'Master technique, expand theory, and develop a unique voice.',
                'activities'       => [
                    'Technique drills (15 min)',
                    'Scales/modes across neck (25 min)',
                    'Improvisation (target chord tones) (25 min)',
                    'Challenging repertoire (25 min)',
                    'Ear training (10 min)',
                    'Arpeggio sweeps (triads → 7ths)',
                    'Modal improvisation over backing tracks',
                    'Hybrid picking patterns',
                ],
            ],
            [
                'name'             => '🔴 Stage 5: Expert (4–5+ years)',
                'description'      => 'Total musical freedom, composition, and professional-level playing.',
                'goal_name'        => 'Stage 5: Expert',
                'goal_description' => 'Total musical freedom, composition, and professional-level playing.',
                'activities'       => [
                    'Composition & songwriting',
                    'Advanced improvisation (outside playing)',
                    'Jazz harmony & reharmonization',
                    'Studio recording practice',
                    'Transcribe full songs/solos by ear',
                    'Modal interchange studies',
                    'Polyrhythm exercises',
                    'Genre specialisation deep dive',
                ],
            ],
        ];
    }
}
