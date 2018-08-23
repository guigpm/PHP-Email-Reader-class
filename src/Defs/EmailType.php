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
	const IMAP = 'imap';
	const IMAP_SSL = 'imap/ssl';
	const NNTP = 'nntp';
	const NNTP_SSL = 'nntp/ssl';

	const VALIDATE = '/validate-cert';
	const NOVALIDATE = '/novalidate-cert';
	const SSL = '/ssl';
	const NORSH = '/norsh';
	const TLS = '/tls';
	const NOTLS = '/notls';
	const ANONYMOUS = '/anonymous';
	const READONLY = '/readonly';
	const DEBUG = '/debug';
}