<?php

namespace Tests\Unit;

use App\Exceptions\DuplicateQuestionException;
use App\Exceptions\InvalidInputException;
use App\Repositories\QuestionsRepository;
use App\Services\QuestionService;
use Faker\Provider\Lorem;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class QandATest extends TestCase
{
    use RefreshDatabase;

    protected $service;

    protected function setUp(): void
    {
        parent::setup();
        $this->service = new QuestionService(app(QuestionsRepository::class));
    }

    public function testIsNewStart()
    {
        $result = $this->service->isNewStart();
        $this->assertTrue($result);
    }

    public function testIsFinishedFalse()
    {
        $result = $this->service->isFinished();
        $this->assertFalse($result);
    }

    public function testIsExistQuestionFalse()
    {
        $this->service->addQuestion(Lorem::sentence(3), Lorem::sentence(3));
        $result = $this->service->isExistQuestion(Lorem::sentence(3));
        $this->assertFalse($result);
    }

    public function testIsExistQuestionTrue()
    {
        $question = Lorem::sentence(3);
        $this->service->addQuestion($question, Lorem::sentence(3));

        $result = $this->service->isExistQuestion($question);
        $this->assertTrue($result);
    }

    public function testAddEmptyInputs()
    {
        $this->expectException(InvalidInputException::class);
        $this->service->addQuestion('', Lorem::sentence(3));

        $this->expectException(InvalidInputException::class);
        $this->service->addQuestion(Lorem::sentence(3), '');
    }

    public function testSuccessAddQuestion()
    {
        $result = $this->service->addQuestion(Lorem::sentence(3), Lorem::sentence(3));
        $this->assertTrue($result);
    }

    public function testDuplicateQuestion()
    {
        $question = Lorem::sentence(3);
        $this->service->addQuestion($question, Lorem::sentence(3));

        $this->expectException(DuplicateQuestionException::class);
        $this->service->addQuestion($question, Lorem::sentence(3));
    }

    public function testWrongAnswer()
    {
        $this->service->addQuestion(Lorem::sentence(3), Lorem::sentence(3));
        $questionData = $this->service->getAnUnansweredQuestion();

        $this->service->setStatus($questionData->toArray(), Lorem::sentence(3));

        $questionData = $this->service->getQuestionDetail($questionData->id);
        $this->assertEquals('False', $questionData['status']);
    }

    public function testTrueAnswer()
    {
        $answer = Lorem::sentence(3);
        $this->service->addQuestion(Lorem::sentence(3), $answer);
        $questionData = $this->service->getAnUnansweredQuestion();

        $this->service->setStatus($questionData->toArray(), $questionData->answer);

        $questionData = $this->service->getQuestionDetail($questionData->id);
        $this->assertEquals('True', $questionData['status']);
    }

    public function testIsFinishedTrue()
    {
        $this->service->addQuestion(Lorem::sentence(3), Lorem::sentence(3));
        $questionData = $this->service->getAnUnansweredQuestion();

        $this->service->setStatus($questionData->toArray(), Lorem::sentence(3));

        $result = $this->service->isFinished();
        $this->assertTrue($result);
    }

}
