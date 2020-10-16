<?php
namespace App\Exceptions;


class InvalidInputException extends \Exception
{
    public function __construct($message) {

        parent::__construct($message);
    }
}
