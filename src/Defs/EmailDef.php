<?php
namespace Mattioli\EmailReader\Defs;

use ForceUTF8\Encoding;

/**
 * @author Guilherme Mattioli
 * @version 1.0
 */
class EmailDef implements \JsonSerializable {
	public static function get_types() {
		$oClass = new \ReflectionClass(get_called_class());
        return $oClass->getConstants();
	}

	public static function __utf8_Converter(&$input) {
		if (is_string($input)) {
			$enc = new Encoding();
			$input = preg_replace(
				"/\&([a-z]{1})crase;/i", "&$1grave;", $input
			);
			$input = html_entity_decode($input);
			$input = $enc->encode("UTF-8", $enc->toLatin1($input));
		} elseif ($input and (is_array($input) or is_object($input))) {
			foreach ($input as &$value) {
				self::__utf8_Converter($value);
			}
		}
	}

	public function jsonSerialize() {
		$dados = [];
		if ($types = $this->get_types()) foreach ($types as $key => $value) {
			$dados[$key] = $value;
		}

		$oClass = new \ReflectionClass(get_called_class());
		$props = $oClass->getProperties(
			\ReflectionProperty::IS_PUBLIC
		// |	\ReflectionProperty::IS_PROTECTED
		);

		if ($props) foreach ($props as $prop) {
			$dados[$prop->name] = $this->{$prop->name};
		}

		self::__utf8_Converter($dados);
		return $dados;
	}
}