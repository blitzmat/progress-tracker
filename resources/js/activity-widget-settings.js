window.activityWidgetSettings = function ($wire, isEdit = false) {
    const propertyName = isEdit ? 'editingWidgetSettings' : 'widgetSettings';

    return {
        metronome: null,
        finger: null,

        init() {
            const settings = $wire.get(propertyName) || {};
            this.metronome = settings.metronome || null;
            this.finger = settings.finger_warmup || null;
        },

        toggleMetronome() {
            if (this.metronome?.tempo) {
                this.metronome = null;
            } else {
                this.metronome = {
                    tempo: 120,
                    sound: 'beep',
                    volume: 0.5,
                    time_signature: '4/4'
                };
            }
            this.updateWidgets();
        },

        toggleFingerWarmup() {
            if (this.finger?.note_type) {
                this.finger = null;
            } else {
                this.finger = {
                    note_type: 'quarter',
                    pattern: '1-2-3-4'
                };
            }
            this.updateWidgets();
        },

        updateWidgets() {
            let data = {};
            if (this.metronome) data.metronome = this.metronome;
            if (this.finger) data.finger_warmup = this.finger;
            $wire.set(propertyName, data);
        }
    };
};
