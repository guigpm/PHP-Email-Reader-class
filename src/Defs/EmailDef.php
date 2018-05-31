<?php
namespace Mattioli\EmailReader\Def;

/**
 * @author Guilherme Mattioli
 * @version 1.0
 */
class EmailDef {
	public static function get_types() {
		$oClass = new \ReflectionClass(get_called_class());
        return $oClass->getConstants();
	}
}