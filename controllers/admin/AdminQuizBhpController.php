<?php

class AdminQuizBhpController extends ModuleAdminController{
    public function __construct()
    {
      $db = \Db::getInstance();
      $id_lang = Context::getContext()->language->id;

      $this->table = "quiz_bhp";
      $this->className = "QuizBhpClass";

      $this->fields_list = array(
        'id_quiz_bhp'=> array(
            'title' => "ID",
            'orderby' => true,
            'search' => false,
            'align' => 'center',
            'class' => 'fixed-width-xs'
          ),
        'question' => array(
            'title' => 'question',
            'orderby' => false,
            'search' => false,
            'class' => 'fixed-width-xxl'
        ),
      );

      $this->actions = ['edit', 'delete', 'view'];

      $this->bulk_actions = array(
            'delete' => array(
                'text'    => 'Delete selected',
                'icon'    => 'icon-trash',
                'confirm' => 'Delete selected items?',
            ),
        );

        $this->fields_form = [
            'legend' => [
                'title' => 'Quiz BHP',
            ],
            'input' => [
                [
                    'type' => 'text',
                    'label' => 'Question',
                    'name' => 'question',
                    'required' => true
                ],
            ],
            'submit' => [
                'title' => 'Save',
                'class' => 'btn btn-default pull-right'
            ]
        ];

      $this->bootstrap = true;
      parent::__construct();
    }
}
