<?php

namespace QuizArena\Tests\Unit;

use QuizArena\Controllers\QuizController;
use QuizArena\Tests\Support\PdoTestCase;

class QuizControllerValidationTest extends PdoTestCase
{
    private QuizController $controller;

    protected function setUp(): void
    {
        $this->controller = new QuizController($this->createPdoMock());
    }

    public function testCreateRejectsShortTitle(): void
    {
        $result = $this->controller->create('user-1', $this->validPayload([
            'title' => 'ab',
        ]));

        $this->assertFalse($result['success']);
        $this->assertSame('Title must be at least 3 characters', $result['errors']['title']);
    }

    public function testCreateRequiresCategory(): void
    {
        $result = $this->controller->create('user-1', $this->validPayload([
            'category' => '',
        ]));

        $this->assertFalse($result['success']);
        $this->assertSame('Category is required', $result['errors']['category']);
    }

    public function testCreateRejectsInvalidDifficulty(): void
    {
        $result = $this->controller->create('user-1', $this->validPayload([
            'difficulty' => 9,
        ]));

        $this->assertFalse($result['success']);
        $this->assertSame('Difficulty must be 1, 2 or 3', $result['errors']['difficulty']);
    }

    public function testCreateRequiresAtLeastOneQuestion(): void
    {
        $result = $this->controller->create('user-1', $this->validPayload([
            'questions' => [],
        ]));

        $this->assertFalse($result['success']);
        $this->assertSame('Add at least one question', $result['errors']['questions']);
    }

    public function testCreateRejectsEmptyQuestionContent(): void
    {
        $payload = $this->validPayload();
        $payload['questions'][0]['content'] = '';

        $result = $this->controller->create('user-1', $payload);

        $this->assertFalse($result['success']);
        $this->assertSame('Question 1 cannot be empty', $result['errors']['question_0']);
    }

    public function testCreateRequiresExactlyFourAnswers(): void
    {
        $payload = $this->validPayload();
        $payload['questions'][0]['answers'] = ['A', 'B', 'C'];

        $result = $this->controller->create('user-1', $payload);

        $this->assertFalse($result['success']);
        $this->assertSame('Question 1 must have exactly 4 answers', $result['errors']['answers_0']);
    }

    public function testCreateRequiresValidCorrectAnswerIndex(): void
    {
        $payload = $this->validPayload();
        $payload['questions'][0]['correct_answer'] = 5;

        $result = $this->controller->create('user-1', $payload);

        $this->assertFalse($result['success']);
        $this->assertSame('Question 1 must have a correct answer selected', $result['errors']['correct_0']);
    }

    /**
     * @param array<string, mixed> $overrides
     * @return array<string, mixed>
     */
    private function validPayload(array $overrides = []): array
    {
        return array_merge([
            'title'      => 'Valid Quiz Title',
            'category'   => 'Science',
            'difficulty' => 2,
            'questions'  => [[
                'content'        => 'What is 2 + 2?',
                'correct_answer' => 1,
                'time_limit'     => 30,
                'answers'        => ['3', '4', '5', '6'],
            ]],
        ], $overrides);
    }
}
