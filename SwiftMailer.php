<?php
/**
 * Leafpub: Simple, beautiful publishing. (https://leafpub.org)
 *
 * @link      https://github.com/Leafpub/leafpub
 * @copyright Copyright (c) 2016 Leafpub Team
 * @license   https://github.com/Leafpub/leafpub/blob/master/LICENSE.md (GPL License)
 */

namespace Leafpub\Plugins\SwiftMailer;

require __DIR__ . '/lib/swift_required.php';

use Leafpub\Leafpub,
    Leafpub\Mailer\Bridge\MailerInterface;


class SwiftMailer implements MailerInterface {
    
     public function send(\Leafpub\Mailer\Mail\Mail $mail){
         
         try {
            $message = \Swift_Message::newInstance();

            $message->setSubject($mail->subject);
            $message->setBody($mail->message);
            $message->setTo($mail->to->getEmail());
            $message->setFrom($mail->from->getEmail());

            $transport = $this->getTransport();
            $mailer = \Swift_Mailer::newInstance($transport);

            return $mailer->send($message);
         } catch (Exception $e){
             return false;
         }
     }

     private function getTransport(){
         $options = Plugin::getSwiftOptions();
         $transport = null;

         switch ($options['SwiftMailer.transport']){
             case 'smtp':
                $transport = \Swift_SmtpTransport::newInstance();
                $transport->setHost($options['SwiftMailer.host']);
                $transport->setPort($options['SwiftMailer.port']);
                $transport->setUser($options['SwiftMailer.user']);
                $transport->setPass($options['SwiftMailer.pass']);
                if ($options['SwiftMailer.encryption'] !== null){
                    $transport->setEncryption($options['SwiftMailer.encryption']);
                }
                break;
            case 'sendmail':
                $transport = \Swift_SendmailTransport::newInstance();
                break;
            case 'mail':
                $transport = \Swift_MailTransport::newInstance();
                break;
         }
         // Check Settings table for options
         return $transport;
     }
}