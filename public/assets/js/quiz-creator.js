let questionCount = 0;

function addQuestion() {
    questionCount++;
    const index = questionCount - 1;
    const letters = ['A', 'B', 'C', 'D'];

    const block = document.createElement('div');
    block.className = 'question-block';
    block.dataset.index = index;

    block.innerHTML = `
        <div class="question-block-header">
            <span class="question-number">Question ${questionCount}</span>
            <button type="button" class="question-remove" onclick="removeQuestion(this)">✕ Remove</button>
        </div>

        <textarea
            class="question-content"
            name="questions[${index}][content]"
            placeholder="Enter your question here..."
            required
        ></textarea>

        <div class="answers-grid">
            ${letters.map((letter, i) => `
                <div class="answer-row">
                    <div class="answer-letter" data-index="${i}" onclick="setCorrect(this, ${index})">
                        ${letter}
                    </div>
                    <input
                        type="text"
                        class="answer-input"
                        name="questions[${index}][answers][${i}]"
                        placeholder="Answer ${letter}..."
                        required
                    >
                </div>
            `).join('')}
        </div>

        <input type="hidden" name="questions[${index}][correct_answer]" value="0" class="correct-input">

        <div class="question-footer">
            <div class="time-limit-group">
                ⏱ Time limit:
                <select name="questions[${index}][time_limit]">
                    <option value="5">5s</option>
                    <option value="10">10s</option>
                    <option value="15">15s</option>
                    <option value="30" selected>30s</option>
                    <option value="45">45s</option>
                    <option value="60">60s</option>
                </select>
            </div>
            <span class="correct-hint">Click A/B/C/D to mark correct answer</span>
        </div>
    `;

    document.getElementById('questions-list').appendChild(block);

    // Domyślnie zaznacz A jako poprawną
    const firstLetter = block.querySelector('.answer-letter');
    firstLetter.classList.add('correct');
}

function setCorrect(el, questionIndex) {
    const block = el.closest('.question-block');
    
    // Odznacz wszystkie
    block.querySelectorAll('.answer-letter').forEach(l => l.classList.remove('correct'));
    
    // Zaznacz kliknięty
    el.classList.add('correct');
    
    // Zapisz wartość w hidden input
    block.querySelector('.correct-input').value = el.dataset.index;
}

function removeQuestion(btn) {
    const block = btn.closest('.question-block');
    block.remove();
    renumberQuestions();
}

function renumberQuestions() {
    document.querySelectorAll('.question-block').forEach((block, i) => {
        block.querySelector('.question-number').textContent = `Question ${i + 1}`;
    });
}

// Dodaj pierwsze pytanie automatycznie
document.getElementById('add-question').addEventListener('click', addQuestion);
addQuestion();