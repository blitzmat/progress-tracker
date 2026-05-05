<?php

namespace App\Enums;

enum WidgetType: string
{
    case Metronome = 'metronome';
    case FingerWarmup = 'finger_warmup';
    case AlphaTab = 'alpha_tab';   // ← add this
}
