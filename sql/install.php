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
$sql = array();

$sql[0] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'quiz_bhp` (
    `id_quiz_bhp` int(11) NOT NULL AUTO_INCREMENT,
    `name` varchar(512) NOT NULL,
    `percent_to_sentence` int(8) NOT NULL,
    PRIMARY KEY  (`id_quiz_bhp`)
) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8;';

$sql[1] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'quiz_bhp_question` (
    `id_quiz_bhp_question` int(11) NOT NULL AUTO_INCREMENT,
    `id_quiz_bhp` int(11) NOT NULL,
    `question` varchar(512) NOT NULL,
    PRIMARY KEY  (`id_quiz_bhp_question`),
    FOREIGN KEY (`id_quiz_bhp`) REFERENCES `' . _DB_PREFIX_ . 'quiz_bhp`(`id_quiz_bhp`)
) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8;';

$sql[2] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'quiz_answer` (
    `id_quiz_answer` int(11) NOT NULL AUTO_INCREMENT,
    `id_quiz_bhp_question` int(11) NOT NULL,
    `answer` varchar(512) NOT NULL,
    `correct_answer` tinyint(1) NOT NULL,
    PRIMARY KEY  (`id_quiz_answer`),
    FOREIGN KEY (`id_quiz_bhp_question`) REFERENCES `' . _DB_PREFIX_ . 'quiz_bhp_question`(`id_quiz_bhp_question`)
) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8;';

$sql[3] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'quiz_result` (
    `id_quiz_result` int(11) NOT NULL AUTO_INCREMENT,
    `id_quiz_bhp` int(11) NOT NULL,
    `id_customer` int(11) NOT NULL,
    `filled_form` tinyint(1) NOT NULL DEFAULT 0,
    PRIMARY KEY  (`id_quiz_result`),
    FOREIGN KEY (`id_quiz_bhp`) REFERENCES `' . _DB_PREFIX_ . 'quiz_bhp`(`id_quiz_bhp`)
) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8;';

foreach ($sql as $query) {
    if (Db::getInstance()->execute($query) == false) {
        return false;
    }
}