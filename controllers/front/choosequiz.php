<?php

class QuizBhpChooseQuizModuleFrontController extends ModuleFrontController {
	public function initContent() {
    	parent::initContent();

        $query = 'SELECT name, id_quiz_bhp FROM `' . _DB_PREFIX_ . 'quiz_bhp` WHERE 1';
        $quizList = Db::getInstance()->executeS($query);

        $this->context->smarty->assign('quizList', $quizList);   
        $this->setTemplate('module:quizbhp/views/templates/front/choosequiz.tpl');
	}
}