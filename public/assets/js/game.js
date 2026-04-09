/**
 * game.js — QuizArena game engine (v2)
 * Uses chosen_index (0-3) not answer UUIDs — matches DB schema.
 */
'use strict';

const TIMER_CIRCUMFERENCE = 2 * Math.PI * 18;
const LETTERS = ['A', 'B', 'C', 'D'];

const states = {
    loading:  document.getElementById('state-loading'),
    playing:  document.getElementById('state-playing'),
    finished: document.getElementById('state-finished'),
};
const ui = {
    qCurrent:     document.getElementById('q-current'),
    qTotal:       document.getElementById('q-total'),
    progressFill: document.getElementById('progress-fill'),
    timerArc:     document.getElementById('timer-arc'),
    timerCount:   document.getElementById('timer-count'),
    qCategory:    document.getElementById('q-category'),
    qText:        document.getElementById('q-text'),
    answersGrid:  document.getElementById('answers-grid'),
    liveScore:    document.getElementById('live-score'),
};

let sessionId     = null;
let questions     = [];
let currentIdx    = 0;
let score         = 0;
let timerHandle   = null;
let secondsLeft   = 0;
let questionStart = null;
let answerLocked  = false;

function showState(name) {
    Object.entries(states).forEach(([key, el]) => {
        el.classList.toggle('is-hidden', key !== name);
    });
}

async function post(url, body) {
    const res = await fetch(url, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(body),
    });
    if (!res.ok) {
        const err = await res.json().catch(() => ({}));
        throw new Error(err.error ?? `HTTP ${res.status}`);
    }
    return res.json();
}

document.addEventListener('DOMContentLoaded', startGame);

async function startGame() {
    showState('loading');
    try {
        const data = await post('/api/game/start.php', { quiz_id: window.QUIZ_ID });
        sessionId = data.session_id;
        questions = data.questions;
    } catch (err) {
        console.error('Failed to start game:', err);
        showError('Could not load the quiz. Please try again.');
        return;
    }
    if (!questions.length) { showError('This quiz has no questions yet.'); return; }
    ui.qTotal.textContent = questions.length;
    showState('playing');
    renderQuestion(0);
}

function renderQuestion(idx) {
    currentIdx   = idx;
    answerLocked = false;
    const q      = questions[idx];

    ui.qCurrent.textContent     = idx + 1;
    ui.progressFill.style.width = ((idx / questions.length) * 100) + '%';
    ui.qCategory.textContent    = q.category ?? 'General';
    ui.qText.textContent        = q.text;

    ui.answersGrid.innerHTML = '';
    q.answers.forEach((ans) => {
        const btn = document.createElement('button');
        btn.className           = 'answer-btn';
        btn.dataset.answerIndex = ans.index;
        btn.setAttribute('role', 'listitem');
        btn.innerHTML = `<span class="answer-btn__letter">${LETTERS[ans.index]}</span>${escHtml(ans.text)}`;
        btn.addEventListener('click', () => handleAnswer(btn, q, ans.index));
        ui.answersGrid.appendChild(btn);
    });

    startTimer(q.time_limit ?? 20);
}

function startTimer(seconds) {
    clearInterval(timerHandle);
    secondsLeft   = seconds;
    questionStart = Date.now();
    ui.timerArc.classList.remove('is-urgent');
    updateTimerDisplay(secondsLeft, seconds);

    timerHandle = setInterval(() => {
        secondsLeft--;
        updateTimerDisplay(secondsLeft, seconds);
        if (secondsLeft <= 5) ui.timerArc.classList.add('is-urgent');
        if (secondsLeft <= 0) { clearInterval(timerHandle); handleTimeout(); }
    }, 1000);
}

function updateTimerDisplay(s, total) {
    ui.timerCount.textContent = s;
    ui.timerArc.style.strokeDashoffset = TIMER_CIRCUMFERENCE * (1 - s / total);
}

function stopTimer() { clearInterval(timerHandle); }

async function handleAnswer(btn, question, chosenIndex) {
    if (answerLocked) return;
    answerLocked = true;
    stopTimer();
    const timeSpentMs = Date.now() - questionStart;
    disableAllAnswers();

    let correctIndex = chosenIndex;
    try {
        const result = await post('/api/game/answer.php', {
            session_id:    sessionId,
            question_id:   question.id,
            chosen_index:  chosenIndex,
            time_spent_ms: timeSpentMs,
        });
        correctIndex = result.correct_index;
        if (result.correct) { score += result.points_awarded ?? 0; updateScore(score); }
    } catch (err) {
        console.error('Answer save failed:', err);
    }

    showAnswerResult(chosenIndex, correctIndex);
    setTimeout(() => advance(), 1200);
}

async function handleTimeout() {
    if (answerLocked) return;
    answerLocked = true;
    disableAllAnswers();
    const q = questions[currentIdx];
    try {
        const result = await post('/api/game/answer.php', {
            session_id: sessionId, question_id: q.id, chosen_index: -1, time_spent_ms: 0,
        });
        showAnswerResult(-1, result.correct_index);
    } catch (err) { console.error('Timeout save failed:', err); }
    setTimeout(() => advance(), 1200);
}

function disableAllAnswers() {
    document.querySelectorAll('.answer-btn').forEach(b => b.disabled = true);
}

function showAnswerResult(chosenIndex, correctIndex) {
    document.querySelectorAll('.answer-btn').forEach(btn => {
        const idx = Number(btn.dataset.answerIndex);
        if (idx === correctIndex) btn.classList.add('is-correct');
        else if (idx === chosenIndex) btn.classList.add('is-wrong');
    });
}

function advance() {
    const next = currentIdx + 1;
    if (next < questions.length) renderQuestion(next);
    else finishGame();
}

function updateScore(newScore) {
    ui.liveScore.textContent = newScore;
    ui.liveScore.classList.remove('bump');
    void ui.liveScore.offsetWidth;
    ui.liveScore.classList.add('bump');
}

async function finishGame() {
    ui.progressFill.style.width = '100%';
    showState('finished');
    try {
        const result = await post('/api/game/finish.php', { session_id: sessionId });
        window.location.href = result.redirect_url;
    } catch (err) {
        console.error('Finish failed:', err);
        setTimeout(() => { window.location.href = `/quiz/results.php?session=${sessionId}`; }, 2000);
    }
}

function showError(msg) {
    states.loading.innerHTML = `
        <div style="text-align:center;color:var(--tertiary)">
            <p style="font-size:1.1rem;font-weight:600">⚠️ ${escHtml(msg)}</p>
            <a href="/quiz/browse.php" style="color:var(--primary);margin-top:1rem;display:inline-block">← Back to Browse</a>
        </div>`;
}

function escHtml(str) {
    return String(str).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}