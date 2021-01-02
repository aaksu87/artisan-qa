<?php

namespace App\Services;

use App\Exceptions\DuplicateQuestionException;
use App\Exceptions\InvalidInputException;
use App\Exceptions\NoDataException;
use App\Models\Progress;
use App\Models\Question;
use App\Repositories\QuestionRepository;

class QuestionService
{
    /**
     * @var QuestionRepository
     */
    protected $questionRepository;

    /**
     * QuestionService constructor.
     * @param QuestionRepository $questionRepository
     */
    public function __construct(QuestionRepository $questionRepository)
    {
        $this->questionRepository = $questionRepository;
    }

    /**
     * @return bool
     */
    public function isNewStart()
    {
        return $this->questionCount() == 0;
    }

    /**
     * @return bool
     */
    public function isFinished()
    {
        if (
            $this->isNewStart() ||
            $this->unansweredQuestionCount() > 0
        ) {
            return false;
        }
        return true;
    }

    /**
     * @return int
     */
    public function questionCount()
    {
        return $this->questionRepository->all()->count();
    }

    /**
     * @return int
     */
    public function unansweredQuestionCount()
    {
        return $this->questionRepository->unansweredQuestions()->count();
    }

    /**
     * @return int
     */
    public function trueQuestionCount()
    {
        return $this->questionRepository->trueQuestions()->count();
    }

    /**
     * @param string $question
     * @return bool
     */
    public function isExistQuestion(string $question)
    {
        return $this->questionRepository->getQuestionByText($question) ? true : false;
    }

    /**
     * @param $question
     * @param $answer
     * @throws DuplicateQuestionException
     * @throws InvalidInputException
     */
    public function addQuestion($question, $answer)
    {
        if ($question == '' || $answer == '') {
            throw new InvalidInputException(__('qanda.error.invalid_inputs'));
        }
        if ($this->isExistQuestion($question)) {
            throw new DuplicateQuestionException(__('qanda.error.duplicate_question'));
        }

        $question = $this->questionRepository->create(['question' => $question, 'answer' => $answer]);
        $question->progress()->create(['answer'=>Progress::STATUS_UNANSWERED]);
    }

    /**
     * @return array
     */
    public function getQuestionTableData()
    {
        $data = $this->questionRepository->allDataWithProgress();

        if ($data->isEmpty()) {
            throw new NoDataException(__('qanda.error.no_question_data'));
        }
        return $data->toArray();
    }

    /**
     * @param int $id
     * @return array|false
     */
    public function getQuestionDetail(int $id)
    {
        $detail = $this->questionRepository->find($id);
        return $detail ?? false;
    }

}