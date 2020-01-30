<?
#####################################################
# Bitrix: Module TaskMail24                         #
# Copyright (c) 2020 D.Starovoytov (VseUchteno)     #
# mailto:denis@starovoytov.online                   #
#####################################################


namespace Vseuchteno\Taskmail24;

use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Vseuchteno\Taskmail24\ModuleHelper;

Loader::includeModule('tasks');
Loader::includeModule('socialnetwork');
Loader::includeModule('intranet');

/**
 * Класс-обертка для работы с задачами, группами и структурой в Битрикс24 (модули tasks, socialnetwork и intranet)
 * @package B24task
 */
class B24task
{
	
	protected static $allowedUserFields = array(
		"ID", "NAME", "LAST_NAME", "SECOND_NAME", "WORK_COMPANY", "WORK_POSITION", "WORK_PHONE", "UF_DEPARTMENT", "UF_INTERESTS", "UF_SKILLS", 
		"UF_WEB_SITES", "UF_XING", "UF_LINKEDIN", "UF_FACEBOOK", "UF_TWITTER", "UF_SKYPE", "UF_DISTRICT", "UF_PHONE_INNER"
	);
	
	public function __construct(array $arParams = array())
	{
	    
	}
	
	/**
	 * Получить список всех сотрудников системы
	 * @param type $active_only только активные
	 * 
	 * @return array
	 */
	public function getAllCompetitors($active_only=true) {
		
		$result=[];
		
		$arFilter=[];
		if ($active_only) {
			$arFilter['ACTIVE']='Y';
		}
		$dbRes = \Bitrix\Intranet\UserTable::getList(array(
				'filter' => $arFilter,
				'select' => static::$allowedUserFields,
		));
		while ($arRes = $dbRes->fetch())
		{
			$user_id=$arRes['ID'];
			$result[$user_id]['ID'] = $arRes['ID'];
			$result[$user_id]['FIO'] = trim($arRes['LAST_NAME']." ".$arRes['NAME']);
			$result[$user_id]['POSITION'] = $arRes['WORK_POSITION'];
			$result[$user_id]['TITLE'] = $result[$user_id]['FIO']." (".$arRes['WORK_POSITION'].")";
		}
		
		return $result;
	}

	public function getAllGroups() {
		$arGroups = \Bitrix\Socialnetwork\WorkgroupTable::getList()->fetchAll();
		return $arGroups;
	}

	/**
	 * Получить всех пользователей из Группы (Проекта)
	 * 
	 * @param type $group_id
	 * @return type
	 */
	public function getAllUsersByGroup($group_id) {
		$result=[];

		$dbRequests = \CSocNetUserToGroup::GetList(
			array("USER_LAST_NAME" => "ASC", "USER_NAME" => "ASC"),
			array(
				"GROUP_ID" => $group_id,
				"USER_ACTIVE" => "Y"
			),
			false,
			array(),
			array("ID", "USER_ID", "DATE_CREATE", "DATE_UPDATE", "USER_NAME", "USER_LAST_NAME", "ROLE")
		);
		while ($arRequests = $dbRequests->GetNext())
		{
                    
			$result[]=[
				'ID' => $arRequests['USER_ID'],
				'TITLE' => trim($arRequests['USER_LAST_NAME']." ".$arRequests['USER_NAME']),
			];
		}
		return $result;
	}
	
	
	public function createTask($params=[]) {
		
		$task = new \Bitrix\Tasks\Item\Task();
		foreach($params as $paramName=>$paramValue) {
			$task[$paramName]=$paramValue;
		}
		
		$result = $task->save();
		if($result->isSuccess())
		{
			return $task->getId();
		}
		else
		{
		   return false;
		}
	}

	
	public function getTaskById($id) {
		
		$task = new \Bitrix\Tasks\Item\Task($id);
		return $task->getData();
	}
        
	
}