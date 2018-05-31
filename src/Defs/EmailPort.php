<?php
namespace Mattioli\EmailReader\Def;

use Mattioli\EmailReader\Def\EmailDef;

/**
 * @author Guilherme Mattioli
 * @version 1.0
 */
class EmailPort extends EmailDef {
	const NONE = null;
	const POP = 110;
	const POP_SSL = 995;
	const IMAP_SSL = 993;
	const SMTP_SSL = 465;
	const SMTP_TLS = 587;
}