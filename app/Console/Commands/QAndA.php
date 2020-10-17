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
     * @var string
     */
    protected $previousJob = '';

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
        $choice = $this->getInput('choice', __('qanda.main_question'), 'mainScreen', ["add", "list", "exit"], "add");
        if ($choice == 'add') {
            $this->addQuestion();
        } elseif ($choice == 'list') {
            while (!$this->questionService->isFinished()) { //list and practice questions until all answered
                $this->listQuestions();
            }
            $this->showOverview();
        }
    }


    private function addQuestion()
    {
        try {
            $this->previousJob = 'mainScreen';
            $questionText = $this->getInput('ask', __('qanda.new_question'), 'addQuestion');
            $answerText = $this->getInput('ask', __('qanda.new_answer'), 'mainScreen');
            $this->questionService->addQuestion($questionText, $answerText);
        } catch (InvalidInputException | DuplicateQuestionException $e) {
            $this->error($e->getMessage());
            sleep(1);  //show user the error for 1 second, then ask again
            $this->addQuestion();
        } catch (\Exception $e) {
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
        if (!$this->questionService->isFinished()) {
            $this->selectQuestion(); //ask question id for practice, if there are any unanswered ones.
        }
    }

    private function selectQuestion()
    {
        $questionId = (int)$this->getInput('ask', __('qanda.select_q_id'), 'mainScreen', [], '', false);

        $questionData = $this->questionService->getQuestionDetail($questionId);
        if ($questionData && $questionData['status'] == 'Unanswered') {
            $this->previousJob = 'listQuestions';
            $this->answerQuestion($questionData);
        }
    }

    private function answerQuestion($questionData)
    {
        $answer = $this->getInput('ask', $questionData['question'], 'mainScreen');
        if ($answer) {
            $this->questionService->setStatus($questionData, $answer);
        }
    }

    private function showOverview()
    {
        $this->listQuestions();
        $bar = $this->output->createProgressBar($this->questionService->questionCount());
        $bar->advance($this->questionService->trueQuestionCount());

        //ask for reset the progress
        if ($this->getInput('confirm', __('qanda.reset_confirm'), '', [], '', false)) {
            $this->call('qanda:reset');
        }
    }


    /**
     * @param $type
     * @param $text
     * @param string $previousJob
     * @param array $options
     * @param string $default
     * @param bool $clearScreen
     * @return mixed|string
     */
    private function getInput($type, $text, $previousJob = '', $options = [], $default = '', $clearScreen = true)
    {
        //refresh screen
        if ($clearScreen) {
            system('clear');
        }

        //choice - ask - confirm
        if ($type == 'choice') {
            $input = $this->choice($text, $options, $default);
        } else {
            $input = $this->{$type}($text);
        }

        //quit to console
        if ($input === 'exit') {
            exit();
        }

        //go back to previous job
        if ($input === 'back' && $this->previousJob != '') {
            $this->{$this->previousJob}();
            $this->previousJob = $previousJob;
            return;
        }

        if ($previousJob != '') {
            $this->previousJob = $previousJob;
        }


        return $input;
    }

}