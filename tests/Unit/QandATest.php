<?php

namespace Tests\Unit;

use App\Exceptions\DuplicateQuestionException;
use App\Exceptions\InvalidInputException;
use App\Models\Question;
use App\Repositories\QuestionRepository;
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
        $this->service = new QuestionService(app(QuestionRepository::class));
    }

    public function testIsNewStartTrue()
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

    public function testAddEmptyInputsExpectExceptions()
    {
        $this->expectException(InvalidInputException::class);
        $this->service->addQuestion('', Lorem::sentence(3));

        $this->expectException(InvalidInputException::class);
        $this->service->addQuestion(Lorem::sentence(3), '');
    }

    public function testAddQuestionSuccessData()
    {
        $question = Lorem::sentence(3);
        $answer = Lorem::sentence(3);
        $this->service->addQuestion($question, $answer);

        $questionData = app(QuestionRepository::class)->getQuestionByText($question);

        $this->assertEquals($question, $questionData['question']);
        $this->assertEquals($answer, $questionData['answer']);
        $this->assertEquals('Unanswered', $questionData['status']);
    }

    public function testDuplicateQuestionExpectException()
    {
        $question = Lorem::sentence(3);
        $this->service->addQuestion($question, Lorem::sentence(3));

        $this->expectException(DuplicateQuestionException::class);
        $this->service->addQuestion($question, Lorem::sentence(3));
    }

    public function testWrongAnswerStatusSetFalse()
    {
        $this->service->addQuestion(Lorem::sentence(3), Lorem::sentence(3));
        $questionData = $this->getAnUnansweredQuestion();

        $this->service->setStatus($questionData->toArray(), Lorem::sentence(3));

        $questionData = $this->service->getQuestionDetail($questionData->id);
        $this->assertEquals('False', $questionData['status']);
    }

    public function testTrueAnswerStatusSetTrue()
    {
        $answer = Lorem::sentence(3);
        $this->service->addQuestion(Lorem::sentence(3), $answer);
        $questionData = $this->getAnUnansweredQuestion();

        $this->service->setStatus($questionData->toArray(), $questionData->answer);

        $questionData = $this->service->getQuestionDetail($questionData->id);
        $this->assertEquals('True', $questionData['status']);
    }

    public function testIsFinishedTrue()
    {
        $this->service->addQuestion(Lorem::sentence(3), Lorem::sentence(3));

        $questionData = $this->getAnUnansweredQuestion();
        $this->service->setStatus($questionData->toArray(), Lorem::sentence(3));

        $result = $this->service->isFinished();
        $this->assertTrue($result);
    }


    //for test process
    private function getAnUnansweredQuestion()
    {
        return app(QuestionRepository::class)->findBy('status', Question::STATUS_UNANSWERED);
    }

}
