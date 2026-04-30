<?php
/*
Plugin Name: E-Server Radio Player
Plugin URI: https://merchan.dev
Description: Reproductor de Barra Horizontal Premium (v2.2). Diseño Ultra-Responsive optimizado para móviles y pantallas táctiles.
Version: 2.2.0
Author: Espressivo Venezuela | Merchan.Dev
Author URI: https://merchan.dev
License: Proprietary
*/

if (!defined('ABSPATH')) exit;

class EServer_Radio_Plugin {

    const EXPIRY_DATE = '2027-05-28T23:59:59';
    const STREAM_URL = 'https://radio.diarioeloriental.com/radio.aac';

    public function __construct() {
        add_shortcode('eserver_radio', array($this, 'render_shortcode'));
    }

    public function render_shortcode() {
        ob_start();
        ?>
        <!-- Google Fonts & Icons -->
        <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;700;900&family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" />

        <div class="es-resp-wrapper">
            <div class="es-resp-bar" id="esRespBar">
                
                <!-- Lock Screen -->
                <div class="es-resp-lock" id="esRespLock">
                    <span class="material-symbols-rounded">lock</span>
                    <p>EXPIRADO</p>
                </div>

                <!-- PLAY SECTION -->
                <div class="es-resp-play-zone">
                    <div class="es-resp-halo"></div>
                    <button class="es-resp-btn" id="esRespPlay" onclick="toggleRespRadio()">
                        <span class="material-symbols-rounded" id="esRespIcon">play_arrow</span>
                    </button>
                </div>

                <!-- INFO & PROGRESS SECTION -->
                <div class="es-resp-main-zone">
                    <div class="es-resp-meta">
                        <span class="es-resp-status" id="esRespStatus"></span>
                        <div class="es-resp-live">
                            <span class="es-resp-dot"></span>
                            <span>EN VIVO</span>
                        </div>
                    </div>
                    <div class="es-resp-track-wrap">
                        <div class="es-resp-track">
                            <div class="es-resp-fill" id="esRespFill"></div>
                            <div class="es-resp-waves">
                                <?php for($i=0; $i<40; $i++): ?>
                                    <div class="es-resp-w" style="--d: <?php echo $i * 0.04; ?>s"></div>
                                <?php endfor; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- VOLUME SECTION -->
                <div class="es-resp-vol-zone">
                    <button class="es-resp-mute" onclick="toggleRespMute()">
                        <span class="material-symbols-rounded" id="esRespMuteIcon">volume_up</span>
                    </button>
                    <div class="es-resp-vol-slider">
                        <input type="range" id="esRespVol" min="0" max="1" step="0.01" value="1" oninput="updateRespVol()">
                    </div>
                </div>
            </div>
        </div>

        <audio id="esRespAudio" preload="none"></audio>

        <style>
            .es-resp-wrapper {
                width: 100%;
                max-width: 100%;
                display: flex;
                justify-content: center;
                padding: 20px 10px;
                box-sizing: border-box;
                font-family: 'Inter', sans-serif;
            }

            .es-resp-bar {
                position: relative;
                width: 100%;
                max-width: 650px;
                height: 84px;
                background: rgba(13, 17, 28, 0.9);
                backdrop-filter: blur(40px);
                -webkit-backdrop-filter: blur(40px);
                border: 1px solid rgba(255, 255, 255, 0.08);
                border-radius: 24px;
                display: flex;
                align-items: center;
                padding: 0 20px;
                box-shadow: 0 30px 60px -12px rgba(0, 0, 0, 0.6);
                overflow: hidden;
                transition: all 0.3s ease;
            }

            /* Lock */
            .es-resp-lock {
                display: none;
                position: absolute;
                inset: 0;
                background: #000;
                z-index: 100;
                align-items: center;
                justify-content: center;
                gap: 12px;
                color: #ff3333;
                font-weight: 900;
                font-size: 0.8rem;
                letter-spacing: 2px;
            }
            .es-resp-lock.active { display: flex; }

            /* Play Zone */
            .es-resp-play-zone { position: relative; flex-shrink: 0; margin-right: 20px; }
            .es-resp-halo {
                position: absolute; inset: -8px;
                background: radial-gradient(circle, rgba(239, 68, 68, 0.3) 0%, transparent 70%);
                border-radius: 50%; opacity: 0; transition: opacity 0.5s;
            }
            .es-playing .es-resp-halo { opacity: 1; animation: esH 2s infinite; }
            @keyframes esH { 0%, 100% { transform: scale(1); opacity: 0.2; } 50% { transform: scale(1.3); opacity: 0.4; } }

            .es-resp-btn {
                width: 56px; height: 56px;
                border-radius: 18px;
                background: linear-gradient(135deg, #ff4444 0%, #ff8800 100%);
                border: none; cursor: pointer;
                display: flex; align-items: center; justify-content: center;
                color: #fff; box-shadow: 0 10px 20px rgba(255, 68, 68, 0.4);
                transition: transform 0.2s cubic-bezier(0.175, 0.885, 0.32, 1.275);
                position: relative; z-index: 2;
            }
            .es-resp-btn:hover { transform: scale(1.05); }
            .es-resp-btn .material-symbols-rounded { font-size: 34px; font-variation-settings: 'FILL' 1; }

            /* Main Zone */
            .es-resp-main-zone { flex: 1; min-width: 0; display: flex; flex-direction: column; gap: 8px; }
            .es-resp-meta { display: flex; justify-content: space-between; align-items: center; }
            .es-resp-status { color: rgba(255,255,255,0.3); font-size: 0.7rem; font-weight: 700; letter-spacing: 0.5px; }
            .es-resp-live { display: flex; align-items: center; gap: 6px; color: #ff4444; font-size: 0.65rem; font-weight: 900; letter-spacing: 1.5px; }
            .es-resp-dot { width: 7px; height: 7px; background: #ff4444; border-radius: 50%; box-shadow: 0 0 10px #ff4444; }
            .es-playing .es-resp-dot { animation: esP 1.5s infinite; }
            @keyframes esP { 0%, 100% { transform: scale(1); opacity: 0.4; } 50% { transform: scale(1.2); opacity: 1; } }

            .es-resp-track-wrap { position: relative; width: 100%; height: 8px; background: rgba(255,255,255,0.05); border-radius: 10px; overflow: hidden; }
            .es-resp-fill { position: absolute; left: 0; top: 0; height: 100%; width: 0%; background: linear-gradient(90deg, #ff4444, #ff8800); z-index: 2; transition: width 0.1s linear; }
            .es-resp-waves { position: absolute; inset: 0; display: flex; align-items: center; justify-content: space-between; padding: 0 2px; z-index: 1; opacity: 0.2; }
            .es-resp-w { width: 1px; height: 60%; background: #fff; }
            .es-playing .es-resp-w { animation: esW 0.8s infinite alternate; animation-delay: var(--d); }
            @keyframes esW { from { transform: scaleY(0.4); opacity: 0.3; } to { transform: scaleY(1.4); opacity: 1; } }

            /* Vol Zone */
            .es-resp-vol-zone { margin-left: 20px; display: flex; align-items: center; gap: 12px; }
            .es-resp-mute { background: none; border: none; cursor: pointer; color: rgba(255,255,255,0.25); transition: color 0.2s; }
            .es-resp-mute:hover { color: #fff; }
            .es-resp-vol-slider { width: 90px; display: flex; align-items: center; }
            #esRespVol { width: 100%; -webkit-appearance: none; height: 2px; background: rgba(255,255,255,0.1); border-radius: 2px; outline: none; }
            #esRespVol::-webkit-slider-thumb { -webkit-appearance: none; width: 12px; height: 12px; background: #fff; border-radius: 50%; cursor: pointer; box-shadow: 0 0 10px rgba(0,0,0,0.5); }

            /* RESPONSIVE BREAKPOINTS */
            @media (max-width: 600px) {
                .es-resp-bar { height: 76px; padding: 0 15px; border-radius: 20px; }
                .es-resp-vol-slider { width: 60px; }
                .es-resp-play-zone { margin-right: 15px; }
                .es-resp-btn { width: 50px; height: 50px; border-radius: 15px; }
                .es-resp-btn .material-symbols-rounded { font-size: 30px; }
            }

            @media (max-width: 480px) {
                .es-resp-vol-slider { display: none; } /* Ocultar slider en movil pequeño */
                .es-resp-vol-zone { margin-left: 10px; }
                .es-resp-bar { height: 70px; padding: 0 12px; }
                .es-resp-live span:last-child { display: none; } /* Solo mostrar el punto rojo en movil muy pequeño si no cabe */
                .es-resp-live { padding-right: 5px; }
            }

            @media (max-width: 360px) {
                .es-resp-play-zone { margin-right: 10px; }
                .es-resp-btn { width: 44px; height: 44px; border-radius: 12px; }
                .es-resp-btn .material-symbols-rounded { font-size: 26px; }
            }
        </style>

        <script>
            (function() {
                const EXPIRY = new Date('<?php echo self::EXPIRY_DATE; ?>');
                const STREAM = '<?php echo self::STREAM_URL; ?>';
                let playing = false;

                const audio  = document.getElementById('esRespAudio');
                const btn    = document.getElementById('esRespPlay');
                const icon   = document.getElementById('esRespIcon');
                const status = document.getElementById('esRespStatus');
                const fill   = document.getElementById('esRespFill');
                const lock   = document.getElementById('esRespLock');
                const bar    = document.getElementById('esRespBar');
                const vol    = document.getElementById('esRespVol');
                const mIcon  = document.getElementById('esRespMuteIcon');

                function check() {
                    if (new Date() >= EXPIRY) {
                        lock.classList.add('active');
                        if (playing) stop();
                        return true;
                    }
                    return false;
                }

                window.toggleRespRadio = function() {
                    if (check()) return;
                    playing ? stop() : start();
                };

                function start() {
                    audio.src = STREAM + '?t=' + Date.now();
                    status.innerText = 'CONECTANDO...';
                    audio.play().then(() => {
                        playing = true;
                        bar.classList.add('es-playing');
                        icon.innerText = 'pause';
                        status.innerText = '';
                        animateFill();
                    }).catch(() => {
                        status.innerText = 'ERROR';
                    });
                }

                function stop() {
                    audio.pause(); audio.src = '';
                    playing = false;
                    bar.classList.remove('es-playing');
                    icon.innerText = 'play_arrow';
                    status.innerText = 'DETENIDO';
                    fill.style.width = '0%';
                }

                function animateFill() {
                    if (!playing) return;
                    let p = 0;
                    const interval = setInterval(() => {
                        if (!playing) { clearInterval(interval); return; }
                        p = (p + 0.1) % 100;
                        fill.style.width = p + '%';
                    }, 100);
                }

                window.updateRespVol = function() {
                    audio.volume = vol.value;
                    mIcon.innerText = vol.value == 0 ? 'volume_off' : 'volume_up';
                };

                window.toggleRespMute = function() {
                    if (audio.volume > 0) {
                        audio.v = audio.volume; audio.volume = 0; vol.value = 0;
                        mIcon.innerText = 'volume_off';
                    } else {
                        audio.volume = audio.v || 1; vol.value = audio.volume;
                        mIcon.innerText = 'volume_up';
                    }
                };

                check();
                setInterval(check, 30000);
            })();
        </script>
        <?php
        return ob_get_clean();
    }
}

new EServer_Radio_Plugin();
