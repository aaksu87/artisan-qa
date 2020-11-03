<?php

namespace Tests\Unit;

use App\Exceptions\DuplicateQuestionException;
use App\Exceptions\InvalidInputException;
use App\Models\Progress;
use App\Models\Question;
use App\Repositories\ProgressRepository;
use App\Repositories\QuestionRepository;
use App\Services\ProgressService;
use App\Services\QuestionService;
use Faker\Provider\Lorem;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class QandATest extends TestCase
{
    use RefreshDatabase;

    protected $question_service;
    protected $progress_service;

    protected function setUp(): void
    {
        parent::setup();
        $this->question_service = new QuestionService(app(QuestionRepository::class));
        $this->progress_service = new ProgressService(app(ProgressRepository::class));
    }

    public function testIsNewStartTrue()
    {
        $result = $this->question_service->isNewStart();
        $this->assertTrue($result);
    }

    public function testIsFinishedFalse()
    {
        $result = $this->question_service->isFinished();
        $this->assertFalse($result);
    }

    public function testIsExistQuestionFalse()
    {
        $this->question_service->addQuestion(Lorem::sentence(3), Lorem::sentence(3));
        $result = $this->question_service->isExistQuestion(Lorem::sentence(3));
        $this->assertFalse($result);
    }

    public function testIsExistQuestionTrue()
    {
        $question = Lorem::sentence(3);
        $this->question_service->addQuestion($question, Lorem::sentence(3));

        $result = $this->question_service->isExistQuestion($question);
        $this->assertTrue($result);
    }

    public function testAddEmptyInputsExpectExceptions()
    {
        $this->expectException(InvalidInputException::class);
        $this->question_service->addQuestion('', Lorem::sentence(3));

        $this->expectException(InvalidInputException::class);
        $this->question_service->addQuestion(Lorem::sentence(3), '');
    }

    public function testAddQuestionSuccessData()
    {
        $question = Lorem::sentence(3);
        $answer = Lorem::sentence(3);
        $this->question_service->addQuestion($question, $answer);

        $questionData = app(QuestionRepository::class)->getQuestionByText($question);

        $this->assertEquals($question, $questionData['question']);
        $this->assertEquals($answer, $questionData['answer']);
        $this->assertEquals('Unanswered', $questionData->progress->status);
    }

    public function testDuplicateQuestionExpectException()
    {
        $question = Lorem::sentence(3);
        $this->question_service->addQuestion($question, Lorem::sentence(3));

        $this->expectException(DuplicateQuestionException::class);
        $this->question_service->addQuestion($question, Lorem::sentence(3));
    }

    public function testWrongAnswerStatusSetFalse()
    {
        $this->question_service->addQuestion(Lorem::sentence(3), Lorem::sentence(3));
        $questionData = $this->getAnUnansweredQuestion();

        $this->progress_service->setStatus($questionData, Lorem::sentence(3));

        $questionData = $this->question_service->getQuestionDetail($questionData->id);
        $this->assertEquals('False', $questionData->progress->status);
    }

    public function testTrueAnswerStatusSetTrue()
    {
        $answer = Lorem::sentence(3);
        $this->question_service->addQuestion(Lorem::sentence(3), $answer);
        $questionData = $this->getAnUnansweredQuestion();

        $this->progress_service->setStatus($questionData, $questionData->answer);

        $questionData = $this->question_service->getQuestionDetail($questionData->id);
        $this->assertEquals('True', $questionData->progress->status);
    }

    public function testIsFinishedTrue()
    {
        $this->question_service->addQuestion(Lorem::sentence(3), Lorem::sentence(3));

        $questionData = $this->getAnUnansweredQuestion();
        $this->progress_service->setStatus($questionData, Lorem::sentence(3));

        $result = $this->question_service->isFinished();
        $this->assertTrue($result);
    }


    //for test process
    private function getAnUnansweredQuestion()
    {
        return app(Question::class)->newQuery()->whereHas('progress', function ($query) {
            return $query->where('status', '=', Progress::STATUS_UNANSWERED);
        })->get()->first();
    }

}
