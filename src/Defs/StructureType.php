<?php
namespace Mattioli\EmailReader\Defs;

use Mattioli\EmailReader\Defs\EmailDef;

/**
 * @author Guilherme Mattioli
 * @version 1.0
 */
class StructureType extends EmailDef {
	const NONE = null;
	const HTML = 'HTML';
	const TEXT = 'PLAIN';
	const ALTERNATIVE = 'ALTERNATIVE';
	const MULTIPART = 'MULTIPART';
}