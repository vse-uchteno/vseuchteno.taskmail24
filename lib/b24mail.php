<?
#####################################################
# Bitrix: Module TaskMail24                         #
# Copyright (c) 2020 D.Starovoytov (VseUchteno)     #
# mailto:denis@starovoytov.online                   #
#####################################################


namespace Vseuchteno\Taskmail24;

use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Mail;
use Vseuchteno\Taskmail24\ModuleHelper;

Loader::includeModule('mail');

/**
 * Класс-обертка для работы с почтой в Битрикс24 (модуль mail)
 * @package B24mail
 */class B24mail
{
	public function __construct(array $arParams = array())
	{
	    
	}

	public function getAllMailboxes() {
		$arMailboxes = \Bitrix\Mail\MailboxTable::getList()->fetchAll();
		return $arMailboxes;
	}
		
	public function getMailboxById($mailbox_id) {
		return \Bitrix\Mail\MailboxTable::getById($mailbox_id)->fetch();
	}
		
	public function getMailboxFolders($mailbox_id) {
		$arMailbox=$this->getMailboxById($mailbox_id);
		return $arMailbox['OPTIONS']['imap']['dirs']; 
	}
		
	/**
	 * Получение списка сообщений в ящике
	 * 
	 * @param integer $mailbox_id - ID почтового ящика
	 * @param string $keyPhrase - фраза в SUBJECT по которой идет отбор писем для создания задач (указывается в настройках модуля)
	 * @param boolean $new_message_only - выбирать только письма с признаком НЕ ПРОЧИТАННОЕ
	 * 
	 * @return array - массив отобранных писем
	 */
	public function getMessageslist($mailbox_id, $keyPhrase="", $new_message_only=true) {
                
		$filter=[];
		$filter['MAILBOX_ID'] = $mailbox_id;
		
		if ($new_message_only) {
			$filter['NEW_MESSAGE'] = "Y";
		}
		
		if ($keyPhrase != "") {
			$filter['%=SUBJECT'] = "%".$keyPhrase."%";
		}
		
		$arMessages=\Bitrix\Mail\MailMessageTable::getList(['select' => ['*'], 'filter' => $filter])->fetchAll();
		
		return $arMessages;
	}
		
	public function setMessageAsReaded($message_id) {
            \CMailMessage::Update($message_id, Array("NEW_MESSAGE"=>"N"));
            $obMmu=new \Bitrix\Mail\MailMessageUidTable();
            $obMmu->updateList(["MESSAGE_ID" => $message_id], ['IS_SEEN' => 'Y']);
	}

		
	public function moveMessageToFolder($message_id, $folder) {
            $obMmu=new \Bitrix\Mail\MailMessageUidTable();
            $obMmu->updateList(["MESSAGE_ID" => $message_id], ['DIR_MD5' => md5($folder)]);
	}        
	
}