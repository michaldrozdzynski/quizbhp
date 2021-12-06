<?php

class QuizBhpQuizSummaryModuleFrontController extends ModuleFrontController {
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
        $query = 'SELECT * FROM `' . _DB_PREFIX_ . 'quiz_result` WHERE id_quiz_bhp = \'' . Tools::getValue('quizId') . '\'';
        $row = Db::getInstance()->getRow($query);

        if ($row['id_customer'] != Context::getContext()->customer->id || $row['filled_form'] != 0) {
            Tools::redirect(Context::getContext()->link->getModuleLink('quizbhp', 'choosequiz'));
        }

        $this->setTemplate('module:quizbhp/views/templates/front/quizsummary.tpl');
    }

    public function processPostRequest() {
        $query = 'UPDATE `' . _DB_PREFIX_ . 'quiz_result` SET filled_form = 1 WHERE id_quiz_bhp = \'' . Tools::getValue('quizId') . '\' AND id_customer = ' . Context::getContext()->customer->id;
        Db::getInstance()->execute($query);
        $name = Tools::getValue('name');
        $surname = Tools::getValue('surname');
        $placeOfBirth = Tools::getValue('placeOfBirth');
        $dateOfBirth = Tools::getValue('dateOfBirth');
        $query = 'SELECT * FROM `' . _DB_PREFIX_ . 'quiz_bhp` WHERE id_quiz_bhp = \'' . Tools::getValue('quizId') . '\'';
        $row = Db::getInstance()->getRow($query);

        $quizName = $row['name'];
        $this->sendMail($name, $surname, $placeOfBirth, $quizName, $dateOfBirth);
        
        Tools::redirect(Context::getContext()->link->getModuleLink('quizbhp', 'choosequiz'));
    }

    public function sendMail($name, $surname, $placeOfBirth, $quizName, $dateOfBirth) {
        Mail::Send(
           (int)(Configuration::get('PS_LANG_DEFAULT')), // defaut language id
           'passed_test', // email template file to be use
           'Zdany test ' . $quizName, // email subject
           array(
               '{email}' => Configuration::get('PS_SHOP_EMAIL'), // sender email address
               '{name}' => $name,
               '{surname}' => $surname,
               '{placeOfBirth}' => $placeOfBirth,
               '{dateOfBirth}' => $dateOfBirth,
           ),
           Configuration::get('PS_SHOP_EMAIL'), // receiver email address
           NULL, //receiver name
           NULL, //from email address
           NULL,  //from name
           NULL, //file attachment
           NULL, //mode smtp
           _PS_MODULE_DIR_ . 'quizbhp/mails' //custom template path
       );
   }
}