<?php

namespace App\Livewire;

use App\Models\Widget;
use Livewire\Component;

class MetronomeWidget extends Component
{
    public Widget $widget;
    public int $tempo = 120;
    public string $sound = 'beep';
    public float $volume = 0.5;   // [NEW] 0.0 to 1.0
    public bool $isPlaying = false;

    public function mount(Widget $widget): void
    {
        $this->widget = $widget;
        $this->tempo = $widget->settings['tempo'] ?? 120;
        $this->sound = $widget->settings['sound'] ?? 'beep';
        $this->volume = $widget->settings['volume'] ?? 0.5;   // [NEW]
    }

    public function togglePlay(): void
    {
        $this->isPlaying = !$this->isPlaying;
    }

    public function updatedTempo(int $value): void
    {
        $this->saveSettings();
    }

    public function updatedSound(string $value): void
    {
        $this->saveSettings();
    }

    public function updatedVolume(float $value): void   // [NEW]
    {
        $this->saveSettings();
    }

    protected function saveSettings(): void
    {
        $this->widget->update([
            'settings' => [
                'tempo'  => $this->tempo,
                'sound'  => $this->sound,
                'volume' => $this->volume,
            ],
        ]);
    }

    public function render()
    {
        return view('livewire.metronome-widget');
    }
}
