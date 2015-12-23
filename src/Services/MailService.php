<?php

namespace Services;

class MailService
{

    private $_host;

    private $_port;

    private $_email;

    private $_password;

    private $_transporter;

    private $_mailer;

    public function __construct($host, $port, $email, $password)
    {
        $this->_host = $host;
        $this->_port = $port;
        $this->_email = $email;
        $this->_password = $password;
    }

    public function setup()
    {
        $this->_transporter = \Swift_SmtpTransport::newInstance(EMAIL_HOST, EMAIL_PORT, 'ssl')
            ->setUsername($this->_email)
            ->setPassword($this->_password);
        $this->_createMailer();
    }

    private function _createMailer()
    {
        $this->_mailer = \Swift_Mailer::newInstance($this->_transporter);
    }

    public function sendMail(array $mailOptions = array())
    {
        $message = \Swift_Message::newInstance('Test')
            ->setFrom(array($mailOptions['myEmail']))
            ->setTo($mailOptions['toEmail'])
            ->setSubject('I Browse Smart Purchase Order')
            ->setBody($mailOptions['template'], 'text/html');

        $this->_mailer->send($message);
    }

}