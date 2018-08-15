<?php
namespace Mattioli\EmailReader\Defs;

use Mattioli\EmailReader\Defs\EmailDef;

/**
 * @author Guilherme Mattioli
 * @version 1.0
 */
class EncodeType extends EmailDef {
	const NONE = null;
	const BASE64 = 3;
	const PLAIN = 4;
}