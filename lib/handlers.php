<?
#####################################################
# Bitrix: Module TaskMail24                         #
# Copyright (c) 2020 D.Starovoytov (VseUchteno)     #
# mailto:denis@starovoytov.online                   #
#####################################################


namespace Vseuchteno\Taskmail24;

use Bitrix\Main\Loader;
use Vseuchteno\Taskmail24\B24task;
use Vseuchteno\Taskmail24\Mail2taskTable;
use Vseuchteno\Taskmail24\ModuleHelper;

class Handlers
{
	static $module_id = 'vseuchteno.taskmail24';

        function OnTaskUpdateHandler($ID, &$arFields, $arTaskCopy) {
            
            $CREATE_TASK_ONCLOSE_RESPONSE=ModuleHelper::getModuleOption('CREATE_TASK_ONCLOSE_RESPONSE');
            if ($CREATE_TASK_ONCLOSE_RESPONSE !== 'Y') {
                return false;
            }
            $previusFields=$arFields['META:PREV_FIELDS'];
            if ($previusFields['STATUS'] != 5)  {
	
                /*Закрыта*/
                 if ($arFields['STATUS'] == 5) {
                        $obB24task= new B24task();
                        $arTask=$obB24task->getTaskById($ID);
                        
                        
                        $resultM2T = \Vseuchteno\Taskmail24\Mail2taskTable::getList(array(
                            'select' => ['*', 'MESSAGE'],
                            'filter' => array('=TASK_ID' => $ID)
                        ));
                        $rowM2T = $resultM2T->fetch();
                        
                        $arEventFields = array(
                            "EMAIL_TO"      => $rowM2T['VSEUCHTENO_TASKMAIL24_MAIL2TASK_MESSAGE_FIELD_REPLY_TO'],
                            "SUBJECT"       => "Re: ".$arTask['TITLE'],
                            "MESSAGE"       => $arTask['DESCRIPTION'],
                            "TASK_ID"       => $ID,  
                            "TASK_DATE"     => $arTask['CREATED_DATE']->format("d.m.Y"),  
                            "TASK_TITLE"     => $arTask['TITLE'],  
                        );
                        \CEvent::SendImmediate("TASKMAIL_TASK_CLOSE", SITE_ID, $arEventFields);

                }
            }

        }


}
