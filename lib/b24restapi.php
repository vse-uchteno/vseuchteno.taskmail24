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
use Bitrix\Main\Web\HttpClient;



class B24RestApi
{
	protected static $url;

	public function __construct(array $arParams = array())
	{
	    self::$url = ModuleHelper::getModuleOption('BITRIX24_TASKS_REST_URL');
	}
	

	public function addTask($fields)
	{
                $ownerId=$this->getSonetGroupOwner($fields['GROUP_ID']);
                $fields['RESPONSIBLE_ID'] = $ownerId;
		$arParams = array(
		   'fields' => $fields,
		);
                
		$result = self::restCommand('tasks.task.add', $arParams);
		if($result['result'])
			return $result['result'];
	}
        
	public function addLead($fields)
	{
		$arParams = array(
		   'fields' => $fields,
		);
		$result = self::restCommand('crm.lead.add', $arParams);
		if($result['result'])
			return $result['result'];
	}


	public function getSonetGroupOwner($groupId)
	{
                $result=1; /*Заглушка чтобы назначался главный Администратор*/
		$arParams = ["ID" => $groupId];
		$result = self::restCommand('sonet_group.user.get', $arParams);
		if(!empty($result['result'])) {
                    foreach ($result['result'] as $groupUser) {
                        if ($groupUser['ROLE'] == 'A') {
                            $result = $groupUser['USER_ID'];
                            break;
                        }
                    }
                }
                
                return $result;
        }

	public function getAllSonetGroups()
	{
		$arResult = array();
		$arParams = array();
		$result = self::restCommand('sonet_group.get', $arParams);
		if(!empty($result['result'])) {
                    $arResult = $result['result'];
                }
                
                return $arResult;
        }


	protected static function restCommand($method, array $params = array())
	{
		$httpClient = new HttpClient();
		$httpClient->setHeader('Content-Type', 'application/json');
		$httpClient->setHeader('Accept', 'application/json');
		$jsonRequestValues = json_encode($params);
		$jsonResponse = $httpClient->post(self::$url . $method, $jsonRequestValues);
		return json_decode($jsonResponse, true);
	}

}