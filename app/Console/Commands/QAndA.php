<?php

namespace App\Console\Commands;

use App\Exceptions\DuplicateQuestionException;
use App\Exceptions\InvalidInputException;
use App\Services\QuestionService;
use Illuminate\Console\Command;

class QAndA extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'qanda:interactive';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Runs an interactive command line based Q And A system.';

    /**
     * @var QuestionService $questionService
     */
    protected $questionService;

    /**
     * QAndA constructor.
     * @param QuestionService $questionService
     */
    public function __construct(QuestionService $questionService)
    {
        parent::__construct();
        $this->questionService = $questionService;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        if ($this->questionService->isFinished()) { //when all questions are answered
            $this->showOverview();
        } elseif ($this->questionService->isNewStart()) { //when the interaction is starting new
            $this->addQuestion();
        } else {
            $this->mainScreen();
        }
    }

    private function mainScreen()
    {
        $choice = $this->getInput('choice', __('qanda.main_question'), ["add", "list", "exit"], "add");
        if ($choice == 'add') {
            $this->addQuestion();
        } elseif ($choice == 'list') {
            while (!$this->questionService->isFinished()) {
                $this->listQuestions();
                $this->selectQuestion();
            }
            $this->showOverview();
        }
    }


    private function addQuestion()
    {
        try{
            $questionText = $this->getInput('ask', __('qanda.new_question'));
            $answerText = $this->getInput('ask', __('qanda.new_answer'));
            $this->questionService->addQuestion($questionText, $answerText);
        }catch (InvalidInputException | DuplicateQuestionException $e){
            $this->error($e->getMessage());
            sleep(1);
            $this->addQuestion();
        }catch (\Exception $e){
            $this->error(__('qanda.error.general'));
            sleep(1);
        }

        $this->mainScreen();
    }

    private function listQuestions()
    {
        system('clear');
        $headers = ['ID', 'Question', 'Status'];
        $questions = $this->questionService->getQuestionTableData();
        $this->table($headers, $questions);
    }

    private function selectQuestion()
    {
        $questionId = (int)$this->getInput('ask', __('qanda.select_q_id'), [], '', false);

        $questionData = $this->questionService->getQuestionDetail($questionId);
        if ($questionData && $questionData['status'] == 'Unanswered') {
            $this->answerQuestion($questionData);
        }
    }

    private function answerQuestion($questionData)
    {
        $answer = $this->getInput('ask', $questionData['question'], 'answerQuestion');
        if ($answer) {
            $this->questionService->setStatus($questionData, $answer);
        }
    }

    private function showOverview()
    {
        $this->listQuestions();
        $bar = $this->output->createProgressBar($this->questionService->questionCount());
        $bar->advance($this->questionService->trueQuestionCount());
        if ($this->getInput('confirm', __('qanda.reset_confirm'),[],'',false)) {
            $this->call('qanda:reset');
        }
    }



    /**
     * @param $type
     * @param $text
     * @param array $options
     * @param string $default
     * @param bool $clearScreen
     * @return string
     */
    private function getInput($type, $text, $options = [], $default = '', $clearScreen = true)
    {
        if ($clearScreen) {
            system('clear');
        }

        if ($type == 'choice') {
            $input = $this->choice($text, $options, $default);
        }
        else {
            $input = $this->$type($text);
        }

        if ($input === 'exit') {
            exit();
        }

        return $input;
    }

}