<?php
namespace Mattioli\EmailReader\Defs;

use Mattioli\EmailReader\Defs\EmailDef;

/**
 * @author Guilherme Mattioli
 * @version 1.0
 */
class EmailPort extends EmailDef {
	const NONE = null;
	const POP = 110;
	const POP_SSL = 995;
	const IMAP = 143;
	const IMAP_SSL = 993;
	const NNTP = 119;
	const SMTP_SSL = 465;
	const SMTP_TLS = 587;
}