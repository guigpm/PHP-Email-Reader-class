<?php
namespace Mattioli\EmailReader\Defs;

use Mattioli\EmailReader\Defs\EmailDef;

/**
 * @author Guilherme Mattioli
 * @version 1.0
 */
class EmailType extends EmailDef {
	const NONE = null;
	const POP = 'pop3';
	const POP_SSL = 'pop3/ssl';
	const POP_SSL_NOVALIDATE = 'pop3/ssl/novalidate-cert';
	const IMAP = 'imap';
	const IMAP_SSL = 'imap/ssl';
	const IMAP_SSL_NOVALIDATE = 'imap/ssl/novalidate-cert';
}