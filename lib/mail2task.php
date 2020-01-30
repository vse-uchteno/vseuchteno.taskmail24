<?php
#####################################################
# Bitrix: Module TaskMail24                         #
# Copyright (c) 2020 D.Starovoytov (VseUchteno)     #
# mailto:denis@starovoytov.online                   #
#####################################################


namespace Vseuchteno\Taskmail24;

use Bitrix\Main;
use Bitrix\Main\DB\SqlExpression;
use Bitrix\Main\Entity;
use Bitrix\Main\Entity\ReferenceField;
use Bitrix\Main\Loader;
use Bitrix\Mail;
use Bitrix\Tasks;

Loader::includeModule('mail');
Loader::includeModule('tasks');


class Mail2taskTable extends Main\ORM\Data\DataManager
{
	public static function getTableName()
	{
		return 'b_vu_taskmail_mail_to_task';
	}

	public static function getMap()
	{

		$map = array(
			'MESSAGE_ID' => array(
				'data_type' => 'integer',
				'primary' => true
			),
			'MESSAGE_BY' => array(
				'data_type' => 'MailMessage',
				'reference' => array('=this.MESSAGE_ID' => 'ref.ID')
			),
			new ReferenceField(
				'MESSAGE',
				\Bitrix\Mail\MailMessageTable::getEntity(),
				array('=this.MESSAGE_ID' => 'ref.ID')
			),
			'TASK_ID' => array(
				'data_type' => 'integer',
				'primary' => true
			),
			'TASK_BY' => array(
				'data_type' => 'Task',
				'reference' => array('=this.TASK_ID' => 'ref.ID')
			),
			new ReferenceField(
				'TASK',
				\Bitrix\Tasks\TaskTable::getEntity(),
				array('=this.TASK_ID' => 'ref.ID')
			),
			
		);

		return $map;
	}
}
