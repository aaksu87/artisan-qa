<?php

namespace App\Services;

use App\Exceptions\DuplicateQuestionException;
use App\Exceptions\InvalidInputException;
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

        $this->questionRepository->create(['question' => $question, 'answer' => $answer]);
    }

    /**
     * @return array
     */
    public function getQuestionTableData()
    {
        return $this->questionRepository->all(['id', 'question', 'status'])->toArray();
    }

    /**
     * @param int $id
     * @return array|false
     */
    public function getQuestionDetail(int $id)
    {
        $detail = $this->questionRepository->find($id);
        return $detail ? $detail->toArray() : false;
    }

    /**
     * @param array $questionData
     * @param string $userAnswer
     * @return int
     */
    public function setStatus(array $questionData, string $userAnswer)
    {
        $status = ($questionData['answer'] == $userAnswer) ? Question::STATUS_TRUE : Question::STATUS_FALSE;
        return $this->questionRepository->update(['status' => $status], $questionData['id']);
    }

    public function resetProgress()
    {
        $this->questionRepository->resetProgress();
    }

}