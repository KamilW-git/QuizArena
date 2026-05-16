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

// Dodaj pierwsze pytanie automatycznie lub odtwórz z błędnego formularza
document.getElementById('add-question').addEventListener('click', addQuestion);

if (typeof preloadedQuestions !== 'undefined' && preloadedQuestions && Object.keys(preloadedQuestions).length > 0) {
    const questionsArray = Array.isArray(preloadedQuestions) ? preloadedQuestions : Object.values(preloadedQuestions);
    
    questionsArray.forEach((q, index) => {
        addQuestion();
        const blocks = document.querySelectorAll('.question-block');
        const block = blocks[blocks.length - 1];
        
        block.querySelector('.question-content').value = q.content || '';
        
        const inputs = block.querySelectorAll('.answer-input');
        const answers = q.answers || ['', '', '', ''];
        answers.forEach((ans, i) => {
            if (inputs[i]) inputs[i].value = ans;
        });
        
        const timeLimit = block.querySelector(`select[name="questions[${index}][time_limit]"]`);
        if (timeLimit && q.time_limit) timeLimit.value = q.time_limit;
        
        const correctIndex = parseInt(q.correct_answer) || 0;
        const letters = block.querySelectorAll('.answer-letter');
        if (letters[correctIndex]) {
            setCorrect(letters[correctIndex], index);
        }
    });
} else {
    addQuestion();
}

// --- Open Trivia DB Integration ---

const catMap = {
    'geography': 22,
    'science': 17,
    'history': 23,
    'math': 19,
    'technology': 18,
    'sports': 21,
    'music': 12,
    'movies': 11
};

function decodeHTML(html) {
    const txt = document.createElement('textarea');
    txt.innerHTML = html;
    return txt.value;
}

// Shuffle array
function shuffle(array) {
    for (let i = array.length - 1; i > 0; i--) {
        const j = Math.floor(Math.random() * (i + 1));
        [array[i], array[j]] = [array[j], array[i]];
    }
    return array;
}

document.getElementById('auto-generate').addEventListener('click', async function() {
    const btn = this;
    const originalText = btn.textContent;
    
    const catInput = document.querySelector('input[name="category"]').value.toLowerCase();
    const diffSelect = document.querySelector('select[name="difficulty"]').value;
    
    // Map inputs
    const openTdbCat = catMap[catInput] || 9; // 9 = General Knowledge
    const diffMap = { '1': 'easy', '2': 'medium', '3': 'hard' };
    const openTdbDiff = diffMap[diffSelect] || 'easy';
    
    try {
        btn.textContent = '🤖 Loading...';
        btn.disabled = true;
        
        const amountSelect = document.getElementById('auto-generate-amount');
        const amount = amountSelect ? amountSelect.value : 10;
        
        const url = `https://opentdb.com/api.php?amount=${amount}&category=${openTdbCat}&difficulty=${openTdbDiff}&type=multiple`;
        const res = await fetch(url);
        const data = await res.json();
        
        if (data.response_code !== 0 || !data.results || data.results.length === 0) {
            alert('Could not fetch questions from OpenTDB for this category/difficulty. Try changing them!');
            return;
        }
        
        // Remove empty default question if it's the only one and empty
        const blocks = document.querySelectorAll('.question-block');
        if (blocks.length === 1) {
            const firstContent = blocks[0].querySelector('.question-content').value.trim();
            if (firstContent === '') {
                blocks[0].remove();
                questionCount = 0; // reset
            }
        }
        
        data.results.forEach(q => {
            // Create a new empty block
            addQuestion();
            
            // The newly added block is the last one
            const newBlock = document.querySelectorAll('.question-block');
            const block = newBlock[newBlock.length - 1];
            const index = block.dataset.index;
            
            // Set question content
            block.querySelector('.question-content').value = decodeHTML(q.question);
            
            // Prepare answers
            let answersArr = [...q.incorrect_answers.map(decodeHTML), decodeHTML(q.correct_answer)];
            answersArr = shuffle(answersArr);
            
            // Find which index is the correct answer
            const correctIndex = answersArr.indexOf(decodeHTML(q.correct_answer));
            
            // Set inputs
            const inputs = block.querySelectorAll('.answer-input');
            answersArr.forEach((ans, i) => {
                inputs[i].value = ans;
            });
            
            // Set correct answer radio
            const letters = block.querySelectorAll('.answer-letter');
            setCorrect(letters[correctIndex], index);
        });
        
        // Update numbering
        renumberQuestions();
        
    } catch (err) {
        alert('Error communicating with OpenTDB API.');
        console.error(err);
    } finally {
        btn.textContent = originalText;
        btn.disabled = false;
    }
});