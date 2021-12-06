<?php

class QuizBhpClass extends ObjectModelCore
{
    public $id_quiz_bhp;
    public $question;

    public static $definition = array(
      'table' => 'quiz_bhp',
      'primary' => 'id_quiz_bhp',
      'multilang' => false,
      'fields' => array(
        'id_quiz_bhp'=> array('type' => self::TYPE_NOTHING, 'validate' => 'isUnsignedId'),
        'question' => array('type' => self::TYPE_STRING, 'validate' => 'isGenericName', 'required' => true),
      )
    );
}