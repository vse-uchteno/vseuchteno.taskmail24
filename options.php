<?php
#####################################################
# Bitrix: Module TaskMail24                         #
# Copyright (c) 2020 D.Starovoytov (VseUchteno)     #
# mailto:denis@starovoytov.online                   #
#####################################################

defined('B_PROLOG_INCLUDED') || die;

use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Config\Option;
use Vseuchteno\Taskmail24\B24mail;
use Vseuchteno\Taskmail24\B24task;
use Vseuchteno\Taskmail24\B24RestApi;
use Vseuchteno\Taskmail24\ModuleHelper;

Loc::loadMessages(__FILE__);

global $APPLICATION, $USER;

if (!$USER->IsAdmin()) {
	return;
}

$module_id = 'vseuchteno.taskmail24';
Loader::includeModule($module_id);

$group_id = Option::get($module_id, 'CREATE_TASK_IN_GROUP', false);
$mailbox_id = Option::get($module_id, 'CONTROL_MAILBOX', false);


$obB24mail = new B24mail();
$obB24task = new B24task();
$obB24RestApi = new B24RestApi();

$arMailboxes[0] = Loc::getMessage("VUTM24_OPTION_NOT_SELECTED");
$arMailboxes= array_merge($arMailboxes,ModuleHelper::prepareArrayForOption($obB24mail->getAllMailboxes(), 'ID', 'NAME'));

$arMailboxDirs[""] = Loc::getMessage("VUTM24_OPTION_NOT_SELECTED");
if ($mailbox_id) {
	$arMailboxDirs= array_replace($arMailboxDirs,ModuleHelper::prepareArrayForOption($obB24mail->getMailboxFolders($mailbox_id), false, false));
}



$arGroups[0] = Loc::getMessage("VUTM24_OPTION_NOT_SELECTED");
$arGroups= array_replace($arGroups,ModuleHelper::prepareArrayForOption($obB24task->getAllGroups(), 'ID', 'NAME'));

$arCompetitors[0] = Loc::getMessage("VUTM24_OPTION_NOT_SELECTED");
$arCompetitors= array_replace($arCompetitors,ModuleHelper::prepareArrayForOption($obB24task->getAllCompetitors(), 'ID', 'TITLE'));


$arResponsible[0] = Loc::getMessage("VUTM24_OPTION_NOT_SELECTED");
if ($group_id) {
	$arResponsible= array_replace($arResponsible,ModuleHelper::prepareArrayForOption($obB24task->getAllUsersByGroup($group_id), 'ID', 'TITLE'));
}

$arOutterGroups[0] = Loc::getMessage("VUTM24_OPTION_NOT_SELECTED");
$arOutterGroups= array_replace($arOutterGroups,ModuleHelper::prepareArrayForOption($obB24RestApi->getAllSonetGroups(), 'ID', 'NAME'));




$tabs = array(
	array(
		'DIV' => 'general',
		'TAB' => Loc::getMessage("VUTM24_OPTION_GENERAL_TITLE"),
		'TITLE' => Loc::getMessage("VUTM24_OPTION_GENERAL_DESCRIPTION"),
	),
	array(
		'DIV' => 'outterb24',
		'TAB' => Loc::getMessage("VUTM24_OPTION_OUTER_B24_TITLE"),
		'TITLE' => Loc::getMessage("VUTM24_OPTION_OUTER_B24_DESCRIPTION"),
	),
);

$options = array(
	'general' => array(
		Array("CONTROL_MAILBOX", Loc::getMessage("VUTM24_OPTION_CONTROL_MAILBOX"), 0, Array("selectbox", $arMailboxes)),
		Array("note"=>Loc::getMessage("VUTM24_OPTION_CONTROL_MAILBOX_NOTE")),
		Array("AFTER_CREATE_MOVE_TO_FOLDER", Loc::getMessage("VUTM24_OPTION_AFTER_CREATE_MOVE_TO_FOLDER"), 0, Array("selectbox", $arMailboxDirs)),
		Array("CREATE_TASK_ON_SUBJECT", Loc::getMessage("VUTM24_OPTION_CREATE_TASK_ON_SUBJECT"), "", Array("text", 30)),
		Array("note"=>Loc::getMessage("VUTM24_OPTION_CREATE_TASK_ON_SUBJECT_NOTE")),
		Array("CREATE_TASK_CREATOR", Loc::getMessage("VUTM24_OPTION_CREATE_TASK_CREATOR"), 0, Array("selectbox", $arCompetitors)),
		Array("CREATE_TASK_IN_GROUP", Loc::getMessage("VUTM24_OPTION_CREATE_TASK_IN_GROUP"), 0, Array("selectbox", $arGroups)),
		Array("note"=>Loc::getMessage("VUTM24_OPTION_CREATE_TASK_IN_GROUP_NOTE")),
		Array("CREATE_TASK_RESPONSIBLE", Loc::getMessage("VUTM24_OPTION_CREATE_TASK_RESPONSIBLE"), 0, Array("selectbox", $arResponsible)),
		Array("CREATE_TASK_SET_OBSERVERS_FROM_GROUP", Loc::getMessage("VUTM24_OPTION_CREATE_TASK_SET_OBSERVERS_FROM_GROUP"), 'Y', array('checkbox', true)),
		Array("CREATE_TASK_ONCLOSE_RESPONSE", Loc::getMessage("VUTM24_OPTION_CREATE_TASK_ONCLOSE_RESPONSE"), 'Y', array('checkbox', true)),
	),
	'outterb24' => array(
		Array("BITRIX24_TASKS_REST_URL", Loc::getMessage("VUTM24_OPTION_BITRIX24_TASKS_REST_URL"), "", Array("text", 100)),
		Array("note"=>Loc::getMessage("VUTM24_OPTION_BITRIX24_TASKS_REST_URL_NOTE")),
		Array("BITRIX24_CREATE_TASK", Loc::getMessage("VUTM24_OPTION_BITRIX24_CREATE_TASK"), 'Y', array('checkbox', true)),
		Array("BITRIX24_TASKS_IN_GROUP", Loc::getMessage("VUTM24_OPTION_BITRIX24_TASKS_IN_GROUP"), 0, Array("selectbox", $arOutterGroups)),
		Array("BITRIX24_CREATE_LEAD", Loc::getMessage("VUTM24_OPTION_BITRIX24_CREATE_LEAD"), 'Y', array('checkbox', true)),
	),
);

$tabControl = new CAdminTabControl('tabControl', $tabs);

if (check_bitrix_sessid() && strlen($_POST['save']) > 0) {
	foreach ($options as $option) {
		__AdmSettingsSaveOptions($module_id, $option);
	}
	
	ModuleHelper::clearCache();
	
	LocalRedirect($APPLICATION->GetCurPage()."?mid=".urlencode($mid)."&lang=".LANGUAGE_ID."&".$tabControl->ActiveTabParam());
}



?>
<form method="POST"
	action="<? echo $APPLICATION->GetCurPage() ?>?mid=<?= htmlspecialcharsbx($mid) ?>&lang=<?= LANGUAGE_ID ?>">
	<? 
		$tabControl->Begin();
		$tabControl->BeginNextTab();
	?>
	<? __AdmSettingsDrawList($module_id, $options['general']); ?>
	<?
	$tabControl->BeginNextTab();
	?>
	<? __AdmSettingsDrawList($module_id, $options['outterb24']); ?>
	<? $tabControl->Buttons(array('btnApply' => false, 'btnCancel' => false, 'btnSaveAndAdd' => false)); ?>
	<?= bitrix_sessid_post(); ?>
	<? $tabControl->End(); ?>
</form>