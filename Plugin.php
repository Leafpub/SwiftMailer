<?php
/**
 * Leafpub: Simple, beautiful publishing. (https://leafpub.org)
 *
 * @link      https://github.com/Leafpub/leafpub
 * @copyright Copyright (c) 2016 Leafpub Team
 * @license   https://github.com/Leafpub/leafpub/blob/master/LICENSE.md (GPL License)
 */

namespace Leafpub\Plugins\SwiftMailer;

use Leafpub\Leafpub,
    Leafpub\Plugin\APlugin,
    Leafpub\Mailer;

class Plugin extends APlugin {
    const NAME = 'SwiftMailer';
    /**
    * Construct our plugin
    *
    * Add the SwiftMailer to the possible mailers and add a menu icon
    *
    * @param \Slim\App $app
    * @return void
    *
    */
    public function __construct($app){
        parent::__construct($app);
        Mailer::addMailer('SwiftMailer', 'Leafpub\\Plugins\\SwiftMailer\\SwiftMailer');

        Leafpub::on('navigation.admin', function($event){
            $menu = $event->getEventData();
            $menu[] = [
                'title' => 'SwiftMailer',
                'link' => $this->url('options'),
                'icon' => 'fa fa-envelope'
            ];
            $event->setEventData($menu);
        });
    }

    /**
    * Set a standard transport
    *
    */
    public static function afterActivation(){
        self::setSetting('SwiftMailer.transport', 'sendmail');
    }

    /**
    * Set the standard mailer
    *
    */
    public static function afterDeActivation(){
        $mailer = self::getSetting('mailer');
        if ($mailer === 'SwiftMailer'){
            self::setSetting('mailer', 'default');
        }
    }

    /**
    * Show the options
    *
    * @param $req
    * @param $res
    * @param $args
    *
    */
    public static function showOptions($req, $res, $args){
        $swiftSettings = self::getSwiftSettings();
        
        $html = self::render('options', [
            'title' => 'Swift Mailer options',
            'plugin_scripts' => [
                self::NAME => '/scripts/options.js'
            ],
            'transports' => [
                'mail',
                'sendmail',
                'smtp'
            ],
            'actualTransport' => self::getSetting('SwiftMailer.transport'),
            'SwiftOptions' => self::getSwiftSettings()
        ], self::NAME);

        return $res->write($html);
    }

    /**
    * Save the options
    *
    * @param $req
    * @param $res
    * @param $args
    *
    */
    public static function saveOptions($req, $res, $args){
        $params = $req->getParams();

        $transport = $params['SwiftMailer_transport'];
        unset($params['SwiftMailer_transport']);
        self::setSetting('SwiftMailer.transport', $transport);

        if ($transport === 'smtp'){
            if ($params['SwiftMailer_host'] === null){
                $invalid[] = 'SwiftMailer.host';
            } elseif (!filter_var($params['SwiftMailer_host'], FILTER_VALIDATE_URL)){
                $invalid[] = 'SwiftMailer.host';
            }

            if ($params['SwiftMailer_port'] === null){
                $invalid[] = 'SwiftMailer.port';
            } 

            return $res->withJson([
                'success' => false,
                'invalid' => $invalid,
                'message' => 'Fields need to be set'
            ]);

            foreach($params as $key => $val){
                if ($val !== null){
                    self::setSetting(str_replace('_', '.', $key), $val);
                }
            }
        }

         // Send response
        return $res->withJson([
            'success' => true,
        ]);
    }

    /**
    * Get the SwiftMailer saveOptions
    *
    * @return array
    *
    */
    public static function getSwiftSettings(){
        return [
            'SwiftMailer.transport' => self::getSetting('SwiftMailer.transport'),
            'SwiftMailer.host' => self::getSetting('SwiftMailer.host'),
            'SwiftMailer.port' => self::getSetting('SwiftMailer.port'), 
            'SwiftMailer.user' => self::getSetting('SwiftMailer.user'),
            'SwiftMailer.pass' => self::getSetting('SwiftMailer.pass'),
            'SwiftMailer.encryption' => self::getSetting('SwiftMailer.encryption')
        ];
    }
}