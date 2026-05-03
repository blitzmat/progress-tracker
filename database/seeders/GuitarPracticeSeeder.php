<?php

namespace Database\Seeders;

use App\Models\Goal;
use App\Models\Activity;
use Illuminate\Database\Seeder;

class GuitarPracticeSeeder extends Seeder
{
    public function run(): void
    {
        // Define the phases – each phase is a Goal
        $phases = [
            [
                'phase' => '🟢 Phase 1: Absolute Beginner (Months 0–3)',
                'description' => 'Comfortable hold, basic open chords, simple rhythm, play two easy songs.',
                'activities' => [
                    'Warm-up: Finger stretches & chromatic exercise (1-2-3-4)',
                    'Open Chords: E, A, D, G, C, Am, Em – clean switches at 60 bpm',
                    'Strumming: All downstrokes, then D-D-U-U-D-U pattern',
                    'Melody & Notes: Natural notes on E and A strings, Twinkle Twinkle',
                    'Song Practice: 2–4 chord song (Knockin\' on Heaven\'s Door)',
                ],
            ],
            [
                'phase' => '🟡 Phase 2: Late Beginner (Months 3–9)',
                'description' => 'Barre chords, power chords, minor pentatonic scale, intro to lead playing.',
                'activities' => [
                    'Warm-up: Chromatic spider walk alternate picking',
                    'Barre Chords: E-shape & A-shape (F, Bm) – check each string',
                    'Power Chords: Movable shape, play riffs (Smoke on the Water)',
                    'Scale & Lead: Minor pentatonic Box 1, phrasing (slides, hammers, pulls)',
                    'Rhythm: 16th-note strums, palm muting, funk scratches',
                    'Ear & Repertoire: Transcribe simple riffs, play along with recordings',
                ],
            ],
            [
                'phase' => '🟠 Phase 3: Intermediate (1–3 Years)',
                'description' => 'Fretboard fluency, major scale, CAGED system, solid rhythm, blues/rock improvisation.',
                'activities' => [
                    'Technique: Finger independence drills, 3nps major scale, bending accuracy',
                    'Chords & Harmony: CAGED shapes, 7th chords, funk/reggae/shuffle rhythms',
                    'Scales & Theory: 5 pentatonic boxes connected, harmonized major scale',
                    'Improvisation: 12-bar blues chord-tone targeting, call & response',
                    'Ear Training: Transcribe solos (BB King, Clapton), identify triads',
                    'Repertoire: Full songs with intros, solos, endings – loop/backing track',
                ],
            ],
            [
                'phase' => '🔵 Phase 4: Advanced (3–6 Years)',
                'description' => 'Advanced techniques, modal improvisation, complex harmony, transcription ability.',
                'activities' => [
                    'Technique: Paul Gilbert sequences, legato, sweep picking (3 & 5 strings)',
                    'Harmony: Modes over drone, drop-2 voicings, altered/dominant scales',
                    'Improvisation: Jazz standards (Autumn Leaves), chromatic enclosures',
                    'Transcription: Solos by SRV, Eric Johnson, Charlie Parker heads on guitar',
                    'Ear Training: Chord progression recognition, sing then play',
                    'Repertoire: Challenging instrumentals (Satch, Vai, Tim Henson)',
                ],
            ],
            [
                'phase' => '🟣 Phase 5: Expert / Professional (5+ Years)',
                'description' => 'Distinctive voice, effortless technique, mastery to teach and perform professionally.',
                'activities' => [
                    'Artistic Voice: Compose original pieces, develop signature tone',
                    'Targeted Work: Identify weaknesses (odd meters, sight-reading)',
                    'High-Level Transcribing: Adapt sax/piano solos to guitar',
                    'Performance: Live playing, studio sessions, self-recording analysis',
                    'Teaching: Mentor others, explain concepts',
                    'Exploration: Study a new style every year (flamenco, bebop, etc.)',
                ],
            ],
        ];

        foreach ($phases as $phase) {
            /** @var Goal $goal */
            $goal = Goal::create([
                'user_id'    => null,          // null = system default, visible to all users
                'name'       => $phase['phase'],
                'description' => $phase['description'],
            ]);

            foreach ($phase['activities'] as $activityName) {
                Activity::create([
                    'user_id'     => null,         // system default activity
                    'goal_id'     => $goal->id,
                    'name'        => $activityName,
                    'description' => null,
                ]);
            }
        }
    }
}
