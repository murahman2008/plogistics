<?php

function startSession()
{
	if(session_status() == PHP_SESSION_NONE)
		session_start();
}

function destroySession()
{
	session_destroy();
}

function getSessionData($key)
{
	return (isset($_SESSION[$key]) ? $_SESSION[$key] : false);
}

function setSessionData($key, $value)
{
	clearSessionData($key);
	$_SESSION[$key] = $value;	
}

function clearSessionData($key)
{
	unset($_SESSION[$key]);
}

startSession();