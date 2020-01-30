<?
#####################################################
# Bitrix: Module TaskMail24                         #
# Copyright (c) 2020 D.Starovoytov (VseUchteno)     #
# mailto:denis@starovoytov.online                   #
#####################################################


namespace Vseuchteno\Taskmail24;

use Bitrix\Main\Localization\Loc;
use Vseuchteno\Taskmail24\ModuleHelper;
use Vseuchteno\Taskmail24\B24mail;
use Vseuchteno\Taskmail24\B24task;
use Vseuchteno\Taskmail24\B24RestApi;


Loc::loadMessages(__FILE__);

/**
 * Основная рутина для обработки почты
 * @package MailProcessor
 */
class MailProcessor {
	
	private $moduleOptions=[];
	
	public function __construct(array $arParams = array())
	{
	    $this->moduleOptions=ModuleHelper::getModuleOptions();
	}

	public function process() {
		$obB24mail= new B24mail();
		$phrase=$this->moduleOptions['CREATE_TASK_ON_SUBJECT'];
		$mailboxId=$this->moduleOptions['CONTROL_MAILBOX'];
		
		if (($mailboxId == "")||($mailboxId == 0)) {
			exit(Loc::getMessage('VUTM24_ERROR_MAILBOX_NOT_SELECTED'));
		}
		
		$arMessages=$obB24mail->getMessageslist($mailboxId, $phrase);
		echo(count($arMessages));
		print_r($arMessages);
		
		foreach ($arMessages as $arMessage) {
			$obB24task= new B24task();
			
			$arTaskParametrs=[];
			$arTaskParametrs['TITLE']=$arMessage['SUBJECT'];
			$arTaskParametrs['DESCRIPTION']=$arMessage['BODY'];
			
			if (($this->moduleOptions['CREATE_TASK_IN_GROUP'] != "")&&((int)$this->moduleOptions['CREATE_TASK_IN_GROUP'] > 0)) {
				$arTaskParametrs['GROUP_ID']=$this->moduleOptions['CREATE_TASK_IN_GROUP'];
			}
			
			if (($this->moduleOptions['CREATE_TASK_CREATOR'] != "")&&((int)$this->moduleOptions['CREATE_TASK_CREATOR'] > 0)) {
				$arTaskParametrs['CREATED_BY']=$this->moduleOptions['CREATE_TASK_CREATOR'];
			}
			
			if (($this->moduleOptions['CREATE_TASK_RESPONSIBLE'] != "")&&((int)$this->moduleOptions['CREATE_TASK_RESPONSIBLE'] > 0)) {
				$arTaskParametrs['RESPONSIBLE_ID']=$this->moduleOptions['CREATE_TASK_RESPONSIBLE'];
			}
			
			if (($this->moduleOptions['CREATE_TASK_SET_OBSERVERS_FROM_GROUP'] != "")&&($this->moduleOptions['CREATE_TASK_SET_OBSERVERS_FROM_GROUP'] == "Y")) {
				if (isset($arTaskParametrs['GROUP_ID'])) {
					$arUsersOfGroup=$obB24task->getAllUsersByGroup($arTaskParametrs['GROUP_ID']);
					$arAuditors=[];
					foreach ($arUsersOfGroup as $arUser) {
                                                /*Не добавляем в Наблюдатели Постановщика и Ответственного*/
                                                if ($arUser['ID'] == $arTaskParametrs['CREATED_BY']) continue;
                                                if ($arUser['ID'] == $arTaskParametrs['RESPONSIBLE_ID']) continue;
                                            
						$arAuditors[$arUser['ID']]=$arUser['ID'];
					}
					
					$arTaskParametrs['AUDITORS']=$arAuditors;
				}
			}
			
			
			$taskId=$obB24task->createTask($arTaskParametrs);
			
			if ($taskId !== false) {
                            
                            /*Пометим письмо прочитанным*/
                            $obB24mail->setMessageAsReaded($arMessage['ID']);
                            
                            /*Переместим его в другую папку*/
                            if ($this->moduleOptions['AFTER_CREATE_MOVE_TO_FOLDER'] != "") {
                                $obB24mail->moveMessageToFolder($arMessage['ID'],$this->moduleOptions['AFTER_CREATE_MOVE_TO_FOLDER']);
                            }
                            
                            /*Сохраняем связку Задачи с Письмом в отдельной таблице (пригодится)*/
                            $add_result = Mail2taskTable::add([
                                'MESSAGE_ID' => $arMessage['ID'],
                                'TASK_ID' => $taskId,
                            ]);
                            if($add_result->isSuccess())
                            {
                                if ($this->moduleOptions['BITRIX24_TASKS_REST_URL'] != "") {
                                        $obB24RestApi = new B24RestApi();  
                                        
                                        /*Создаем задачу для контроля во внешнем Битрикс24*/
                                        if (($this->moduleOptions['BITRIX24_CREATE_TASK'] != "")&&($this->moduleOptions['BITRIX24_CREATE_TASK'] == "Y")) {
                                            if (($this->moduleOptions['BITRIX24_TASKS_IN_GROUP'] != "")&&((int)$this->moduleOptions['BITRIX24_TASKS_IN_GROUP'] > 0)) {
                                                unset($arTaskParametrs['AUDITORS']);
                                                unset($arTaskParametrs['CREATED_BY']);
                                                unset($arTaskParametrs['RESPONSIBLE_ID']);
                                            
                                                $arTaskParametrs['GROUP_ID']=$this->moduleOptions['BITRIX24_TASKS_IN_GROUP'];
                                                $obB24RestApi->addTask($arTaskParametrs);

                                            }
                                            
                                        }
                                        
                                        /*Создаем Лид во внешнем Битрикс24*/
                                        if (($this->moduleOptions['BITRIX24_CREATE_LEAD'] != "")&&($this->moduleOptions['BITRIX24_CREATE_LEAD'] == "Y")) {
                                            $arLeadParametrs=[];
                                            $arLeadParametrs['TITLE']=$arMessage['SUBJECT'];
                                            $arLeadParametrs['COMMENTS']=$arMessage['BODY_HTML'];
                                            $arLeadParametrs['NAME']=$arMessage['FIELD_FROM'];
                                            $arLeadParametrs['EMAIL'] =  [ 
                                                ["VALUE" => $arMessage['FIELD_REPLY_TO'], "VALUE_TYPE" => "WORK"],
                                            ];
                                            
                                            $obB24RestApi->addLead($arLeadParametrs);
                                        }
                                        
                                }
                                
                                
                            }
			}
			
			unset($CB24task);
		}
	}
	
	
	
}