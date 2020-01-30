<?
#####################################################
# Bitrix: Module TaskMail24                         #
# Copyright (c) 2020 D.Starovoytov (VseUchteno)     #
# mailto:denis@starovoytov.online                   #
#####################################################


namespace Vseuchteno\Taskmail24;

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Loader;

Class ModuleHelper
{
	protected static $moduleId = "vseuchteno.taskmail24";
	protected static $ttlCache = 604800; 

	public static function getModuleOptions()
	{
            $obCache = new \CPHPCache();
            $cacheTime = self::$ttlCache;
            $cacheId = 'Options' . self::$moduleId;
            $cachePath = self::$moduleId;

            if($obCache->InitCache($cacheTime, $cacheId, $cachePath))
            {
                    $vars = $obCache->GetVars();
                    $result = $vars['result'];
            }
            elseif($obCache->StartDataCache())
            {
                    $result = Option::getForModule(self::$moduleId);
                    $obCache->EndDataCache(array('result' => $result));
            }
            return $result;
	}

        
	public static function getModuleOption($optionId)
	{
                $moduleOptions=ModuleHelper::getModuleOptions();
		return (isset($moduleOptions[$optionId])?$moduleOptions[$optionId]:"");
	}

	
	public static function clearCache()
	{
		$obCache = new \CPHPCache();
		$cacheId = 'Options' . self::$moduleId;
		$cachePath = self::$moduleId;
		$obCache->Clean($cacheId, $cachePath);
	}

	/**
	 * Преобразует сырой массив в массив параметров для "selectbox" COption
	 * 
	 * @param array $raw_array
	 * @param string|boolean $field_name_for_id
	 * @param string|boolean $field_name_for_title
	 * 
	 * @return array
	 */
	public static function prepareArrayForOption($raw_array,$field_name_for_id = 'ID', $field_name_for_title = 'TITLE')
	{
		
		$result=[];
                if (empty($raw_array))	return $result;

		foreach($raw_array as $arRowIndex => $arRow) {
			
			if (($field_name_for_id === false) && ($field_name_for_title === false) ) {
				$result[$arRowIndex] = reset($arRow);		
			}
			
			if (isset($arRow[$field_name_for_id])) {
				$result[$arRow[$field_name_for_id]] = (isset($arRow[$field_name_for_title])?$arRow[$field_name_for_title]:"");		
			}
		}
		return $result;
	}
	
}