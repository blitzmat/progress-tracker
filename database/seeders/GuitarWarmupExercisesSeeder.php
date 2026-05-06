<?php

namespace Database\Seeders;

use App\Models\Stage;
use App\Models\Activity;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

class GuitarWarmupExercisesSeeder extends Seeder
{
    public function run(): void
    {
        $exercises = [
            // ── Beginner: Chromatic Scale ──────────────
            [
                'stage_name' => 'Stage 1: Beginner',
                'activities' => [
                    [
                        'name' => 'Chromatic Scale (1‑2‑3‑4)',
                        'description' => 'Classic chromatic exercise walking up the strings.',
                        'alpha_tab' => [
                            'tex' => $this->chromaticTex(),
                            'default_tempo' => 60,
                            'default_volume' => 0.5,
                            'default_time_signature' => '4/4',
                        ],
                    ],
                ],
            ],

            // ── Early Intermediate: Dexterity Exercise ─
            [
                'stage_name' => 'Stage 2: Early Intermediate',
                'activities' => [
                    [
                        'name' => 'Spider Walk Dexterity',
                        'description' => 'Finger independence spider walk across strings.',
                        'alpha_tab' => [
                            'tex' => $this->spiderTex(),
                            'default_tempo' => 80,
                            'default_volume' => 0.5,
                            'default_time_signature' => '4/4',
                        ],
                    ],
                ],
            ],

            // ── Intermediate: Minor Pentatonic Scale ───
            [
                'stage_name' => 'Stage 3: Intermediate',
                'activities' => [
                    [
                        'name' => 'Minor Pentatonic Scale (Box 1)',
                        'description' => 'A minor pentatonic scale, ascending and descending.',
                        'alpha_tab' => [
                            'tex' => $this->pentatonicTex(),
                            'default_tempo' => 100,
                            'default_volume' => 0.5,
                            'default_time_signature' => '4/4',
                        ],
                    ],
                ],
            ],

            // ── Advanced: Trill Exercise ───────────────
            [
                'stage_name' => 'Stage 4: Advanced',
                'activities' => [
                    [
                        'name' => 'Trill Exercise (1‑2, 2‑3, 3‑4)',
                        'description' => 'Trills between adjacent fingers on every string.',
                        'alpha_tab' => [
                            'tex' => $this->trillTex(),
                            'default_tempo' => 120,
                            'default_volume' => 0.5,
                            'default_time_signature' => '4/4',
                        ],
                    ],
                ],
            ],
        ];

        foreach ($exercises as $section) {
            $stage = Stage::where('name', 'like', '%' . $section['stage_name'] . '%')->first();
            if (!$stage) {
                $this->command->warn("Stage '{$section['stage_name']}' not found. Skipping.");
                continue;
            }

            foreach ($section['activities'] as $data) {
                // Avoid duplicates
                $exists = Activity::where('name', $data['name'])
                    ->where('goal_id', optional($stage->goals->first())->id) // only if the stage has a goal
                    ->exists();

                if ($exists) {
                    $this->command->line("Activity '{$data['name']}' already exists. Skipping.");
                    continue;
                }

                // Create the activity under the stage’s first goal
                $goal = $stage->goals->first();
                if (!$goal) {
                    $this->command->warn("No goal found for stage '{$stage->name}'. Skipping '{$data['name']}'.");
                    continue;
                }

                Activity::create([
                    'user_id'         => null,
                    'goal_id'         => $goal->id,
                    'name'            => $data['name'],
                    'description'     => $data['description'],
                    'widget_settings' => [
                        'alpha_tab' => $data['alpha_tab'],
                    ],
                ]);

                $this->command->info("Created activity '{$data['name']}' in stage '{$stage->name}'.");
            }
        }
    }

    // ─── alphaTex generators ─────────────────────────

    private function chromaticTex(): string
    {
        // Walk up 1-2-3-4 on each string (6th to 1st), eighth notes
        $bars = [];
        $strings = [6, 5, 4, 3, 2, 1];
        $frets = [1, 2, 3, 4];
        $bar = '';
        foreach ($strings as $s) {
            foreach ($frets as $f) {
                $bar .= "$f.$s ";
            }
        }
        $bars[] = rtrim($bar);
        // Repeat the pattern for a total of 4 bars
        $melody = implode(' | ', array_fill(0, 4, $bars[0]));

        return "\\tuning E4 B3 G3 D3 A2 E2\n\\staff{score tabs}\n:8 $melody";
    }

    private function spiderTex(): string
    {
        // Spider walk: 1.6 2.5 3.4 4.3 1.5 2.4 3.3 4.2 etc.
        $pairs = [
            [1,6], [2,5], [3,4], [4,3],
            [1,5], [2,4], [3,3], [4,2],
            [1,4], [2,3], [3,2], [4,1],
            [1,3], [2,2], [3,1], [4,6],
        ];
        $notes = implode(' ', array_map(fn($p) => "{$p[0]}.{$p[1]}", $pairs));
        $melody = "$notes | $notes | $notes | $notes";

        return "\\tuning E4 B3 G3 D3 A2 E2\n\\staff{score tabs}\n:16 $melody";
    }

    private function pentatonicTex(): string
    {
        // A minor pentatonic box 1, eighth notes
        $asc = '5.6 8.6 5.5 7.5 5.4 7.4 5.3 7.3 5.2 8.2 5.1 8.1';
        $desc = '8.1 5.1 8.2 5.2 7.3 5.3 7.4 5.4 7.5 5.5 8.6 5.6';
        $melody = "$asc | $desc | $asc | $desc";

        return "\\tuning E4 B3 G3 D3 A2 E2\n\\staff{score tabs}\n:8 $melody";
    }

    private function trillTex(): string
    {
        // Trills: 1h2p1h2p... on each string, 16th notes
        $trillPairs = [
            ['1.6', '2.6'], ['2.5', '3.5'], ['3.4', '4.4'],
            ['4.3', '3.3'], ['3.2', '2.2'], ['2.1', '1.1'],
        ];
        $bars = [];
        foreach ($trillPairs as [$a, $b]) {
            $bars[] = "$a {h} $b {p} $a {h} $b {p} $a {h} $b {p} $a {h} $b {p}";
        }
        $melody = implode(' | ', $bars);

        return "\\tuning E4 B3 G3 D3 A2 E2\n\\staff{score tabs}\n:16 $melody";
    }
}
