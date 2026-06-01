<!DOCTYPE html>

<html class="dark" lang="en"><head>
<meta charset="utf-8"/>
<meta content="width=device-width, initial-scale=1.0" name="viewport"/>
<title>Game Modes | QuizArena</title>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&amp;display=swap" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet"/>
<script id="tailwind-config">
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    colors: {
                        "on-surface": "#faf8fe",
                        "primary-fixed-dim": "#9c7eff",
                        "outline": "#75757a",
                        "on-secondary-fixed": "#004624",
                        "on-primary-fixed-variant": "#32008a",
                        "surface-container-lowest": "#000000",
                        "secondary": "#00fd93",
                        "surface": "#0d0e12",
                        "outline-variant": "#47484c",
                        "on-error-container": "#ffb2b9",
                        "secondary-fixed-dim": "#00ed89",
                        "secondary-container": "#006d3c",
                        "surface-bright": "#2a2c32",
                        "on-error": "#490013",
                        "tertiary-container": "#ff00d4",
                        "on-tertiary-container": "#0e000a",
                        "surface-container": "#18191e",
                        "surface-container-highest": "#24262b",
                        "surface-dim": "#0d0e12",
                        "surface-container-low": "#121317",
                        "on-surface-variant": "#abaab0",
                        "on-secondary": "#005b31",
                        "surface-variant": "#24262b",
                        "on-primary-fixed": "#000000",
                        "tertiary-dim": "#ff5ed6",
                        "surface-container-high": "#1e1f25",
                        "tertiary-fixed-dim": "#ff6bd7",
                        "on-primary": "#340090",
                        "on-primary-container": "#280072",
                        "inverse-primary": "#6834eb",
                        "on-tertiary-fixed-variant": "#6f005b",
                        "primary": "#b6a0ff",
                        "primary-fixed": "#a98fff",
                        "inverse-surface": "#faf8fe",
                        "secondary-dim": "#00ed89",
                        "on-tertiary-fixed": "#34002a",
                        "primary-container": "#a98fff",
                        "surface-tint": "#b6a0ff",
                        "primary-dim": "#7e51ff",
                        "on-secondary-container": "#e2ffe6",
                        "error": "#ff6e84",
                        "background": "#0d0e12",
                        "secondary-fixed": "#00fd93",
                        "error-dim": "#d73357",
                        "error-container": "#a70138",
                        "tertiary": "#ff5ed6",
                        "on-background": "#faf8fe",
                        "inverse-on-surface": "#545559",
                        "tertiary-fixed": "#ff87da",
                        "on-secondary-fixed-variant": "#006638",
                        "on-tertiary": "#430036"
                    },
                    borderRadius: {
                        "DEFAULT": "1rem",
                        "lg": "2rem",
                        "xl": "3rem",
                        "full": "9999px"
                    },
                    fontFamily: {
                        "headline": ["Plus Jakarta Sans"],
                        "display": ["Plus Jakarta Sans"],
                        "body": ["Plus Jakarta Sans"],
                        "label": ["Plus Jakarta Sans"]
                    }
                }
            }
        }
    </script>
<script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
<style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #0d0e12; color: #faf8fe; }
        .material-symbols-outlined { font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24; }
        .glass-effect { backdrop-filter: blur(20px); -webkit-backdrop-filter: blur(20px); }
        .text-gradient-primary { background: linear-gradient(135deg, #7e51ff 0%, #b6a0ff 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
        .btn-gradient { background: linear-gradient(135deg, #7e51ff 0%, #b6a0ff 100%); }
        .neon-glow-secondary:hover { box-shadow: 0 0 20px rgba(0, 253, 147, 0.3); }
        .neon-glow-primary:hover { box-shadow: 0 0 20px rgba(126, 81, 255, 0.3); }
        .hide-scrollbar::-webkit-scrollbar { display: none; }

        /* Floating Animation */
        @keyframes floating {
            0% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
            100% { transform: translateY(0px); }
        }
        .animate-float { animation: floating 6s ease-in-out infinite; }

        /* Status Badge Pulse */
        @keyframes status-pulse {
            0% { transform: scale(1); opacity: 1; }
            50% { transform: scale(1.05); opacity: 0.8; }
            100% { transform: scale(1); opacity: 1; }
        }
        .pulse-badge { animation: status-pulse 2s ease-in-out infinite; }

        /* Entrance Animations */
        @keyframes slide-up-fade {
            0% { opacity: 0; transform: translateY(40px); }
            100% { opacity: 1; transform: translateY(0); }
        }
        .entrance-anim { animation: slide-up-fade 0.8s cubic-bezier(0.2, 0.8, 0.2, 1) forwards; opacity: 0; }
        .delay-100 { animation-delay: 0.1s; }
        .delay-200 { animation-delay: 0.2s; }
        .delay-300 { animation-delay: 0.3s; }
        .delay-400 { animation-delay: 0.4s; }

        /* Mouse Follow Glow */
        .mouse-glow-container { position: relative; overflow: hidden; }
        .mouse-glow {
            position: absolute;
            width: 400px;
            height: 400px;
            background: radial-gradient(circle, rgba(126, 81, 255, 0.15) 0%, transparent 70%);
            border-radius: 50%;
            pointer-events: none;
            transform: translate(-50%, -50%);
            left: var(--mouse-x, -50%);
            top: var(--mouse-y, -50%);
            transition: opacity 0.3s ease;
            opacity: 0;
            z-index: 1;
        }
        .mouse-glow-container:hover .mouse-glow { opacity: 1; }

        /* Specific Glow Colors */
        .glow-secondary .mouse-glow { background: radial-gradient(circle, rgba(0, 253, 147, 0.15) 0%, transparent 70%); }
        .glow-tertiary .mouse-glow { background: radial-gradient(circle, rgba(255, 94, 214, 0.15) 0%, transparent 70%); }

        @media (prefers-reduced-motion: reduce) {
            .animate-float, .pulse-badge, .entrance-anim, .mouse-glow {
                animation: none !important;
                transition: none !important;
                opacity: 1 !important;
                transform: none !important;
            }
        }
    </style>
</head>
<body class="bg-background text-on-surface selection:bg-primary/30 min-h-screen overflow-x-hidden">
<!-- Top Navigation Bar -->
<nav class="fixed top-0 w-full z-50 bg-[#0d0e12]/80 backdrop-blur-xl shadow-[0px_24px_48px_rgba(0,0,0,0.5)] flex justify-between items-center h-20 px-8">
<div class="text-2xl font-black italic tracking-tighter text-transparent bg-clip-text bg-gradient-to-br from-[#7e51ff] to-[#b6a0ff] cursor-pointer">
            QuizArena
        </div>
<div class="hidden md:flex items-center gap-8">
<a class="text-[#abaab0] hover:text-[#faf8fe] transition-colors font-bold tracking-tight" href="#">Dashboard</a>
<a class="text-[#abaab0] hover:text-[#faf8fe] transition-colors font-bold tracking-tight" href="#">Create Quiz</a>
<a class="text-[#abaab0] hover:text-[#faf8fe] transition-colors font-bold tracking-tight" href="#">Leaderboard</a>
</div>
<div class="flex items-center gap-4">
<button class="p-2 text-[#abaab0] hover:bg-[#1e1f25] rounded-full transition-all duration-300 active:scale-95">
<span class="material-symbols-outlined">notifications</span>
</button>
<div class="h-10 w-10 rounded-full border-2 border-primary overflow-hidden cursor-pointer active:scale-95 duration-150">
<img alt="User avatar with XP level indicator" data-alt="A high-quality 3D rendered profile avatar of a young gamer with vibrant purple neon highlights on their headset." src="https://lh3.googleusercontent.com/aida-public/AB6AXuDRzF80oOQ5T_ABelnF3ib7sZ1iJ_G3pUqJ3ZfaJSiKPp80qbuL3NaohUirDF2f1tNiGreuHLa6LHmHynlNO2lkSE5z2J9fCTVZj9pa6-fqZ6KPcUAOTp5FfPA5VkB17WRaUwEjHcE2yyKJ0FVNGhN7kRXawpnWB6oGdYSkxRxb45S9ws91NZiMWgPoXA4izboSlEt0FyE_8TdDd9jxk6zUnOzbbNaw3YqYmxxSj5bfJWjxcpIFUvpQ4cMRx8CnImF9pna-b-0WtK4"/>
</div>
</div>
</nav>
<!-- Main Content Canvas -->
<main class="pt-32 pb-24 px-6 md:px-12 max-w-7xl mx-auto">
<!-- Hero Section -->
<section class="text-center mb-20 relative">
<div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[800px] h-[800px] bg-[#7c4dff]/10 blur-[120px] rounded-full -z-10"></div>
<div class="absolute top-1/4 right-0 w-[400px] h-[400px] bg-[#ff00d4]/10 blur-[100px] rounded-full -z-10"></div>
<div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-surface-container-high mb-6 active:scale-95 transition-transform cursor-pointer">
<span class="material-symbols-outlined text-secondary text-sm" style="font-variation-settings: 'FILL' 1;">stars</span>
<span class="text-xs font-bold tracking-widest uppercase text-on-surface-variant">Battle Royale Coming Soon</span>
</div>
<h1 class="text-6xl md:text-8xl font-black tracking-tighter mb-6">
                Game <span class="text-gradient-primary">Modes</span>
</h1>
<p class="text-xl md:text-2xl font-bold text-on-surface-variant max-w-3xl mx-auto leading-relaxed">
                Choose your challenge and compete your way in QuizArena. 
                <span class="text-on-surface">Supporting multiple ways to play, from casual training to high-stakes competitive multiplayer.</span>
</p>
</section>
<!-- Back Button -->
<div class="mb-12 flex justify-start">
<button class="group flex items-center gap-2 px-6 py-3 rounded-full bg-surface-container hover:bg-surface-container-high transition-all duration-300 border border-outline-variant/10" onclick="window.history.back()">
<span class="material-symbols-outlined text-primary group-hover:-translate-x-1 transition-transform">arrow_back</span>
<span class="font-bold">Back to Home</span>
</button>
</div>
<!-- Game Modes Bento Grid -->
<section class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-12 gap-6 mb-24">
<!-- Standard Mode - Wide -->
<div class="lg:col-span-8 bg-[#18191e] rounded-xl overflow-hidden relative group transition-all duration-500 animate-float entrance-anim mouse-glow-container" style="animation-delay: 0.1s;">
<div class="mouse-glow"></div>
<div class="absolute inset-0 bg-gradient-to-br from-primary/5 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-700"></div>
<div class="p-8 md:p-12 flex flex-col h-full relative z-10">
<div class="flex justify-between items-start mb-8">
<div class="flex flex-col gap-1">
<span class="px-4 py-1 bg-secondary/10 text-secondary text-xs font-black uppercase tracking-widest rounded-full w-fit pulse-badge">Available</span>
<h3 class="text-4xl font-black tracking-tight mt-2">Standard Mode</h3>
</div>
<span class="material-symbols-outlined text-5xl text-primary/40">school</span>
</div>
<p class="text-on-surface-variant text-lg leading-relaxed mb-8 max-w-xl">
                        Play classic quizzes across various categories. Answer questions, improve your knowledge, earn points, and climb the rankings in a stress-free environment.
                    </p>
<div class="grid grid-cols-2 gap-4 mb-10">
<div class="flex items-center gap-3 text-sm font-bold text-on-surface/80">
<span class="material-symbols-outlined text-secondary" style="font-variation-settings: 'FILL' 1;">check_circle</span> Unlimited quizzes
                        </div>
<div class="flex items-center gap-3 text-sm font-bold text-on-surface/80">
<span class="material-symbols-outlined text-secondary" style="font-variation-settings: 'FILL' 1;">check_circle</span> Multiple categories
                        </div>
<div class="flex items-center gap-3 text-sm font-bold text-on-surface/80">
<span class="material-symbols-outlined text-secondary" style="font-variation-settings: 'FILL' 1;">check_circle</span> Leaderboards
                        </div>
<div class="flex items-center gap-3 text-sm font-bold text-on-surface/80">
<span class="material-symbols-outlined text-secondary" style="font-variation-settings: 'FILL' 1;">check_circle</span> Personal statistics
                        </div>
</div>
<div class="mt-auto">
<a href="/register.php" class="inline-block px-10 py-4 btn-gradient text-on-primary font-black rounded-full shadow-lg shadow-primary/20 hover:scale-105 active:scale-95 transition-all neon-glow-primary text-center">
                            Play Now
                        </a>
</div>
</div>
<div class="absolute bottom-0 right-0 w-64 h-64 bg-primary/10 rounded-tl-[100px] blur-3xl -z-10 group-hover:bg-primary/20 transition-all"></div>
</div>
<!-- Duel Mode -->
<div class="lg:col-span-4 bg-[#18191e] rounded-xl overflow-hidden transition-all duration-500 group relative animate-float delay-100 entrance-anim mouse-glow-container glow-secondary" style="animation-delay: 0.2s;">
<div class="mouse-glow"></div>
<div class="absolute inset-0 bg-gradient-to-t from-secondary/5 to-transparent opacity-0 group-hover:opacity-100 transition-opacity"></div>
<div class="p-8 h-full flex flex-col relative z-10">
<div class="flex justify-between items-start mb-6">
<span class="px-4 py-1 bg-surface-container-highest text-on-surface-variant text-xs font-black uppercase tracking-widest rounded-full">In Progress</span>
<span class="material-symbols-outlined text-4xl text-secondary/30">swords</span>
</div>
<h3 class="text-3xl font-black tracking-tight mb-4">Duel Mode</h3>
<p class="text-on-surface-variant text-sm leading-relaxed mb-8">
                        Challenge another player in a real-time head-to-head quiz battle. Faster answers and better accuracy determine the winner.
                    </p>
<ul class="space-y-3 mb-10 text-xs font-bold text-on-surface-variant">
<li class="flex items-center gap-2">• 1 vs 1 gameplay</li>
<li class="flex items-center gap-2">• Real-time competition</li>
<li class="flex items-center gap-2">• Ranked matches</li>
<li class="flex items-center gap-2">• Skill-based matchmaking</li>
</ul>
<div class="mt-auto">
<button class="w-full py-4 bg-surface-container-highest text-on-surface-variant font-bold rounded-full cursor-not-allowed border border-outline-variant/10">
                            Coming Soon
                        </button>
</div>
</div>
</div>
<!-- Battle Royale -->
<div class="lg:col-span-6 bg-[#18191e] rounded-xl overflow-hidden transition-all duration-500 group relative animate-float delay-200 entrance-anim mouse-glow-container glow-tertiary" style="animation-delay: 0.3s;">
<div class="mouse-glow"></div>
<div class="p-8 md:p-10 h-full flex flex-col md:flex-row gap-8 relative z-10">
<div class="flex-1 flex flex-col">
<div class="flex justify-between items-start mb-6">
<span class="px-4 py-1 bg-surface-container-highest text-on-surface-variant text-xs font-black uppercase tracking-widest rounded-full">In Progress</span>
<span class="material-symbols-outlined text-4xl text-tertiary/30">groups</span>
</div>
<h3 class="text-3xl font-black tracking-tight mb-4">Battle Royale</h3>
<p class="text-on-surface-variant text-sm leading-relaxed mb-6">
                            Up to 100 players compete in a massive quiz battle. One wrong answer can put you at risk, and only one player remains victorious.
                        </p>
<ul class="space-y-2 mb-8 text-xs font-bold text-on-surface-variant">
<li class="flex items-center gap-2">• Up to 100 players</li>
<li class="flex items-center gap-2">• Elimination rounds</li>
<li class="flex items-center gap-2">• Live rankings</li>
<li class="flex items-center gap-2">• Last player standing wins</li>
</ul>
<div class="mt-auto">
<button class="w-full py-4 bg-surface-container-highest text-on-surface-variant font-bold rounded-full cursor-not-allowed border border-outline-variant/10">
                                Coming Soon
                            </button>
</div>
</div>
<div class="hidden md:block w-48 rounded-lg overflow-hidden relative">
<img class="h-full object-cover grayscale group-hover:grayscale-0 transition-all duration-700 opacity-40 group-hover:opacity-100" data-alt="Futuristic colosseum for 100-player Battle Royale mode." src="https://lh3.googleusercontent.com/aida-public/AB6AXuBA8mNmu74nFBkFbuniKUWZGBkIcq2a9VejijGYLKfC8WxFe4T0Jp5fff-kxpjBhtRV3sMhfeTBsKh2xeZ4z112o8bUcYATWKVcDNSqL0Imv7i0NPJRTd5mw3b3WmCF6CGW0cSSgZFbE0hy6Bg3FeOVddWqC0C0tz0mFTum-kQahzA3qwNibGZsh7QAJszTAyytc0I4A57Mw8L8C438NhnAOVZDxrH4fw-4_Hi_MUzvuppVXlTAmOEk8SJEhvRkWXC3H8u54ldIXHc"/>
</div>
</div>
</div>
<!-- Millionaire Mode -->
<div class="lg:col-span-6 bg-[#18191e] rounded-xl overflow-hidden transition-all duration-500 group relative animate-float delay-300 entrance-anim mouse-glow-container" style="animation-delay: 0.4s;">
<div class="mouse-glow"></div>
<div class="p-8 md:p-10 h-full flex flex-col md:flex-row gap-8 relative z-10">
<div class="flex-1 flex flex-col">
<div class="flex justify-between items-start mb-6">
<span class="px-4 py-1 bg-surface-container-highest text-on-surface-variant text-xs font-black uppercase tracking-widest rounded-full">In Progress</span>
<span class="material-symbols-outlined text-4xl text-primary/30">diamond</span>
</div>
<h3 class="text-3xl font-black tracking-tight mb-4">Millionaire Mode</h3>
<p class="text-on-surface-variant text-sm leading-relaxed mb-6">
                            Answer 14 increasingly difficult questions on your journey to one million points. A single incorrect answer ends the game instantly.
                        </p>
<ul class="space-y-2 mb-8 text-xs font-bold text-on-surface-variant">
<li class="flex items-center gap-2">• 14-question progression</li>
<li class="flex items-center gap-2">• Increasing difficulty</li>
<li class="flex items-center gap-2">• High-risk gameplay</li>
<li class="flex items-center gap-2">• Millionaire challenge</li>
</ul>
<div class="mt-auto">
<button class="w-full py-4 bg-surface-container-highest text-on-surface-variant font-bold rounded-full cursor-not-allowed border border-outline-variant/10">
                                Coming Soon
                            </button>
</div>
</div>
<div class="hidden md:block w-48 rounded-lg overflow-hidden relative">
<img class="h-full object-cover grayscale group-hover:grayscale-0 transition-all duration-700 opacity-40 group-hover:opacity-100" data-alt="Elegant high-pressure millionaire-style quiz challenge." src="https://lh3.googleusercontent.com/aida-public/AB6AXuAtogEOMrvoFvX3BotU9oYYAYDUKdw8ji2GnvnshA5pv4cSgISDk11paDtGZiDPwnxXcGNM4zHqJQN6CMrlYHGgRQmSP4ZAAgXB_lpwRNWL_rn4y1DMw5h6H2cerfRWeJTOXNrKpRuXpsUy6HyqXmTwBdhZzQH7QYFw9RxXD06M0rFt_j0kgf5H5wlBh34yhMTKQ7OmmR5NEl3bMWM5zOnmmBms_LlccGIr-bCOhsmEiuQpysM-r9thNJQyigtcQ4Lo7m2Cr3xkYE8"/>
</div>
</div>
</div>
</section>
<!-- Roadmap Section -->
<section class="py-20 border-t border-outline-variant/10">
<div class="flex flex-col lg:flex-row gap-16 items-center">
<div class="lg:w-1/2">
<h2 class="text-4xl md:text-5xl font-black tracking-tight mb-6">Future <span class="text-gradient-primary">Competitive</span> Experiences</h2>
<p class="text-on-surface-variant text-lg mb-10 leading-relaxed max-w-xl">
                        QuizArena is constantly evolving. We're building a world-class platform where knowledge meets high-stakes competition. More game modes, tournaments, seasonal events, and competitive features are currently in development.
                    </p>
<div class="space-y-6">
<div class="flex items-center gap-6 group">
<div class="w-12 h-12 rounded-full bg-surface-container-high flex items-center justify-center border border-primary/20 group-hover:bg-primary/20 transition-colors">
<span class="text-primary font-bold">Q3</span>
</div>
<div>
<h4 class="font-bold text-on-surface">Seasonal Tournament Circuit</h4>
<p class="text-sm text-on-surface-variant">Official leagues with exclusive cosmetic rewards.</p>
</div>
</div>
<div class="flex items-center gap-6 group">
<div class="w-12 h-12 rounded-full bg-surface-container-high flex items-center justify-center border border-secondary/20 group-hover:bg-secondary/20 transition-colors">
<span class="text-secondary font-bold">Q4</span>
</div>
<div>
<h4 class="font-bold text-on-surface">Clan Wars &amp; Team Play</h4>
<p class="text-sm text-on-surface-variant">Join forces and dominate group leaderboards.</p>
</div>
</div>
</div>
</div>
<div class="lg:w-1/2 w-full">
<div class="relative bg-surface-container rounded-xl p-1 overflow-hidden entrance-anim" style="animation-delay: 0.5s;">
<div class="bg-background rounded-lg p-8 h-full flex items-center justify-center min-h-[300px] border border-outline-variant/5">
<div class="grid grid-cols-3 gap-4 w-full h-full">
<div class="h-40 rounded-lg bg-surface-container-high animate-pulse"></div>
<div class="h-64 rounded-lg bg-surface-container animate-pulse delay-75"></div>
<div class="h-32 rounded-lg bg-surface-container-highest animate-pulse delay-150"></div>
<div class="h-20 rounded-lg bg-surface-container animate-pulse delay-300"></div>
<div class="h-48 rounded-lg bg-surface-container-high animate-pulse delay-200"></div>
<div class="h-36 rounded-lg bg-surface-container animate-pulse delay-100"></div>
</div>
<div class="absolute inset-0 flex items-center justify-center backdrop-blur-sm bg-background/40">
<div class="text-center p-6 glass-effect bg-surface-container-high/80 rounded-xl border border-outline-variant/20 shadow-2xl">
<span class="material-symbols-outlined text-primary text-5xl mb-4" style="font-variation-settings: 'FILL' 1;">construction</span>
<h3 class="text-2xl font-black italic">Roadmap In Progress</h3>
</div>
</div>
</div>
</div>
</div>
</div>
</section>
</main>
<!-- Footer -->
<footer class="bg-[#0d0e12] w-full py-12 border-t border-[#1e1f25]/50">
<div class="grid grid-cols-1 md:grid-cols-4 gap-8 px-12 max-w-7xl mx-auto">
<div class="space-y-4">
<div class="text-lg font-bold text-[#faf8fe]">QuizArena</div>
<p class="text-sm font-medium text-[#abaab0]">© 2024 QuizArena. The Neon Arena Awaits.</p>
</div>
<div class="flex flex-col gap-4">
<h4 class="text-on-surface font-bold text-sm">Platform</h4>
<a class="text-sm font-medium text-[#abaab0] hover:text-[#b6a0ff] transition-colors" href="#">Sitemap</a>
<a class="text-sm font-medium text-[#abaab0] hover:text-[#b6a0ff] transition-colors" href="#">Support</a>
</div>
<div class="flex flex-col gap-4">
<h4 class="text-on-surface font-bold text-sm">Legal</h4>
<a class="text-sm font-medium text-[#abaab0] hover:text-[#b6a0ff] transition-colors" href="#">Privacy Policy</a>
<a class="text-sm font-medium text-[#abaab0] hover:text-[#b6a0ff] transition-colors" href="#">Terms of Service</a>
</div>
<div class="flex flex-col gap-4">
<h4 class="text-on-surface font-bold text-sm">Connect</h4>
<div class="flex gap-4">
<a class="w-10 h-10 rounded-full bg-surface-container-high flex items-center justify-center hover:text-primary transition-all" href="#">
<span class="material-symbols-outlined">public</span>
</a>
<a class="w-10 h-10 rounded-full bg-surface-container-high flex items-center justify-center hover:text-primary transition-all" href="#">
<span class="material-symbols-outlined">alternate_email</span>
</a>
</div>
</div>
</div>
</footer>
<script>
        // Micro-interactions and effects
        document.querySelectorAll('button').forEach(btn => {
            btn.addEventListener('mousedown', () => btn.classList.add('scale-95'));
            btn.addEventListener('mouseup', () => btn.classList.remove('scale-95'));
        });

        // Mouse follow glow effect logic
        const cards = document.querySelectorAll('.mouse-glow-container');
        cards.forEach(card => {
            card.addEventListener('mousemove', (e) => {
                const rect = card.getBoundingClientRect();
                const x = e.clientX - rect.left;
                const y = e.clientY - rect.top;
                card.style.setProperty('--mouse-x', `${x}px`);
                card.style.setProperty('--mouse-y', `${y}px`);
            });
        });

        // Simple scroll reveal for cards (enhanced for entrance)
        const observerOptions = {
            threshold: 0.1
        };

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('opacity-100', 'translate-y-0');
                    entry.target.classList.remove('opacity-0', 'translate-y-8');
                }
            });
        }, observerOptions);

        document.querySelectorAll('section > div > div:not(.entrance-anim)').forEach(el => {
            el.classList.add('transition-all', 'duration-700', 'opacity-0', 'translate-y-8');
            observer.observe(el);
        });
    </script>
</body></html>