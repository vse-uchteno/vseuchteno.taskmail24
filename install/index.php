<?
#####################################################
# Bitrix: Module TaskMail24                         #
# Copyright (c) 2020 D.Starovoytov (VseUchteno)     #
# mailto:denis@starovoytov.online                   #
#####################################################


use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Config\Option;
use Bitrix\Main\EventManager;

Loc::loadMessages(__FILE__);

Class vseuchteno_taskmail24 extends CModule
{
	var $MODULE_ID = "vseuchteno.taskmail24";
	var $MODULE_VERSION;
	var $MODULE_VERSION_DATE;
	var $MODULE_NAME;
	var $MODULE_DESCRIPTION;
	var $MODULE_CSS;
	var $MODULE_INSTALL_PATH;
	
	function vseuchteno_taskmail24()
	{
		$arModuleVersion = array();

		$path = str_replace("\\", "/", __FILE__);
		$path = substr($path, 0, strlen($path) - strlen("/index.php"));
		include($path."/version.php");
		
		if (is_array($arModuleVersion) && array_key_exists("VERSION", $arModuleVersion))
		{
			$this->MODULE_VERSION = $arModuleVersion["VERSION"];
			$this->MODULE_VERSION_DATE = $arModuleVersion["VERSION_DATE"];
		}

		$this->MODULE_NAME = Loc::getMessage("VUTM24_MODULE_NAME");
		$this->MODULE_DESCRIPTION = Loc::getMessage("VUTM24_MODULE_DESCRIPTION");
		$this->PARTNER_NAME = Loc::getMessage("VUTM24_PARTNER_NAME");
		$this->PARTNER_URI = Loc::getMessage("VUTM24_PARTNER_URI");
		$this->MODULE_INSTALL_PATH = $path;
	}

	function InstallDb()
	{
		global $DB, $DBType, $APPLICATION;
		$this->errors = false;

		// Database tables creation
		if(!$DB->Query("SELECT 'x' FROM b_vutm_mail2task WHERE 1=0", true))
		{
			$this->errors = $DB->RunSQLBatch($this->MODULE_INSTALL_PATH."/db/".strtolower($DB->type)."/install.sql");
		}


		// agents
		\CAgent::addAgent(
			'Vseuchteno\Taskmail24\Agents\Processemail::do();',
			$this->MODULE_ID,
			'N',
			300,
			"",
			"Y",
			""
		);

		\Bitrix\Main\EventManager::getInstance()->registerEventHandler(
                    'tasks',
                    'OnTaskUpdate',
                    $this->MODULE_ID,
                    '\Vseuchteno\Taskmail24\Handlers',
                    'OnTaskUpdateHandler'
		);


	}

	function InstallFiles()
	{
		
		return true;
	}

	function DoInstall()
	{
		global $DOCUMENT_ROOT, $APPLICATION;
		$this->InstallDb();
		$this->InstallFiles();
                $this->InstallEvents();
		RegisterModule($this->MODULE_ID);
	}

	function UnInstallDb()
	{
		global $DB, $DBType, $APPLICATION;

		$this->errors = false;
		$this->errors = $DB->RunSQLBatch($this->MODULE_INSTALL_PATH."/db/".strtolower($DB->type)."/uninstall.sql");

		\Bitrix\Main\EventManager::getInstance()->unRegisterEventHandler( 
                    'tasks',
                    'OnTaskUpdate',
                    $this->MODULE_ID,
                    '\Vseuchteno\Taskmail24\Handlers',
                    'OnTaskUpdateHandler'
		); 

		\CAgent::removeModuleAgents($this->MODULE_ID);
		Option::delete($this->MODULE_ID);

	}

        
        
	function InstallEvents()
	{
		global $DB;
		
                $dbEvent = CEventMessage::GetList($b="ID", $order="ASC", Array("EVENT_NAME" => "TASKMAIL_TASK_CLOSE"));
                if(!($dbEvent->Fetch()))
                {
                    $langs = CLanguage::GetList(($b=""), ($o=""));
                    while($lang = $langs->Fetch())
                    {
                        $lid = $lang["LID"];
                        IncludeModuleLangFile(__FILE__, $lid);

                        $et = new CEventType;
                        $et->Add(array(
                                "LID" => $lid,
                                "EVENT_NAME" => "TASKMAIL_TASK_CLOSE",
                                "NAME" => GetMessage("VUTM24_TASKMAIL_TASK_CLOSE_NAME"),
                                "DESCRIPTION" => GetMessage("VUTM24_TASKMAIL_TASK_CLOSE_DESC"),
                        ));

                        $arSites = array();
                        $sites = CSite::GetList(($b=""), ($o=""), Array("LANGUAGE_ID"=>$lid));
                        while ($site = $sites->Fetch())
                                $arSites[] = $site["LID"];

                        if(count($arSites) > 0) {

                            $emess = new CEventMessage;
                            $emess->Add(array(
                                    "ACTIVE" => "Y",
                                    "EVENT_NAME" => "TASKMAIL_TASK_CLOSE",
                                    "LID" => $arSites,
                                    "EMAIL_FROM" => "#DEFAULT_EMAIL_FROM#",
                                    "EMAIL_TO" => "#EMAIL#",
                                    "BCC" => "#BCC#",
                                    "SUBJECT" => GetMessage("VUTM24_TASKMAIL_TASK_CLOSE_SUBJECT"),
                                    "MESSAGE" => GetMessage("VUTM24_TASKMAIL_TASK_CLOSE_MESSAGE"),
                                    "BODY_TYPE" => "text",
                            ));

                        }
                    }
                }
                
		return true;
	}

	function UnInstallEvents()
	{
		global $DB;

		$eventType = new CEventType;
		$eventM = new CEventMessage;
                $eventType->Delete("TASKMAIL_TASK_CLOSE");
                $dbEvent = CEventMessage::GetList($b="ID", $order="ASC", Array("EVENT_NAME" => "TASKMAIL_TASK_CLOSE"));
                while($arEvent = $dbEvent->Fetch())
                {
                        $eventM->Delete($arEvent["ID"]);
                }

		return true;
	}

        
	function UnInstallFiles()
	{
		return true;
	}

	function DoUninstall()
	{
		global $DOCUMENT_ROOT, $APPLICATION;
		$this->UnInstallFiles();
		$this->UnInstallDb();
                $this->UnInstallEvents();
		UnRegisterModule($this->MODULE_ID);
	}
}
?>