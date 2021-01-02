<?php
namespace App\Exceptions;


class NoDataException extends \Exception
{
    public function __construct($message) {

        parent::__construct($message);
    }
}
