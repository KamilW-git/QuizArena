<?php
require_once __DIR__ . '/../vendor/autoload.php';

use QuizArena\Helpers\Auth;
use QuizArena\Helpers\Env;

Env::load(__DIR__ . '/../.env');
Auth::start();

// Jeśli użytkownik jest już zalogowany, przenosimy go prosto do Dashboardu
if (Auth::check()) {
    header('Location: /dashboard.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>QuizArena | Outsmart the Arena</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,400;0,500;0,600;0,700;0,800;1,800&display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>
    
    <link rel="stylesheet" href="/assets/css/main.css?v=2">
    <link rel="stylesheet" href="/assets/css/landing.css">
</head>
<body>

<!-- TopNavBar -->
<nav class="landing-navbar">
    <div class="landing-nav-inner">
        <a href="/" class="landing-nav-logo">QuizArena</a>
        
        <div class="landing-nav-links">
            <a href="/dashboard.php">Dashboard</a>
            <a href="/quiz/create.php">Create Quiz</a>
            <a href="/leaderboard/index.php">Leaderboard</a>
        </div>
        
        <div class="landing-nav-actions">
            <a href="/login.php" class="login-btn">Log In</a>
            <a href="/register.php" class="signup-btn">Sign Up</a>
        </div>
    </div>
</nav>

<main>
    <!-- Hero Section -->
    <section class="landing-hero">
        <div class="hero-bg-1"></div>
        <div class="hero-bg-2"></div>
        
        <div class="hero-content">
            <div class="season-badge">Season 4 Now Live</div>
            <h1 class="landing-headline hero-title">Outsmart<br/>the Arena</h1>
            <p class="hero-subtitle">
                Join the world's most intense competitive quiz platform. Rise through the ranks, claim your glory, and dominate the leaderboard in the ultimate neon-lit knowledge colosseum.
            </p>
            <div class="hero-cta">
                <a href="/register.php" class="btn-hero-primary">PLAY NOW</a>
                <a href="/gameModes.php" class="btn-hero-secondary">View Game Modes</a>
            </div>
        </div>

        <!-- Asymmetric Floating Stats -->
        <div class="floating-stat stat-left">
            <div class="stat-value">12.4k</div>
            <div class="stat-label">Players Active Now</div>
        </div>
        <div class="floating-stat stat-right">
            <div class="stat-value">$5,000</div>
            <div class="stat-label">Monthly Prize Pool</div>
        </div>
    </section>

    <!-- Top Players Section -->
    <section class="champions-section">
        <div class="champions-header">
            <div>
                <h2 class="landing-headline champions-title">Elite Champions</h2>
                <p class="champions-subtitle">The masters of the arena. Do you have what it takes to dethrone them?</p>
            </div>
            <a href="/leaderboard/index.php" class="champions-link">
                Full Leaderboard <span class="material-symbols-outlined">arrow_forward</span>
            </a>
        </div>

        <!-- 3D Style Player Preview -->
        <div class="champion-grid">
            <!-- Rank 2 -->
            <div class="champ-card champ-card--2nd">
                <div class="champ-rank-badge">#2</div>
                <div class="champ-avatar-wrapper">
                    <img alt="Rank 2" class="champ-avatar" src="https://lh3.googleusercontent.com/aida-public/AB6AXuCM9TaMvWKx8pmNLYbsU54uarYTpDxP2hAdJTuL_rYGyxrGPMOVCm_MrTfR-QKS4eBz0_r2K3jhRQfxP3WTw8xVwurcq5ed_JC31ur4QukG4-DLXZ9MpZDUfflp2H0O0lwrP-eauXusVgLc5-VJ-XxwVsOrxFyCh6OLNSQ9BTy_H63dY5v5XW8EoBT8LQNik6FnzT6a5oOr8k46suEPOT25n9C2IlwzqmCy3Hlx8uY1Ft4-Q87kxFb_q5qtoL3CFqET_xcf5ZURLTg"/>
                    <div class="champ-icon"><span class="material-symbols-outlined">bolt</span></div>
                </div>
                <h3 class="champ-name">CyberGhost</h3>
                <div class="champ-score">18,420 PTS</div>
                <div class="champ-bar-bg"><div class="champ-bar-fill"></div></div>
            </div>

            <!-- Rank 1 (Hero Spot) -->
            <div class="champ-card champ-card--1st">
                <div class="champ-rank-badge">WINNER</div>
                <div class="champ-avatar-wrapper">
                    <img alt="Rank 1" class="champ-avatar" src="https://lh3.googleusercontent.com/aida-public/AB6AXuB7SPGIJteg3FyvMBvzssuDkuHqKcxyVe1WVuCD8HK4Hpgm-XiPh9UCFCiHMGjwxXhpH6Lho7QtjD0q0dAguiITR0t7rAvvFX_3j6Z7ptxMU4wulzqv-CH5SF1dAo2J9WgC10fJhfkY6hWd4IWwIR98sZ-yxZB2_B10HBYbX3M837y0yPIhSVK5cwnN_s55G7HOXNG2ZyAftDQ1vn-LJ93F1lOYuuEfyLeEeJIOCE8BQkNiab7XDnrPzCTanp9pdk6aIgido5PXR6E"/>
                    <div class="champ-icon"><span class="material-symbols-outlined" style="font-variation-settings:'FILL' 1;">workspace_premium</span></div>
                </div>
                <h3 class="champ-name">NeonViper</h3>
                <div class="champ-score">24,150 PTS</div>
                <div class="champ-stats-row">
                    <div class="champ-stat-box">
                        <div class="champ-stat-label">Win Rate</div>
                        <div class="champ-stat-val">94%</div>
                    </div>
                    <div class="champ-stat-box">
                        <div class="champ-stat-label">Streak</div>
                        <div class="champ-stat-val highlight">12 🔥</div>
                    </div>
                </div>
            </div>

            <!-- Rank 3 -->
            <div class="champ-card champ-card--3rd">
                <div class="champ-rank-badge">#3</div>
                <div class="champ-avatar-wrapper">
                    <img alt="Rank 3" class="champ-avatar" src="https://lh3.googleusercontent.com/aida-public/AB6AXuClW1-snO82vbcjA1tuc0QE60bPPxpsBxB5f_e6gWQ9Ynjyj43O5fvncZwtoRphQMAIC2RaZ5Yih8h4og9W79Kva2fiUyz9ytgZDNCh0edFRxWZvQIsD9xf6y0Uda7rK5aQwCLcklDLlLbmC-MhO9JhiFOA469oxWb6-mBpfchiwAQmk4OJNMDMIRXzkzGB_Uy3Z-Rw72mjTIdQ9t5FkTz5TZXO2VFFpqqTxsuALIStnzXtejckqWGvDh6cJUpCyPIb39ZMC8nGnxk"/>
                    <div class="champ-icon"><span class="material-symbols-outlined">star</span></div>
                </div>
                <h3 class="champ-name">NexusRebel</h3>
                <div class="champ-score">15,900 PTS</div>
                <div class="champ-bar-bg"><div class="champ-bar-fill"></div></div>
            </div>
        </div>
    </section>

    <!-- Features Bento Grid -->
    <section class="features-section">
        <div class="features-grid">
            <div class="feature-card feat-interactive">
                <div class="feat-interactive-bg">
                    <img alt="Game Background" src="https://lh3.googleusercontent.com/aida-public/AB6AXuCR4RCTTHYJwdVuY42WL_bZu9wyXy4fof3drnZLRtvb3FYU3gqpRJP6_hGcIl8lBQpEcNgN1kIRWxfLBZ_mI6RawZGV-b1PAvS-_iFdjLHed5godsvWjJK8H9JYRvzhvOCedeBNFbql8LH-mPhqpVIwjhpbxjem0CGa-csulsysRL3uw932uJXWzUuiXjnp3EDRbJC07wAS3jgM1lGqzWm0mEwNsD6N031tbA5ooXBC0z55VHOObH_rG4hlzbiN6_sRGSt9J_01uZk"/>
                    <div class="feat-interactive-gradient"></div>
                </div>
                <div class="feat-interactive-content">
                    <span class="feat-badge">Interactive Arenas</span>
                    <h3 class="landing-headline feat-title">Custom Quiz Engines</h3>
                    <p class="feat-desc">Create high-stakes challenges with our advanced quiz builder. Supports video, live polls, and split-second buzzer rounds.</p>
                </div>
            </div>

            <div class="feature-card feat-social">
                <div class="feat-icon-circle">
                    <span class="material-symbols-outlined" style="font-variation-settings: 'FILL' 1;">groups</span>
                </div>
                <h4 class="landing-headline feat-title" style="font-size:1.5rem; margin-bottom:0.5rem">Social Squads</h4>
                <p class="feat-desc" style="font-size:1rem">Team up with friends, form guilds, and compete in massive 50v50 trivia wars.</p>
            </div>

            <div class="feature-card feat-rewards">
                <div class="feat-header-row">
                    <span class="material-symbols-outlined">badge</span>
                    <h4 class="landing-headline feat-title" style="font-size:1.25rem; margin-bottom:0">Weekly Rewards</h4>
                </div>
                <p class="feat-desc" style="font-size:1rem; margin-bottom:1.5rem">Top performers every week earn exclusive skins, profile banners, and real cash prizes.</p>
                <div class="feat-avatars">
                    <img alt="Avatar" class="feat-avatar-img" src="https://lh3.googleusercontent.com/aida-public/AB6AXuAYW8IvuK8_QE5ziWKdyCyRkasbCovCQhJCFQtOmxj1_-RaxSuYaxNfShsLr45nTFQqrkyeQWCltxVEfmERJk0-XLFX4njrCk9KVtH30LAJGK6nCfoHi9Dj5RA7c7Hgymf1RZAPhpMW7KvHrm7ZZGK5Kk8RMWbEpO3d8AVBFLjEJJmVv4D-QVk8KW6wUPL2Ukp0lUNKaC5ZHpGJnaUTD3aAn_1HYbTQEpp9W5SkpDLNXkb9u_0UfO8sGYsqVLt_9bAIlUNe9A5jovQ"/>
                    <img alt="Avatar" class="feat-avatar-img" src="https://lh3.googleusercontent.com/aida-public/AB6AXuCnXgprY2tzPNiVyXXZRBSKYGjxCjTkpc6giYsnf4lLnBFJY1M6S1PVhAyCVK-5jpeQLNFbP9d--Q3CPaOWHxJu1YCMaX89Zlcs44EKfGhAIAG7vDfBZwVq2pC3Ki66UAXGROGoE6HxvwbhOrldu_KS5caLQ36JIroYe2ptwYDPesf7D17GIkG8a1No_SdsxBer2SiLbylOIabY35GnCFeqUR_jZ5zpqMKsCXElLTq5iciqYwdAacppPFuMnu14mUkA0H-biBlVEJU"/>
                    <img alt="Avatar" class="feat-avatar-img" src="https://lh3.googleusercontent.com/aida-public/AB6AXuAcCP-szTJSEck41sf7XTZLV-ge7MkzdVYVQxY-aOy4GA_mqEf5rMZZjHWQTVRPDGOB8nhlSQ8s9X35iMLd0cstvWiNDjKxz9QJiwthtfgykyC5xp6SeNv_fSmJMdgbVn1ggdJuhoifLep1QOjImgk9hTLhiltMRRDUJDmgO5aX9vWKo2g6LI1QLXzAr5NoZH7ccIQ3rWT18HWUGE05Wvq5DA2WQ0euQ4vi67ilgH9ee44m9PTfC6iYa6HCTBhAXUf6_VYx7W_DC_k"/>
                    <div class="feat-avatar-plus">+2k</div>
                </div>
            </div>

            <div class="feature-card feat-crossplay">
                <div class="feat-crossplay-content">
                    <h4 class="landing-headline feat-title" style="font-size:2rem">Cross-Platform Dominance</h4>
                    <p class="feat-desc">Play on mobile, desktop, or tablet. Your progress and XP sync instantly across the arena universe.</p>
                </div>
                <div class="feat-phone-mockup">
                    <div class="mockup-screen">
                        <div class="mockup-line"></div>
                        <div class="mockup-body">
                            <div class="mockup-box1"></div>
                            <div class="mockup-box2"></div>
                            <div class="mockup-box3"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="landing-cta">
        <div class="cta-box">
            <div class="cta-blur-1"></div>
            <div class="cta-blur-2"></div>
            <h2 class="landing-headline cta-title">The Arena is Waiting.<br/>Are You Ready?</h2>
            <div class="cta-btn-wrapper">
                <a href="/register.php" class="btn-cta-main">Enter Arena Now</a>
                <div class="cta-note">
                    <span class="material-symbols-outlined">check_circle</span>
                    No Credit Card Required
                </div>
            </div>
        </div>
    </section>
</main>

<!-- Footer (using existing main.css logic if applicable, but we'll use a clean version) -->
<footer style="margin-top:0">
    <div class="footer-brand">
        <div class="auth-logo" style="font-size:1.1rem">QuizArena</div>
        <p>© 2026 QuizArena.<br>The Neon Arena Awaits.</p>
        <div class="footer-social" style="margin-top:1rem">
            <a href="#" class="social-btn"><span class="material-symbols-outlined" style="font-size:1.2rem">public</span></a>
            <a href="#" class="social-btn"><span class="material-symbols-outlined" style="font-size:1.2rem">smart_display</span></a>
            <a href="#" class="social-btn"><span class="material-symbols-outlined" style="font-size:1.2rem">chat_bubble</span></a>
        </div>
    </div>
    <div class="footer-col">
        <h4>Explore</h4>
        <a href="#">Sitemap</a>
        <a href="/leaderboard/index.php">Leaderboard</a>
        <a href="#">Rewards</a>
    </div>
    <div class="footer-col">
        <h4>Legal</h4>
        <a href="#">Privacy Policy</a>
        <a href="#">Terms of Service</a>
        <a href="#">Support</a>
    </div>
    <div class="footer-col">
        <h4>Newsletter</h4>
        <div style="position:relative; margin-top:0.5rem">
            <input type="email" placeholder="Enter your email" style="width:100%; background:var(--bg-card2); border:1px solid var(--border); border-radius:8px; padding:0.75rem 1rem; color:var(--text); outline:none; font-family:inherit;">
            <button style="position:absolute; right:6px; top:6px; background:var(--primary); color:#fff; border:none; border-radius:4px; padding:0.4rem 0.5rem; cursor:pointer;"><span class="material-symbols-outlined" style="font-size:1rem">send</span></button>
        </div>
    </div>
</footer>

</body>
</html>