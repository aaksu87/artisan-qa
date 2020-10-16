<?php
namespace App\Exceptions;


class DuplicateQuestionException extends \Exception
{
    public function __construct($message) {

        parent::__construct($message);
    }
}
