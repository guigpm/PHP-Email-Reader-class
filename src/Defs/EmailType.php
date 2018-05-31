<?php
namespace Mattioli\EmailReader\Def;

use Mattioli\EmailReader\Def\EmailDef;

/**
 * @author Guilherme Mattioli
 * @version 1.0
 */
class EmailType extends EmailDef {
	const NONE = null;
	const POP = 'pop3';
	const POP_SSL = 'pop3/ssl';
	const IMAP = 'imap';
	const IMAP_SSL = 'imap/ssl';
}