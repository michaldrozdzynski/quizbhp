<?php

class QuizBhpQuizModuleFrontController extends ModuleFrontController {
	public function initContent() {
    	parent::initContent();

        switch ($_SERVER['REQUEST_METHOD']) {
            case 'GET':
                $this->processGetRequest();
                break;
            case 'POST':
                $this->processPostRequest();
                break;
        }
	}

    public function processGetRequest() {
        $db = Db::getInstance();

        $query = 'SELECT name, id_quiz_bhp FROM `' . _DB_PREFIX_ . 'quiz_bhp` WHERE id_quiz_bhp = \'' . Tools::getValue('quizId') . '\'';
        $result = Db::getInstance()->executeS($query);
        if (count($result) == 0) {
            Tools::redirect(Context::getContext()->link->getModuleLink('quizbhp', 'choosequiz'));
        }
        $quizId = $result[0]['id_quiz_bhp'];
        $this->context->smarty->assign('quizName', $result[0]['name']);

        $query = 'SELECT * FROM `' . _DB_PREFIX_ . 'quiz_bhp_question` WHERE id_quiz_bhp = ' . $quizId .' ORDER BY RAND()';
        $questions = $db->executeS($query);

        foreach ($questions as $key => $question) {
            $questionId = $question['id_quiz_bhp_question'];

            $query = 'SELECT * FROM `' . _DB_PREFIX_ . 'quiz_answer` WHERE id_quiz_bhp_question = ' . $questionId .' ORDER BY RAND()';
            $answers = $db->executeS($query);

            $questions[$key]['answers'] = $answers;
        }
        $quizResult = Tools::getValue('quizResult');

        $this->context->smarty->assign('quizResult', $quizResult);        
        $this->context->smarty->assign('questions', $questions);
        $this->setTemplate('module:quizbhp/views/templates/front/quiz.tpl');
    }

    public function processPostRequest() {
        dump($_POST);
        $point = 0.0;
        $maxPoint = count($_POST) - 1;
        foreach ($_POST as $key => $item) {
            if ($key == 'quizId') {
                continue;
            }

            $query = 'SELECT * FROM ' . _DB_PREFIX_ . 'quiz_answer WHERE id_quiz_answer = ' . $item;
            $result = Db::getInstance()->getRow($query);

            if ($result['correct_answer'] == 1) {
                $point++;
            }
        }

        $query = 'SELECT percent_to_sentence FROM ' . _DB_PREFIX_ . 'quiz_bhp WHERE id_quiz_bhp = ' . Tools::getValue('quizId');
        $result = Db::getInstance()->getRow($query);
        $percent = $result['percent_to_sentence'] / 100;

        if ($point / $maxPoint > $percent) {
            $db = Db::getInstance();
            Tools::redirect(Context::getContext()->link->getModuleLink('quizbhp', 'quizsummary', ['quizId' => Tools::getValue('quizId')]));
        } else {
            Tools::redirect(Context::getContext()->link->getModuleLink('quizbhp', 'quiz', ['quizResult' => 'failedQuiz', 'quizId' => Tools::getValue('quizId')]));
        }
    }
}