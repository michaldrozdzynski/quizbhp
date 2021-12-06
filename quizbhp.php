<?php
/**
* 2007-2021 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author    PrestaShop SA <contact@prestashop.com>
*  @copyright 2007-2021 PrestaShop SA
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

if (!defined('_PS_VERSION_')) {
    exit;
}


require_once('classes/QuizBhpClass.php');

class Quizbhp extends Module
{
    protected $config_form = false;

    public function __construct()
    {
        $this->name = 'quizbhp';
        $this->tab = 'front_office_features';
        $this->version = '1.0.0';
        $this->author = 'Michał Drożdżyński';
        $this->need_instance = 0;

        /**
         * Set $this->bootstrap to true if your module is compliant with bootstrap (PrestaShop 1.6)
         */
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('Quiz BHP');
        $this->description = $this->l('Create bhp quiz for your customer');

        $this->ps_versions_compliancy = array('min' => '1.7', 'max' => _PS_VERSION_);
    }

    /**
     * Don't forget to create update methods if needed:
     * http://doc.prestashop.com/display/PS16/Enabling+the+Auto-Update
     */
    public function install()
    {
        include(dirname(__FILE__).'/sql/install.php');

        return parent::install() &&
            $this->registerHook('header') &&
            $this->registerHook('backOfficeHeader') &&
            $this->registerHook('displayCustomerAccount');
    }

    public function uninstall()
    {
        include(dirname(__FILE__).'/sql/uninstall.php');

        return parent::uninstall();
    }

    /**
     * Load the configuration form
     */
    public function getContent()
    {
        if (Tools::isSubmit('addQuiz')) {
            return $this->addQuiz();
        }

        if (Tools::isSubmit('submitAddQuiz')) {
            $name = Tools::getValue('quizname');
            $percent = Tools::getValue('percent_to_sentence');

            $db = Db::getInstance();
            $db->insert("quiz_bhp", [
                'name' => $name,
                'percent_to_sentence' => $percent,
            ]);
        }

        if (Tools::isSubmit('submitEditQuiz')) {
            $name = Tools::getValue('quizname');
            $percent = Tools::getValue('percent_to_sentence');
            $quizId = Tools::getValue('id_quiz_bhp');

            $db = Db::getInstance();
            $db->update("quiz_bhp", [
                'name' => $name,
                'percent_to_sentence' => $percent,
            ], 'id_quiz_bhp = ' . $quizId);
        }

        if (Tools::isSubmit('viewquiz_bhp')) {
            if (Tools::isSubmit('addQuestion')) {
                return $this->addQuestion();
            }

            return $this->questionList();
        }

        if (Tools::isSubmit('submitAddQuestion')) {
            $question = Tools::getValue('question');
            $quizId = Tools::getValue('id_quiz_bhp');

            $db = Db::getInstance();
            $db->insert("quiz_bhp_question", [
                'question' => $question,
                'id_quiz_bhp' => $quizId,
            ]);
            $url = Context::getContext()->link->getAdminLink('AdminModules', true, [], ['configure' => 'quizbhp', 'viewquiz_bhp' => '', 'id_quiz_bhp' => $quizId]);
            Tools::redirect($url);
        }

        if (Tools::isSubmit('submitEditQuestion')) {
            $question = Tools::getValue('question');
            $quizId = Tools::getValue('id_quiz_bhp');
            $questionId = Tools::getValue('id_quiz_bhp_question');

            $db = Db::getInstance();
            $db->update('quiz_bhp_question', [
                'question' => $question,
            ], 'id_quiz_bhp_question = ' . $questionId);

            $url = Context::getContext()->link->getAdminLink('AdminModules', true, [], ['configure' => 'quizbhp', 'viewquiz_bhp' => '', 'id_quiz_bhp' => $quizId]);
            Tools::redirect($url);
        }

        if (Tools::isSubmit('viewquiz_bhp_question')) {
            if (Tools::isSubmit('addAnswer')) {
                return $this->addAnswer();
            }

            return $this->answerList();
        }

        if (Tools::isSubmit('submitAddAnswer')) {
            $questionId = Tools::getValue('id_quiz_bhp_question');
            $answer = Tools::getValue('answer');

            $db = Db::getInstance();
            $db->insert("quiz_answer", [
                'answer' => $answer,
                'id_quiz_bhp_question' => $questionId,
            ]);

            $url = Context::getContext()->link->getAdminLink('AdminModules', true, [], ['configure' => 'quizbhp', 'viewquiz_bhp_question' => '', 'id_quiz_bhp_question' => $questionId]);
            Tools::redirect($url);
        }

        if (Tools::isSubmit('correctquiz_answer')) {
            $answerId = Tools::getValue('id_quiz_answer');

            $query = 'SELECT id_quiz_bhp_question FROM `' . _DB_PREFIX_ . 'quiz_answer` WHERE id_quiz_answer = ' . $answerId;
            $db = Db::getInstance();
            $row = $db->getRow($query);

            $questionId = $row['id_quiz_bhp_question'];
            $request = "UPDATE " . _DB_PREFIX_ . "quiz_answer SET correct_answer = 0 WHERE id_quiz_bhp_question = " . $questionId;
            $db->execute($request);

            $request = "UPDATE " . _DB_PREFIX_ . "quiz_answer SET correct_answer = 1 WHERE id_quiz_answer = " . $answerId;
            $db->execute($request);

            $url = Context::getContext()->link->getAdminLink('AdminModules', true, [], ['configure' => 'quizbhp', 'viewquiz_bhp_question' => '', 'id_quiz_bhp_question' => $questionId]);

            Tools::redirect($url);
        }

        if (Tools::isSubmit('deletequiz_answer')) {
            $answerId = Tools::getValue('id_quiz_answer');

            $query = 'SELECT id_quiz_bhp_question FROM `' . _DB_PREFIX_ . 'quiz_answer` WHERE id_quiz_answer = ' . $answerId;
            $db = Db::getInstance();
            $row = $db->getRow($query);
            $questionId = $row['id_quiz_bhp_question'];

            $db->delete('quiz_answer', 'id_quiz_answer = ' . $answerId);

            $url = Context::getContext()->link->getAdminLink('AdminModules', true, [], ['configure' => 'quizbhp', 'viewquiz_bhp_question' => '', 'id_quiz_bhp_question' => $questionId]);

            Tools::redirect($url);
        }

        if (Tools::isSubmit('deletequiz_bhp_question')) {
            $questionId = Tools::getValue('id_quiz_bhp_question');

            $query = 'SELECT id_quiz_bhp FROM `' . _DB_PREFIX_ . 'quiz_bhp_question` WHERE id_quiz_bhp_question = ' . $questionId;
            $db = Db::getInstance();
            $row = $db->getRow($query);
            $quizId = $row['id_quiz_bhp'];
            $db->delete('quiz_answer', 'id_quiz_bhp_question = ' . $questionId);
            $db->delete('quiz_bhp_question', 'id_quiz_bhp_question = ' . $questionId);

            $url = Context::getContext()->link->getAdminLink('AdminModules', true, [], ['configure' => 'quizbhp', 'viewquiz_bhp' => '', 'id_quiz_bhp' => $quizId]);
            Tools::redirect($url);
        }

        if (Tools::isSubmit('updatequiz_bhp_question')) {
            return $this->editQuestion();
        }

        if (Tools::isSubmit('updatequiz_bhp')) {
            return $this->editQuiz();
        }

        if (Tools::isSubmit('deletequiz_bhp')) {
            $quizId = Tools::getValue('id_quiz_bhp');
            $db = Db::getInstance();
            $query = 'SELECT * FROM `' . _DB_PREFIX_ . 'quiz_bhp_question` WHERE id_quiz_bhp = ' . $quizId;
            $questions = $db->executeS($query);

            foreach ($questions as $question) {
                $questionId = $question['id_quiz_bhp_question'];
                $db->delete('quiz_answer', 'id_quiz_bhp_question = ' . $questionId);
            }

            $db->delete('quiz_result', 'id_quiz_bhp = ' . $quizId);
            $db->delete('quiz_bhp_question', 'id_quiz_bhp = ' . $quizId);
            $db->delete('quiz_bhp', 'id_quiz_bhp = ' . $quizId);

            $url = Context::getContext()->link->getAdminLink('AdminModules', true, [], ['configure' => 'quizbhp']);
            Tools::redirect($url);
        }

        return $this->quizTable();
    }

    private function addAnswer() {
        $fields_form = [
            'form' => [
                'legend' => [
                    'title' => $this->l('Add Answer'),
                    'icon' => 'icon-link',
                ],
                'input' => [
                    [
                        'type' => 'text',
                        'label' => $this->l('Answer'),
                        'name' => 'answer',
                        'required' => true,
                    ],
                    [
                        'type' => 'hidden',
                        'name' => 'id_quiz_bhp_question',
                        'required' => true,
                    ],
                ],
                'submit' => [
                    'name' => 'submitAddAnswer',
                    'title' => $this->trans('Save', [], 'Admin.Actions'),
                ],
                'buttons' => array(
                    array(
                        'href' => AdminController::$currentIndex.'&configure='.$this->name.'&viewquiz_bhp_question=&id_quiz_bhp_question='.Tools::getValue('id_quiz_bhp_question').'&token='.Tools::getAdminTokenLite('AdminModules'),
                        'title' => $this->l('Back to list'),
                        'icon' => 'process-icon-back'
                    )
                )
            ],
        ];
   
        $helper = new HelperForm();
        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $lang = new Language((int) Configuration::get('PS_LANG_DEFAULT'));
        $helper->default_form_language = $lang->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') ? Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') : 0;
        $helper->module = $this;
        $helper->identifier = $this->identifier;
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false) .
            '&configure=' . $this->name . '&tab_module=' . $this->tab . '&module_name=' . $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->tpl_vars = [
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
        ];
        $helper->fields_value = array(
            'id_quiz_bhp_question' => Tools::getValue('id_quiz_bhp_question'),
        );

        return $helper->generateForm([$fields_form]);
    }

    private function answerList() {
        $idQuestion = Tools::getValue('id_quiz_bhp_question');

        $sql = 'SELECT * FROM ' . _DB_PREFIX_ . 'quiz_answer WHERE id_quiz_bhp_question='. $idQuestion .' ORDER BY id_quiz_answer';

        $results = Db::getInstance()->executeS($sql);
        $fields_list = array(
            'id_quiz_answer'=> array(
                'title' => "ID",
                'align' => 'center',
                'class' => 'fixed-width-xs',
                'search' => false,
                'orderby' => true,
            ),
            'answer' => array(
                'title' => $this->l('Answer'),
                'class' => 'fixed-width-xxl',
                'search' => false
            ),
            'correct_answer' => array(
                'title' => $this->l('Correct Answer'),
                'class' => 'fixed-width-xxl',
                'align' => 'center',
        		'active' => 'correct',
        		'type' => 'bool',
                'width' => 25,
                'search' => false,
                'orderby' => false,
            ),
        );
  
        $helper = new HelperList();
        $helper->shopLinkType = '';
        $helper->simple_header = false;
        $helper->identifier = 'id_quiz_answer';
        $helper->table = 'quiz_answer';
        $helper->actions = ['delete'];
        $helper->show_toolbar = false;
        $helper->listTotal = count($results);
        $helper->_default_pagination = 10;
        $helper->_pagination = array(5, 10, 50, 100);
        $helper->toolbar_btn['new'] = [
            'href' => $this->context->link->getAdminLink('AdminModules', true, [], ['configure' => $this->name, 'module_name' => $this->name, 'addAnswer' => '', 'viewquiz_bhp_question' => '', 'id_quiz_bhp_question' => Tools::getValue('id_quiz_bhp_question')]),
            'desc' => $this->l('Add New Answer'),
        ];
        $helper->module = $this;
        $helper->title = $this->l('Answer List');
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->currentIndex = AdminController::$currentIndex . '&configure=' . $this->name;
        $page = ( $page = Tools::getValue( 'submitFilter' . $helper->table ) ) ? $page : 1;
        $pagination = ( $pagination = Tools::getValue( $helper->table . '_pagination' ) ) ? $pagination : 10;
        $content = $this->paginate_content( $results, $page, $pagination );

        $sql = 'SELECT * FROM ' . _DB_PREFIX_ . 'quiz_bhp_question WHERE id_quiz_bhp_question='. $idQuestion;

        $results = Db::getInstance()->executeS($sql);
        $quizId = $results[0]['id_quiz_bhp'];

        $this->context->smarty->assign('quizId', $quizId);
        return $helper->generateList($content, $fields_list).$this->display(__FILE__, 'views/templates/admin/backToQuestionList.tpl');
    }

    private function questionList() {
        $idQuiz = Tools::getValue('id_quiz_bhp');

        $sql = 'SELECT * FROM ' . _DB_PREFIX_ . 'quiz_bhp_question WHERE id_quiz_bhp='. $idQuiz .' ORDER BY id_quiz_bhp';

        $results = Db::getInstance()->executeS($sql);
        $fields_list = array(
            'id_quiz_bhp_question'=> array(
                'title' => "ID",
                'align' => 'center',
                'class' => 'fixed-width-xs',
                'search' => false,
                'orderby' => true,
            ),
            'question' => array(
                'title' => $this->l('Question'),
                'class' => 'fixed-width-xxl',
                'search' => false
            ),
        );
  
        $helper = new HelperList();
        $helper->shopLinkType = '';
        $helper->simple_header = false;
        $helper->identifier = 'id_quiz_bhp_question';
        $helper->table = 'quiz_bhp_question';
        $helper->actions = ['view', 'delete', 'edit'];
        $helper->show_toolbar = false;
        $helper->listTotal = count($results);
        $helper->_default_pagination = 10;
        $helper->_pagination = array(5, 10, 50, 100);
        $helper->toolbar_btn['new'] = [
            'href' => $this->context->link->getAdminLink('AdminModules', true, [], ['configure' => $this->name, 'module_name' => $this->name,'viewquiz_bhp' => '', 'addQuestion' => '', 'id_quiz_bhp' => Tools::getValue('id_quiz_bhp')]),
            'desc' => $this->l('Add New Quiz'),
        ];
        $helper->module = $this;
        $helper->title = $this->l('Question List');
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->currentIndex = AdminController::$currentIndex . '&configure=' . $this->name;
        $page = ( $page = Tools::getValue( 'submitFilter' . $helper->table ) ) ? $page : 1;
        $pagination = ( $pagination = Tools::getValue( $helper->table . '_pagination' ) ) ? $pagination : 10;
        $content = $this->paginate_content( $results, $page, $pagination );

        return $helper->generateList($content, $fields_list).$this->display(__FILE__, 'views/templates/admin/backToQuizList.tpl');;
    }

    private function addQuiz() {
        $fields_form = [
            'form' => [
                'legend' => [
                    'title' => $this->l('Add Quiz'),
                    'icon' => 'icon-link',
                ],
                'input' => [
                    [
                        'type' => 'text',
                        'label' => $this->l('Quiz Name'),
                        'name' => 'quizname',
                        'required' => true,
                    ],
                    [
                        'type' => 'text',
                        'label' => $this->l('Percentage to pass the exam'),
                        'name' => 'percent_to_sentence',
                        'class' => 'fixed-width-xs',
                        'required' => true,
                        'desc' => $this->l('Enter a number from 1 to 100'),
                    ],
                ],
                'submit' => [
                    'name' => 'submitAddQuiz',
                    'title' => $this->trans('Save', [], 'Admin.Actions'),
                ],
                'buttons' => array(
                    array(
                        'href' => AdminController::$currentIndex.'&configure='.$this->name.'&token='.Tools::getAdminTokenLite('AdminModules'),
                        'title' => $this->l('Back to list'),
                        'icon' => 'process-icon-back'
                    )
                )
            ],
        ];
   
        $helper = new HelperForm();
        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $lang = new Language((int) Configuration::get('PS_LANG_DEFAULT'));
        $helper->default_form_language = $lang->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') ? Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') : 0;
        $helper->module = $this;
        $helper->identifier = $this->identifier;
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false) .
            '&configure=' . $this->name . '&tab_module=' . $this->tab . '&module_name=' . $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->tpl_vars = [
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
        ];

        return $helper->generateForm([$fields_form]);
    }

    private function editQuiz() {
        $quizId = Tools::getValue('id_quiz_bhp');

        $query = 'SELECT * FROM `' . _DB_PREFIX_ . 'quiz_bhp` WHERE id_quiz_bhp= ' . $quizId;
        $db = Db::getInstance();
        $row = $db->getRow($query);

        $name = $row['name'];
        $percent = $row ['percent_to_sentence'];

        $fields_form = [
            'form' => [
                'legend' => [
                    'title' => $this->l('Edit Quiz'),
                    'icon' => 'icon-link',
                ],
                'input' => [
                    [
                        'type' => 'text',
                        'label' => $this->l('Quiz Name'),
                        'name' => 'quizname',
                        'required' => true,
                    ],
                    [
                        'type' => 'text',
                        'label' => $this->l('Percentage to pass the exam'),
                        'name' => 'percent_to_sentence',
                        'class' => 'fixed-width-xs',
                        'required' => true,
                        'desc' => $this->l('Enter a number from 1 to 100'),
                    ],
                    [
                        'type' => 'hidden',
                        'name' => 'id_quiz_bhp',
                    ]
                    
                ],
                'submit' => [
                    'name' => 'submitEditQuiz',
                    'title' => $this->trans('Save', [], 'Admin.Actions'),
                ],
                'buttons' => array(
                    array(
                        'href' => AdminController::$currentIndex.'&configure='.$this->name.'&token='.Tools::getAdminTokenLite('AdminModules'),
                        'title' => $this->l('Back to list'),
                        'icon' => 'process-icon-back'
                    )
                )
            ],
        ];
   
        $helper = new HelperForm();
        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $lang = new Language((int) Configuration::get('PS_LANG_DEFAULT'));
        $helper->default_form_language = $lang->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') ? Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') : 0;
        $helper->module = $this;
        $helper->identifier = $this->identifier;
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false) .
            '&configure=' . $this->name . '&tab_module=' . $this->tab . '&module_name=' . $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->tpl_vars = [
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
        ];
        $helper->fields_value = array(
            'id_quiz_bhp' => $quizId,
            'quizname' => $name,
            'percent_to_sentence' => $percent,
        );

        return $helper->generateForm([$fields_form]);
    }

    private function addQuestion() {
        $fields_form = [
            'form' => [
                'legend' => [
                    'title' => $this->l('Add question'),
                    'icon' => 'icon-link',
                ],
                'input' => [
                    [
                        'type' => 'text',
                        'label' => $this->l('Question'),
                        'name' => 'question',
                        'required' => true,
                    ],
                    [
                        'type' => 'hidden',
                        'name' => 'id_quiz_bhp',
                    ],
                ],
                'submit' => [
                    'name' => 'submitAddQuestion'.Tools::getValue(''),
                    'title' => $this->trans('Save', [], 'Admin.Actions'),
                ],
                'buttons' => array(
                    array(
                        'href' => AdminController::$currentIndex.'&configure='.$this->name.'&viewquiz_bhp=&id_quiz_bhp='.Tools::getValue('id_quiz_bhp').'&token='.Tools::getAdminTokenLite('AdminModules'),
                        'title' => $this->l('Back to list'),
                        'icon' => 'process-icon-back'
                    )
                )
            ],
        ];
   
        $helper = new HelperForm();
        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $lang = new Language((int) Configuration::get('PS_LANG_DEFAULT'));
        $helper->default_form_language = $lang->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') ? Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') : 0;
        $helper->module = $this;
        $helper->identifier = $this->identifier;
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false) .
            '&configure=' . $this->name . '&tab_module=' . $this->tab . '&module_name=' . $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->tpl_vars = [
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
        ];
        $helper->fields_value = array(
            'id_quiz_bhp' => Tools::getValue('id_quiz_bhp'),
        );

        return $helper->generateForm([$fields_form]);
    }

    private function editQuestion() {
        $questionId = Tools::getValue('id_quiz_bhp_question');

        $query = 'SELECT * FROM `' . _DB_PREFIX_ . 'quiz_bhp_question` WHERE id_quiz_bhp_question = ' . $questionId;
        $db = Db::getInstance();
        $row = $db->getRow($query);

        $quizId = $row['id_quiz_bhp'];
        $question = $row['question'];

        $fields_form = [
            'form' => [
                'legend' => [
                    'title' => $this->l('Edit question'),
                    'icon' => 'icon-link',
                ],
                'input' => [
                    [
                        'type' => 'text',
                        'label' => $this->l('Question'),
                        'name' => 'question',
                        'required' => true,
                    ],
                    [
                        'type' => 'hidden',
                        'name' => 'id_quiz_bhp',
                    ],
                    [
                        'type' => 'hidden',
                        'name' => 'id_quiz_bhp_question',
                    ]
                ],
                'submit' => [
                    'name' => 'submitEditQuestion'.Tools::getValue(''),
                    'title' => $this->trans('Save', [], 'Admin.Actions'),
                ],
                'buttons' => array(
                    array(
                        'href' => AdminController::$currentIndex.'&configure='.$this->name.'&viewquiz_bhp=&id_quiz_bhp='.$quizId.'&token='.Tools::getAdminTokenLite('AdminModules'),
                        'title' => $this->l('Back to list'),
                        'icon' => 'process-icon-back'
                    )
                )
            ],
        ];

        $helper = new HelperForm();
        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $lang = new Language((int) Configuration::get('PS_LANG_DEFAULT'));
        $helper->default_form_language = $lang->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') ? Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') : 0;
        $helper->module = $this;
        $helper->identifier = $this->identifier;
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false) .
            '&configure=' . $this->name . '&tab_module=' . $this->tab . '&module_name=' . $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->tpl_vars = [
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
        ];
        $helper->fields_value = array(
            'id_quiz_bhp' => $quizId,
            'id_quiz_bhp_question' => $questionId,
            'question' => $question,
        );

        return $helper->generateForm([$fields_form]);
    }

    private function quizTable() {
        $sql = 'SELECT * FROM ' . _DB_PREFIX_ . 'quiz_bhp ORDER BY id_quiz_bhp';

        $results = Db::getInstance()->executeS($sql);
        $fields_list = array(
            'id_quiz_bhp'=> array(
                'title' => "ID",
                'align' => 'center',
                'class' => 'fixed-width-xs',
                'search' => false,
                'orderby' => true,
            ),
            'name' => array(
                'title' => 'Quiz Name',
                'class' => 'fixed-width-xxl',
                'search' => false
            ),
            'percent_to_sentence' => array(
                'title' => 'Percentage to pass the exam',
                'class' => 'fixed-width-xxl',
                'search' => false
            ),
        );
  
        $helper = new HelperList();
        $helper->shopLinkType = '';
        $helper->simple_header = false;
        $helper->identifier = 'id_quiz_bhp';
        $helper->table = 'quiz_bhp';
        $helper->actions = ['view', 'delete', 'edit'];
        $helper->show_toolbar = false;
        $helper->listTotal = count($results);
        $helper->_default_pagination = 10;
        $helper->_pagination = array(5, 10, 50, 100);
        $helper->toolbar_btn['new'] = [
            'href' => $this->context->link->getAdminLink('AdminModules', true, [], ['configure' => $this->name, 'module_name' => $this->name, 'addQuiz' => '']),
            'desc' => $this->l('Add New Quiz'),
        ];
        $helper->module = $this;
        $helper->title = $this->l('Quiz List');
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->currentIndex = AdminController::$currentIndex . '&configure=' . $this->name;
        $page = ( $page = Tools::getValue( 'submitFilter' . $helper->table ) ) ? $page : 1;
        $pagination = ( $pagination = Tools::getValue( $helper->table . '_pagination' ) ) ? $pagination : 10;
        $content = $this->paginate_content( $results, $page, $pagination );

        return $helper->generateList($content, $fields_list);
    }

    /**
    * Add the CSS & JavaScript files you want to be loaded in the BO.
    */
    public function hookBackOfficeHeader()
    {
        if (Tools::getValue('module_name') == $this->name) {
            $this->context->controller->addJS($this->_path.'views/js/back.js');
            $this->context->controller->addCSS($this->_path.'views/css/back.css');
        }
    }

    /**
     * Add the CSS & JavaScript files you want to be added on the FO.
     */
    public function hookHeader()
    {
        $this->context->controller->addJS($this->_path.'/views/js/front.js');
        $this->context->controller->addCSS($this->_path.'/views/css/front.css');
    }

    public function hookDisplayCustomerAccount() {
        return $this->display(__FILE__, 'views/templates/hook/quizbhp.tpl');
    }

    private function paginate_content( $content, $page = 1, $pagination = 10 ) {
        if( count($content) > $pagination ) {
             $content = array_slice( $content, $pagination * ($page - 1), $pagination );
        }
     
        return $content;
    }
}
