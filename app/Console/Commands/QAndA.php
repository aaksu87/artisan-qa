<?php

namespace App\Console\Commands;

use App\Exceptions\DuplicateQuestionException;
use App\Exceptions\InvalidInputException;
use App\Exceptions\NoDataException;
use App\Services\ProgressService;
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
     * @var ProgressService $progressService
     */
    protected $progressService;

    /**
     * @var string
     */
    protected $previousJob = '';

    /**
     * QAndA constructor.
     * @param QuestionService $questionService
     * @param ProgressService $progressService
     */
    public function __construct(QuestionService $questionService, ProgressService $progressService)
    {
        parent::__construct();
        $this->questionService = $questionService;
        $this->progressService = $progressService;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        system('clear');
        $this->info(__('qanda.welcome'));
        $this->mainScreen(false);
    }

    private function mainScreen($clearScreen = true)
    {
        $choice = $this->getInput('choice', __('qanda.main_question'), 'mainScreen', ["add", "list", "exit"], "add", $clearScreen);
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
        try{
            $questions = $this->questionService->getQuestionTableData();

            $headers = ['ID', 'Question', 'Status'];
            $this->table($headers, $questions);
            if (!$this->questionService->isFinished()) {
                $this->selectQuestion(); //ask question id for practice, if there are any unanswered ones.
            }
        } catch (NoDataException $e) {
            $this->error($e->getMessage());
            sleep(1);  //show user the error for 1 second, then ask again
            $this->mainScreen();
        }
    }

    private function selectQuestion()
    {
        $questionId = (int)$this->getInput('ask', __('qanda.select_q_id'), 'mainScreen', [], '', false);

        $questionData = $this->questionService->getQuestionDetail($questionId);
        if ($questionData && $questionData->progress->status == 'Unanswered') {
            $this->previousJob = 'listQuestions';
            $this->answerQuestion($questionData);
        }else{
            system('clear');
            $this->error(__('qanda.error.invalid_question_id'));
            sleep(1);  //show user the error for 1 second, then ask again
            $this->listQuestions();
        }
    }

    private function answerQuestion($questionData)
    {
        $answer = $this->getInput('ask', __('qanda.question').$questionData->question.__('qanda.answer'), 'mainScreen');
        if ($answer) {
            $this->progressService->setStatus($questionData, $answer);
        }
    }

    private function showOverview()
    {
        $this->listQuestions();
        $this->info(__('qanda.final_process'));
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