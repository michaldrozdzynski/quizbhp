<?php

class QuizBhpChooseQuizModuleFrontController extends ModuleFrontController {
	public function initContent() {
    	parent::initContent();

        $query = 'SELECT name, id_quiz_bhp FROM `' . _DB_PREFIX_ . 'quiz_bhp` WHERE 1';
        $quizList = Db::getInstance()->executeS($query);

        $query = 'SELECT id_quiz_bhp FROM `' . _DB_PREFIX_ . 'quiz_result` WHERE filled_form =  1 AND id_customer = ' . Context::getContext()->customer->id;
        $results = Db::getInstance()->executeS($query);

        foreach($quizList as $key => $quiz) {
            foreach($results as $result) {
                if ($result['id_quiz_bhp'] == $quiz['id_quiz_bhp']) {
                    unset($quizList[$key]);
                    break;
                }
            }
        }

        $this->context->smarty->assign('quizList', $quizList);   
        $this->setTemplate('module:quizbhp/views/templates/front/choosequiz.tpl');
	}
}