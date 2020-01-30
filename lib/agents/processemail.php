<?
#####################################################
# Bitrix: Module TaskMail24                         #
# Copyright (c) 2020 D.Starovoytov (VseUchteno)     #
# mailto:denis@starovoytov.online                   #
#####################################################

namespace Vseuchteno\Taskmail24\Agents;

use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Vseuchteno\Taskmail24\MailProcessor;
use Vseuchteno\Taskmail24\ModuleHelper;


class Processemail
{
    public static function do()
    {
        $ob = new MailProcessor();
        $ob->process();

        return 'Vseuchteno\Taskmail24\Agents\Processemail::do();';
    }
}