<?php

namespace StarterKit;

class Email
{
	public $cfg;
	public static $instance = null;
	private $twig = null;
	protected function __construct($config)
	{
		$this->cfg = $config;
	}
	
	public static function getInstance($config = false)
	{
		return (is_null(self::$instance) ? self::$instance = new self($config) : self::$instance);
	}
	
	public function create_html($template,$args){
		if(is_null($this->twig)){
			$this->twig = new \Twig_Environment( new \Twig_Loader_Filesystem( $this->cfg['template_path'] ) );
		}
		return $this->twig->loadTemplate($template)->render($args);
	}
	
	public function send($html,$subject,$recipient,$replyto = false)
	{
		$transport = \Swift_SmtpTransport::newInstance($this->cfg['host'], $this->cfg['port']);
		$transport->setUsername($this->cfg['user']);
		$transport->setPassword($this->cfg['pass']);
		$swift = \Swift_Mailer::newInstance($transport);
		$message = new \Swift_Message($subject);
		$message->setFrom($this->cfg['send_from']);
		$message->setBody($html, 'text/html');
		$message->setTo($recipient);
		if($replyto){
			$message->setReplyTo($replyto); 
		}
		
		if ($recipients = $swift->send($message, $failures))
		{
			return true;
		} else {
			Throw new \exception('Unable to send email');
		}
	}
}