/**
 * Audio Synthesizer Nostalgia Jejak Waktu (Web Audio API)
 * Menghasilkan efek suara analog murni: klik rana kamera vintage, flip halaman album, dan kresek vinyl
 */

class VintageAudio {
    constructor() {
        this.audioCtx = null;
        this.vinylNode = null;
        this.vinylGain = null;
        this.isVinylPlaying = false;
        this.soundEnabled = true;
    }

    /** Cek apakah audioCtx siap digunakan */
    _isReady() {
        return this.audioCtx &&
               this.audioCtx.state !== 'closed' &&
               this.audioCtx.state !== 'interrupted';
    }

    init() {
        if (!this.audioCtx) {
            try {
                const AudioContext = window.AudioContext || window.webkitAudioContext;
                if (AudioContext) {
                    this.audioCtx = new AudioContext();
                }
            } catch (e) {
                // Ignore autoplay policy restriction until direct gesture
            }
        }
        if (this.audioCtx && this.audioCtx.state === 'suspended') {
            this.audioCtx.resume().catch(() => {});
        }
    }

    // Efek Shutter Kamera Analog Jadul
    playShutter() {
        if (!this.soundEnabled) return;
        this.init();
        if (!this._isReady()) return;

        try {
            const now = this.audioCtx.currentTime;
            if (now === undefined || now === null) return;

            // Suara 'Klak' cermin mekanis
            const osc1 = this.audioCtx.createOscillator();
            const gain1 = this.audioCtx.createGain();
            osc1.type = 'triangle';
            osc1.frequency.setValueAtTime(320, now);
            osc1.frequency.exponentialRampToValueAtTime(80, now + 0.08);

            gain1.gain.setValueAtTime(0.4, now);
            gain1.gain.exponentialRampToValueAtTime(0.001, now + 0.08);

            osc1.connect(gain1);
            gain1.connect(this.audioCtx.destination);
            osc1.start(now);
            osc1.stop(now + 0.09);

            // Suara 'Klik' rana logam
            const osc2 = this.audioCtx.createOscillator();
            const gain2 = this.audioCtx.createGain();
            osc2.type = 'square';
            osc2.frequency.setValueAtTime(1200, now + 0.04);
            osc2.frequency.exponentialRampToValueAtTime(400, now + 0.12);

            gain2.gain.setValueAtTime(0, now);
            gain2.gain.setValueAtTime(0.25, now + 0.04);
            gain2.gain.exponentialRampToValueAtTime(0.001, now + 0.14);

            osc2.connect(gain2);
            gain2.connect(this.audioCtx.destination);
            osc2.start(now + 0.04);
            osc2.stop(now + 0.15);

            // Noise mekanis winder
            const bufferSize = Math.floor(this.audioCtx.sampleRate * 0.05);
            const buffer = this.audioCtx.createBuffer(1, bufferSize, this.audioCtx.sampleRate);
            const data = buffer.getChannelData(0);
            for (let i = 0; i < bufferSize; i++) {
                data[i] = (Math.random() * 2 - 1) * 0.2;
            }
            const noise = this.audioCtx.createBufferSource();
            noise.buffer = buffer;
            const noiseGain = this.audioCtx.createGain();
            noiseGain.gain.setValueAtTime(0.15, now + 0.09);
            noiseGain.gain.exponentialRampToValueAtTime(0.001, now + 0.18);
            noise.connect(noiseGain);
            noiseGain.connect(this.audioCtx.destination);
            noise.start(now + 0.09);
        } catch (err) {}
    }

    // Efek Balik Halaman Kertas Album (Page Flip)
    playPageFlip() {
        if (!this.soundEnabled) return;
        this.init();
        if (!this._isReady()) return;

        try {
            const now = this.audioCtx.currentTime;
            if (now === undefined || now === null) return;

            const bufferSize = Math.floor(this.audioCtx.sampleRate * 0.15);
            const buffer = this.audioCtx.createBuffer(1, bufferSize, this.audioCtx.sampleRate);
            const data = buffer.getChannelData(0);

            for (let i = 0; i < bufferSize; i++) {
                data[i] = (Math.random() * 2 - 1) * (1 - i / bufferSize);
            }

            const noise = this.audioCtx.createBufferSource();
            noise.buffer = buffer;

            const filter = this.audioCtx.createBiquadFilter();
            filter.type = 'lowpass';
            filter.frequency.setValueAtTime(800, now);
            filter.frequency.exponentialRampToValueAtTime(300, now + 0.15);

            const gain = this.audioCtx.createGain();
            gain.gain.setValueAtTime(0.18, now);
            gain.gain.exponentialRampToValueAtTime(0.001, now + 0.15);

            noise.connect(filter);
            filter.connect(gain);
            gain.connect(this.audioCtx.destination);

            noise.start(now);
        } catch (err) {}
    }

    // Toggle Suara Ambien Kresek Piringan Hitam (Vinyl Dust Crackle)
    toggleVinyl() {
        this.init();
        if (!this.audioCtx) return false;

        if (this.isVinylPlaying) {
            this.stopVinyl();
            return false;
        } else {
            this.startVinyl();
            return true;
        }
    }

    startVinyl() {
        if (!this._isReady() || this.isVinylPlaying) return;

        try {
            const now = this.audioCtx.currentTime;
            if (now === undefined || now === null) return;

            const bufferSize = this.audioCtx.sampleRate * 2;
            const buffer = this.audioCtx.createBuffer(1, bufferSize, this.audioCtx.sampleRate);
            const data = buffer.getChannelData(0);

            for (let i = 0; i < bufferSize; i++) {
                const isClick = Math.random() < 0.0012;
                const clickVal = isClick ? (Math.random() > 0.5 ? 0.3 : -0.3) : 0;
                const hiss = (Math.random() * 2 - 1) * 0.015;
                data[i] = clickVal + hiss;
            }

            this.vinylNode = this.audioCtx.createBufferSource();
            this.vinylNode.buffer = buffer;
            this.vinylNode.loop = true;

            const filter = this.audioCtx.createBiquadFilter();
            filter.type = 'bandpass';
            filter.frequency.value = 1800;
            filter.Q.value = 1.2;

            this.vinylGain = this.audioCtx.createGain();
            this.vinylGain.gain.setValueAtTime(0.08, now);

            this.vinylNode.connect(filter);
            filter.connect(this.vinylGain);
            this.vinylGain.connect(this.audioCtx.destination);

            this.vinylNode.start();
            this.isVinylPlaying = true;
        } catch (err) {}
    }

    stopVinyl() {
        if (this.vinylGain && this._isReady()) {
            try {
                const now = this.audioCtx.currentTime;
                if (now !== undefined && now !== null) {
                    this.vinylGain.gain.exponentialRampToValueAtTime(0.0001, now + 0.3);
                }
            } catch (e) {}
            setTimeout(() => {
                if (this.vinylNode) {
                    try { this.vinylNode.stop(); } catch (e) {}
                    try { this.vinylNode.disconnect(); } catch (e) {}
                    this.vinylNode = null;
                }
                if (this.vinylGain) {
                    try { this.vinylGain.disconnect(); } catch (e) {}
                    this.vinylGain = null;
                }
                this.isVinylPlaying = false;
            }, 350);
        } else {
            this.isVinylPlaying = false;
        }
    }
}

const vintageSound = new VintageAudio();
