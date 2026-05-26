<?php
/**
 * logout.php — Terminar sessão
 */

require_once __DIR__ . '/session.php';

iniciar_sessao();
$_SESSION = [];
session_destroy();

header('Location: ../index.html');
exit;
