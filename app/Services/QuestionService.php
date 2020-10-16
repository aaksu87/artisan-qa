<?php

namespace App\Services;

use App\Exceptions\DuplicateQuestionException;
use App\Exceptions\InvalidInputException;
use App\Models\Questions;
use App\Repositories\QuestionsRepository;

class QuestionService
{
    /**
     * @var QuestionsRepository
     */
    protected $questionRepository;

    public function __construct(QuestionsRepository $questionRepository)
    {
        $this->questionRepository = $questionRepository;
    }

    public function isNewStart()
    {
        return $this->questionCount() == 0;
    }

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

    public function questionCount()
    {
        return $this->questionRepository->all()->count();
    }

    public function unansweredQuestionCount()
    {
        return $this->questionRepository->unansweredQuestions()->count();
    }

    public function trueQuestionCount()
    {
        return $this->questionRepository->trueQuestions()->count();
    }

    public function isExistQuestion(string $question)
    {
        return $this->questionRepository->getQuestionByText($question) ? true : false;
    }

    public function addQuestion($question, $answer)
    {
        if ($question == '' || $answer == '') {
            throw new InvalidInputException(__('qanda.error.invalid_inputs'));
        }
        if ($this->isExistQuestion($question)) {
            throw new DuplicateQuestionException(__('qanda.error.duplicate_question'));
        }

        try {
            $this->questionRepository->create(['question' => $question, 'answer' => $answer]);
            return true;
        } catch (\Exception $e) {
            throw new \Exception();
        }
    }

    public function getQuestionTableData()
    {
        return $this->questionRepository->all(['id', 'question', 'status'])->toArray();
    }

    public function getQuestionDetail(int $id)
    {
        return $this->questionRepository->find($id) ? $this->questionRepository->find($id)->toArray() : false;
    }

    public function setStatus(array $questionData, string $userAnswer)
    {
        $status = ($questionData['answer'] == $userAnswer) ? Questions::STATUS_TRUE : Questions::STATUS_FALSE;
        return $this->questionRepository->update(['status' => $status], $questionData['id']);
    }

    public function resetProgress()
    {
        $this->questionRepository->truncateTable();
    }

    //for unit test
    public function getAnUnansweredQuestion(){
        return $this->questionRepository->findBy('status',Questions::STATUS_UNANSWERED);
    }

}