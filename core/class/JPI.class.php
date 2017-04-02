<?php

/* This file is part of Jeedom.
 *
 * Jeedom is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Jeedom is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Jeedom. If not, see <http://www.gnu.org/licenses/>.
 */

/* * ***************************Includes********************************* */
require_once dirname(__FILE__) . '/../../../../core/php/core.inc.php';

class JPI extends eqLogic
{
    
    
    public static function getjpiVoice($ip, $port)
    {
        
        $url     = 'http://' . $ip . ':' . $port . '/?action=getVoices';
        $content = file_get_contents($url);
        $value   = explode(', ', $content);
        log::add('JPI', 'info', 'Langue(s) découverte(s) : ' . $content);
        return $value;
    }
    
    
    
    /*     * *************************Attributs****************************** */
    
    /*
     * Fonction exécutée automatiquement toutes les minutes par Jeedom
     public static function cron() {
    
     }
     */
    
    
    /*
     * Fonction exécutée automatiquement toutes les heures par Jeedom
     public static function cronHourly() {
    
     }
     */
    
    /*
     * Fonction exécutée automatiquement tous les jours par Jeedom
     public static function cronDayly() {
    
     }
     */
    
    
    
    /*     * *********************Méthodes d'instance************************* */
    
    public function preInsert()
    {
        
    }
    
    public function postInsert()
    {
        
    }
    
    public function postSave()
    {
        
    }
    
    public function preUpdate()
    {
        
    }
    
    public function postUpdate()
    {
        
    }
    
    public function preRemove()
    {
        
    }
    
    public function postRemove()
    {
        
    }
    
    /*
     * Non obligatoire mais permet de modifier l'affichage du widget si vous en avez besoin
     public function toHtml($_version = 'dashboard') {
    
     }
     */
    
    /*     * **********************Getteur Setteur*************************** */
}

class JPICmd extends cmd
{
    /*     * *************************Attributs****************************** */
    
    
    /*     * ***********************Methode static*************************** */
    
    
    /*     * *********************Methode d'instance************************* */
    
    /*
     * Non obligatoire permet de demander de ne pas supprimer les commandes même si elles ne sont pas dans la nouvelle configuration de l'équipement envoyé en JS
     public function dontRemoveCmd() {
     return true;
     }
     */
    public function preSave()
    {
        if ($this->getConfiguration('jpiAction') == 'TOAST') {
            $this->setDisplay('message_placeholder', __('Toast', __FILE__));
            $this->setDisplay('title_disable', 1);
        }
        if ($this->getConfiguration('jpiAction') == 'NOTIF') {
            $this->setDisplay('title_placeholder', __('Header', __FILE__));
            $this->setDisplay('message_placeholder', __('Message', __FILE__));
        }
        
        if ($this->getConfiguration('jpiAction') == 'TTS') {
            $this->setDisplay('title_placeholder', __('Broadcast [Activé,Désactivé]', __FILE__));
        }
        if ($this->getConfiguration('jpiAction') == 'SMS') {
            $this->setDisplay('title_disable', 1);
        }
        
    }
    
    public function execute($_options = null)
    {
        $eqLogic = $this->getEqLogic();
        
        switch ($this->getConfiguration('jpiAction')) {
            
            case 'TTS':
                
                if (($_options['title']) == 'Désactivé' || ($_options['title']) == 'désactivé' || ($_options['title']) == '') {
                    $url = 'http://' . $eqLogic->getConfiguration('jpiIp') . ':' . $eqLogic->getConfiguration('jpiPort') . '/?action=tts&message=' . urlencode($_options['message']) . '&volume=' . $this->getConfiguration('jpiVolume') . '&voice=' . $this->getConfiguration('jpiVoice') . '&queue=1&wait=1';
                    log::add('JPI', 'info', 'Commande TTS envoyée au périphérique JPI : ' . $url);
                    $request_http = new com_http($url);
 					$request_http->setNoReportError(true);                                           
                    $request_http->exec(1,1);
                    break;
                } elseif (($_options['title']) == 'Activé' || ($_options['title']) == 'activé') {
                    $eqLogics = eqLogic::byType('JPI');
                    foreach ($eqLogics as $jpidevice) {
                        $ip   = $jpidevice->getConfiguration('jpiIp');
                        $port = $jpidevice->getConfiguration('jpiPort');
                        $url  = 'http://' . $ip . ':' . $port . '/?action=tts&message=' . urlencode($_options['message']) . '&volume=' . $this->getConfiguration('jpiVolume') . '&voice=' . $this->getConfiguration('jpiVoice') . '&queue=1&wait=1';
                        log::add('JPI', 'info', 'Commande TTS BROADCAST envoyée au périphérique JPI : ' . $url);
                        $request_http = new com_http($url);
 						$request_http->setNoReportError(true);                                           
                        $request_http->exec(1,1);
                      
                        
                    }
                    break;
                }
            
                    break;      
                
            case 'SMS':
                if (isset($_options['answer'])) {
                    $_options['message'] .= ' (' . implode(';', $_options['answer']) . ')';
                }
                $values = array();
                if (isset($_options['message']) && $_options['message'] != '') {
                    $message = trim($_options['message']);
                } else {
                    $message = trim($_options['title'] . ' ' . $_options['message']);
                }
                
                $url = 'http://' . $eqLogic->getConfiguration('jpiIp') . ':' . $eqLogic->getConfiguration('jpiPort') . '/?action=sendSms&number=' . $this->getConfiguration('jpiNumsms') . '&message=' . urlencode($message);
                log::add('JPI', 'info', 'Commande SMS envoyée au périphérique JPI : ' . $url);
                $request_http = new com_http($url);
 				$request_http->setNoReportError(true);                                           
                $request_http->exec(1,1);
                break;
            
            case 'CALL':
                $url = 'http://' . $eqLogic->getConfiguration('jpiIp') . ':' . $eqLogic->getConfiguration('jpiPort') . '/?action=makeCall&number=' . $this->getConfiguration('jpiNumsms');
                log::add('JPI', 'info', 'Commande CALL envoyée au périphérique JPI : ' . $url);
                $request_http = new com_http($url);
 				$request_http->setNoReportError(true);                                           
                $request_http->exec(1,1);
                break;
            
            case 'PICTURE':
                $url = 'http://' . $eqLogic->getConfiguration('jpiIp') . ':' . $eqLogic->getConfiguration('jpiPort') . '/?action=picture&camera=' . $this->getConfiguration('jpiPicture') . '&resolution=' . $this->getConfiguration('jpiResolution');
                log::add('JPI', 'info', 'Commande PICTURE envoyée au périphérique JPI : ' . $url);
                $request_http = new com_http($url);
 				$request_http->setNoReportError(true);                                           
                $request_http->exec(1,1);
                break;
            
            case 'APKCHECK':
                $url = 'http://' . $eqLogic->getConfiguration('jpiIp') . ':' . $eqLogic->getConfiguration('jpiPort') . '?action=checkMaj';
                log::add('JPI', 'info', 'Commande APKCHECK envoyée au périphérique JPI : ' . $url);
                $request_http = new com_http($url);
 				$request_http->setNoReportError(true);                                           
                $request_http->exec(1,1);
                break;
            
            case 'APKMAJ':
                $url = 'http://' . $eqLogic->getConfiguration('jpiIp') . ':' . $eqLogic->getConfiguration('jpiPort') . '?action=forceMaj';
                log::add('JPI', 'info', 'Commande APKMAJ envoyée au périphérique JPI : ' . $url);
                $request_http = new com_http($url);
 				$request_http->setNoReportError(true);                                           
                $request_http->exec(1,1);
                break;
            
            case 'SYSREBOOT':
                $url = 'http://' . $eqLogic->getConfiguration('jpiIp') . ':' . $eqLogic->getConfiguration('jpiPort') . '?action=reboot';
                log::add('JPI', 'info', 'Commande SYSREBOOT envoyée au périphérique JPI : ' . $url);
                $request_http = new com_http($url);
 				$request_http->setNoReportError(true);                                           
                $request_http->exec(1,1);
                break;
            
            case 'GEARREBOOT':
                $url = 'http://' . $eqLogic->getConfiguration('jpiIp') . ':' . $eqLogic->getConfiguration('jpiPort') . '?action=restart';
                log::add('JPI', 'info', 'Commande GEARREBOOT envoyée au périphérique JPI : ' . $url);
                $request_http = new com_http($url);
                $request_http->exec(1,1);
                break;
            
            case 'GEARQUIT':
                $url = 'http://' . $eqLogic->getConfiguration('jpiIp') . ':' . $eqLogic->getConfiguration('jpiPort') . '?action=quit';
                log::add('JPI', 'info', 'Commande GEARQUIT envoyée au périphérique JPI : ' . $url);
                $request_http = new com_http($url);
 				$request_http->setNoReportError(true);                                           
                $request_http->exec(1,1);
                break;
            
            case 'PLAY':
                $url = 'http://' . $eqLogic->getConfiguration('jpiIp') . ':' . $eqLogic->getConfiguration('jpiPort') . '?action=play&media=' . $this->getConfiguration('jpiMedia') . '&volume=' . $this->getConfiguration('jpiVolume') . '&queue=1&wait=1';
                log::add('JPI', 'info', 'Commande PLAY envoyée au périphérique JPI : ' . $url);
                $request_http = new com_http($url);
 				$request_http->setNoReportError(true);                                           
                $request_http->exec(1,1);
                break;
            
            case 'STOP':
                $url = 'http://' . $eqLogic->getConfiguration('jpiIp') . ':' . $eqLogic->getConfiguration('jpiPort') . '?action=stop';
                log::add('JPI', 'info', 'Commande STOP envoyée au périphérique JPI : ' . $url);
                $request_http = new com_http($url);
 				$request_http->setNoReportError(true);                                           
                $request_http->exec(1,1);
                break;
            
            case 'PAUSE':
                $url = 'http://' . $eqLogic->getConfiguration('jpiIp') . ':' . $eqLogic->getConfiguration('jpiPort') . '?action=pause';
                log::add('JPI', 'info', 'Commande PAUSE envoyée au périphérique JPI : ' . $url);
                $request_http = new com_http($url);
 				$request_http->setNoReportError(true);                                           
                $request_http->exec(1,1);
                break;
            
            case 'NEXT':
                $url = 'http://' . $eqLogic->getConfiguration('jpiIp') . ':' . $eqLogic->getConfiguration('jpiPort') . '?action=next';
                log::add('JPI', 'info', 'Commande NEXT envoyée au périphérique JPI : ' . $url);
                $request_http = new com_http($url);
 				$request_http->setNoReportError(true);                                           
                $request_http->exec(1,1);
                break;
            
            case 'MUTE':
                $url = 'http://' . $eqLogic->getConfiguration('jpiIp') . ':' . $eqLogic->getConfiguration('jpiPort') . '?action=muteAll';
                log::add('JPI', 'info', 'Commande MUTE envoyée au périphérique JPI : ' . $url);
                $request_http = new com_http($url);
 				$request_http->setNoReportError(true);                                           
                $request_http->exec(1,1);
                break;
            
            case 'UNMUTE':
                $url = 'http://' . $eqLogic->getConfiguration('jpiIp') . ':' . $eqLogic->getConfiguration('jpiPort') . '?action=unmuteAll';
                log::add('JPI', 'info', 'Commande UNMUTE envoyée au périphérique JPI : ' . $url);
                $request_http = new com_http($url);
 				$request_http->setNoReportError(true);                                           
                $request_http->exec(1,1);
                break;
            
            case 'VOLUME':
                $url = 'http://' . $eqLogic->getConfiguration('jpiIp') . ':' . $eqLogic->getConfiguration('jpiPort') . '?action=setVolume&volume=' . $this->getConfiguration('jpiVolume') . '&stream=' . $this->getConfiguration('jpiStream');
                log::add('JPI', 'info', 'Commande VOLUME envoyée au périphérique JPI : ' . $url);
                $request_http = new com_http($url);
 				$request_http->setNoReportError(true);                                           
                $request_http->exec(1,1);
                break;
            
            case 'VERSION':
                $url = 'http://' . $eqLogic->getConfiguration('jpiIp') . ':' . $eqLogic->getConfiguration('jpiPort') . '?action=getVersion';
                log::add('JPI', 'info', 'Commande VERSION envoyée au périphérique JPI : ' . $url);
                $request_http = new com_http($url);
 				$request_http->setNoReportError(true);                                           
                $request_http->exec(1,1);
                break;
            
            case 'NOM':
                $url = 'http://' . $eqLogic->getConfiguration('jpiIp') . ':' . $eqLogic->getConfiguration('jpiPort') . '?action=getDeviceName';
                log::add('JPI', 'info', 'Commande NOM envoyée au périphérique JPI : ' . $url);
                $request_http = new com_http($url);
 				$request_http->setNoReportError(true);                                           
                $request_http->exec(1,1);
                break;
            
            case 'INFOSMS':
                $url = 'http://' . $eqLogic->getConfiguration('jpiIp') . ':' . $eqLogic->getConfiguration('jpiPort') . '?action=getSmsCounter&detail=0';
                log::add('JPI', 'info', 'Commande INFOSMS envoyée au périphérique JPI : ' . $url);
                $request_http = new com_http($url);
 				$request_http->setNoReportError(true);                                           
                $request_http->exec(1,1);
                break;
            
            case 'WIFI':
                $url = 'http://' . $eqLogic->getConfiguration('jpiIp') . ':' . $eqLogic->getConfiguration('jpiPort') . '?action=getWifiStrength';
                log::add('JPI', 'info', 'Commande WIFI envoyée au périphérique JPI : ' . $url);
                $request_http = new com_http($url);
 				$request_http->setNoReportError(true);                                           
                $request_http->exec(1,1);
                break;
            
            case 'BATTERIE':
                $url = 'http://' . $eqLogic->getConfiguration('jpiIp') . ':' . $eqLogic->getConfiguration('jpiPort') . '?action=getBattLevel';
                log::add('JPI', 'info', 'Commande BATTERIE envoyée au périphérique JPI : ' . $url);
                $request_http = new com_http($url);
 				$request_http->setNoReportError(true);                                           
                $request_http->exec(1,1);
                break;
            
            case 'VIBRATE':
                $url = 'http://' . $eqLogic->getConfiguration('jpiIp') . ':' . $eqLogic->getConfiguration('jpiPort') . '?action=vibration';
                log::add('JPI', 'info', 'Commande VIBRATE envoyée au périphérique JPI : ' . $url);
                $request_http = new com_http($url);
 				$request_http->setNoReportError(true);                                           
                $request_http->exec(1,1);
                break;
            
            case 'FLASH':
                $url = 'http://' . $eqLogic->getConfiguration('jpiIp') . ':' . $eqLogic->getConfiguration('jpiPort') . '?action=animFlash';
                log::add('JPI', 'info', 'Commande FLASH envoyée au périphérique JPI : ' . $url);
                $request_http = new com_http($url);
 				$request_http->setNoReportError(true);                                           
                $request_http->exec(1,1);
                break;
            
            case 'NOTIF':
                $url = 'http://' . $eqLogic->getConfiguration('jpiIp') . ':' . $eqLogic->getConfiguration('jpiPort') . '?action=notification&header=' . urlencode($_options['title']) . '&message=' . urlencode($_options['message']);
                log::add('JPI', 'info', 'Commande NOTIF envoyée au périphérique JPI : ' . $url);
                $request_http = new com_http($url);
 				$request_http->setNoReportError(true);                                           
                $request_http->exec(1,1);
                break;
            
            case 'TOAST':
                $url = 'http://' . $eqLogic->getConfiguration('jpiIp') . ':' . $eqLogic->getConfiguration('jpiPort') . '?action=toast&message=' . urlencode($_options['message']);
                log::add('JPI', 'info', 'Commande NOTIF envoyée au périphérique JPI : ' . $url);
                $request_http = new com_http($url);
 				$request_http->setNoReportError(true);                                           
                $request_http->exec(1,1);
                break;
            
            case 'SCREENON':
                $url = 'http://' . $eqLogic->getConfiguration('jpiIp') . ':' . $eqLogic->getConfiguration('jpiPort') . '?action=screenOn';
                log::add('JPI', 'info', 'Commande SCREENON envoyée au périphérique JPI : ' . $url);
                $request_http = new com_http($url);
 				$request_http->setNoReportError(true);                                           
                $request_http->exec(1,1);
                break;
            
            case 'SCREENOFF':
                $url = 'http://' . $eqLogic->getConfiguration('jpiIp') . ':' . $eqLogic->getConfiguration('jpiPort') . '?action=screenOff';
                log::add('JPI', 'info', 'Commande SCREENOFF envoyée au périphérique JPI : ' . $url);
                $request_http = new com_http($url);
 				$request_http->setNoReportError(true);                                           
                $request_http->exec(1,1);
                break;                
        }
    }
    
    /*     * **********************Getteur Setteur*************************** */
}

?>
