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
        log::add('JPI', 'DEBUG', 'Langue(s) découverte(s) : ' . $content);
        return $value;
    }
    
    public static function getjpiApp($ip, $port)
    {
        
        $url     = 'http://' . $ip . ':' . $port . '/?action=getPackagesNames';
        $content = file_get_contents($url);
        $value   = explode(', ', $content);
        log::add('JPI', 'DEBUG', 'Application(s) découverte(s) : ' . $content);
        return $value;
    }
    
    
    public function cron30($_eqlogic_id = null)
    {
        
        if ($_eqlogic_id !== null) {
            $eqLogics = array(
                eqLogic::byId($_eqlogic_id)
            );
        } else {
            $eqLogics = eqLogic::byType('JPI');
        }
        foreach ($eqLogics as $JPI) {
            if ($JPI->getIsEnable() == 1) {
                $urlbatterie   = 'http://' . $JPI->getConfiguration('jpiIp') . ':' . $JPI->getConfiguration('jpiPort') . '/?action=getBattLevel';
                $valuebatterie = file_get_contents($urlbatterie);
                $cmd           = $JPI->getCmd(null, 'infobatterie');
                $cmd->event($valuebatterie);
                
                $urlversion   = 'http://' . $JPI->getConfiguration('jpiIp') . ':' . $JPI->getConfiguration('jpiPort') . '/?action=getVersion';
                $valueversion = file_get_contents($urlversion);
                $cmd          = $JPI->getCmd(null, 'infoversion');
                $cmd->event($valueversion);
                
                $urlsignal   = 'http://' . $JPI->getConfiguration('jpiIp') . ':' . $JPI->getConfiguration('jpiPort') . '/?action=getWifiStrength';
                $valuesignal = file_get_contents($urlsignal);
                $cmd         = $JPI->getCmd(null, 'infosignal');
                $cmd->event($valuesignal);
                
                $urlnbsms   = 'http://' . $JPI->getConfiguration('jpiIp') . ':' . $JPI->getConfiguration('jpiPort') . '/?action=getSmsCounter';
                $valuenbsms = file_get_contents($urlnbsms);
                $cmd        = $JPI->getCmd(null, 'infosms');
                $cmd->event($valuenbsms);
                
                $urlvolmedia   = 'http://' . $JPI->getConfiguration('jpiIp') . ':' . $JPI->getConfiguration('jpiPort') . '/?action=getVolume&stream=media';
                $valuevolmedia = file_get_contents($urlvolmedia);
                $cmd           = $JPI->getCmd(null, 'infovolume');
                $cmd->event($valuevolmedia);
                
                $JPI->refreshWidget();
            }
        }
        
    }
    
    public static function executerequest($request)
    {
        log::add('JPI', 'info', 'Commande envoyée au device JPI : ' . $request);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_URL, $request);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        $response = curl_exec($ch);
        curl_close($ch);
        
        if (preg_match("/\bok\b/i", $response) || preg_match("/\bstorage\b/i", $response)) {
            log::add('JPI', 'INFO', 'Réponse JPI pour la requête demandée : ' . $response);
            return $response;
        } else {
            log::add('JPI', 'INFO', 'Réponse JPI pour la requête demandée : KO');
            return $response;
            
        }
        
    }
    
    public function preUpdate()
    {
    }
    
    public function postUpdate()
    {
        
        $volmedia = $this->getCmd(null, 'infovolume');
        if (!is_object($volmedia)) {
            $volmedia = new JPICmd();
            $volmedia->setLogicalId('infovolume');
            $volmedia->setIsVisible(0);
            $volmedia->setName(__('Info volume media', __FILE__));
        }
        $volmedia->setType('info');
        $volmedia->setSubType('string');
        $volmedia->setEqLogic_id($this->getId());
        $volmedia->save();
        
        $setvol = $this->getCmd(null, 'setvolumemedia');
        if (!is_object($setvol)) {
            $setvol = new JPICmd();
            $setvol->setLogicalId('setvolumemedia');
            $setvol->setIsVisible(1);
            $setvol->setName(__('Configuration du volume media', __FILE__));
        }
        $setvol->setType('action');
        $setvol->setSubType('other');
        $setvol->setEqLogic_id($this->getId());
        $setvol->save();
        
        $mute = $this->getCmd(null, 'mute');
        if (!is_object($mute)) {
            $mute = new JPICmd();
            $mute->setLogicalId('mute');
            $mute->setIsVisible(1);
            $mute->setName(__('Mute', __FILE__));
        }
        $mute->setType('action');
        $mute->setSubType('other');
        $mute->setEqLogic_id($this->getId());
        $mute->save();
        
        $batterie = $this->getCmd(null, 'infobatterie');
        if (!is_object($batterie)) {
            $batterie = new JPICmd();
        }
        $batterie->setName(__('Niveau de la batterie', __FILE__));
        $batterie->setLogicalId('infobatterie');
        $batterie->setEqLogic_id($this->getId());
        $batterie->setIsVisible(1);
        $batterie->setUnite('%');
        $batterie->setType('info');
        $batterie->setSubType('numeric');
        $batterie->setDisplay('generic_type', 'infobatterie');
        $batterie->save();
        
        $nbsms = $this->getCmd(null, 'infosms');
        if (!is_object($nbsms)) {
            $nbsms = new JPICmd();
        }
        $nbsms->setName(__('Nombre de SMS envoyés', __FILE__));
        $nbsms->setLogicalId('infosms');
        $nbsms->setEqLogic_id($this->getId());
        $nbsms->setIsVisible(1);
        $nbsms->setType('info');
        $nbsms->setSubType('string');
        $nbsms->setDisplay('generic_type', 'infosms');
        $nbsms->save();
        
        $nbsms = $this->getCmd(null, 'infosentsms');
        if (!is_object($nbsms)) {
            $nbsms = new JPICmd();
        }
        $nbsms->setName(__('Statut SMS', __FILE__));
        $nbsms->setLogicalId('infosentsms');
        $nbsms->setEqLogic_id($this->getId());
        $nbsms->setIsVisible(1);
        $nbsms->setType('info');
        $nbsms->setSubType('string');
        $nbsms->setDisplay('generic_type', 'infosentsms');
        $nbsms->save();
        
        $pause = $this->getCmd(null, 'pause');
        if (!is_object($pause)) {
            $pause = new JPICmd();
            $pause->setLogicalId('pause');
            $pause->setIsVisible(1);
            $pause->setName(__('Pause', __FILE__));
        }
        $pause->setType('action');
        $pause->setSubType('other');
        $pause->setEqLogic_id($this->getId());
        $pause->save();
        
        $play = $this->getCmd(null, 'play');
        if (!is_object($play)) {
            $play = new JPICmd();
            $play->setLogicalId('play');
            $play->setIsVisible(1);
            $play->setName(__('Play', __FILE__));
        }
        $play->setType('action');
        $play->setSubType('other');
        $play->setEqLogic_id($this->getId());
        $play->save();
        
        $signal = $this->getCmd(null, 'infosignal');
        if (!is_object($signal)) {
            $signal = new JPICmd();
        }
        $signal->setName(__('Puissance du signal', __FILE__));
        $signal->setLogicalId('infosignal');
        $signal->setEqLogic_id($this->getId());
        $signal->setIsVisible(1);
        $signal->setUnite('%');
        $signal->setType('info');
        $signal->setSubType('string');
        $signal->setDisplay('generic_type', 'infosignal');
        $signal->save();
        
        $preset1 = $this->getCmd(null, 'preset1');
        if (!is_object($preset1)) {
            $preset1 = new JPICmd();
            $preset1->setLogicalId('preset1');
            $preset1->setIsVisible(1);
            $preset1->setName(__('Preset1 media', __FILE__));
        }
        $preset1->setType('action');
        $preset1->setSubType('other');
        $preset1->setEqLogic_id($this->getId());
        $preset1->save();
        
        $preset2 = $this->getCmd(null, 'preset2');
        if (!is_object($preset2)) {
            $preset2 = new JPICmd();
            $preset2->setLogicalId('preset2');
            $preset2->setIsVisible(1);
            $preset2->setName(__('Preset2 media', __FILE__));
        }
        $preset2->setType('action');
        $preset2->setSubType('other');
        $preset2->setEqLogic_id($this->getId());
        $preset2->save();
        
        $preset3 = $this->getCmd(null, 'preset3');
        if (!is_object($preset3)) {
            $preset3 = new JPICmd();
            $preset3->setLogicalId('preset3');
            $preset3->setIsVisible(1);
            $preset3->setName(__('Preset3 media', __FILE__));
        }
        $preset3->setType('action');
        $preset3->setSubType('other');
        $preset3->setEqLogic_id($this->getId());
        $preset3->save();
        
        $preset4 = $this->getCmd(null, 'preset4');
        if (!is_object($preset4)) {
            $preset4 = new JPICmd();
            $preset4->setLogicalId('preset4');
            $preset4->setIsVisible(1);
            $preset4->setName(__('Preset4 media', __FILE__));
        }
        $preset4->setType('action');
        $preset4->setSubType('other');
        $preset4->setEqLogic_id($this->getId());
        $preset4->save();
        
        $forward = $this->getCmd(null, 'next');
        if (!is_object($forward)) {
            $forward = new JPICmd();
            $forward->setLogicalId('next');
            $forward->setIsVisible(1);
            $forward->setName(__('Next', __FILE__));
        }
        $forward->setType('action');
        $forward->setSubType('other');
        $forward->setEqLogic_id($this->getId());
        $forward->save();
        
        $refresh = $this->getCmd(null, 'refresh');
        if (!is_object($refresh)) {
            $refresh = new JPICmd();
            $refresh->setLogicalId('refresh');
            $refresh->setIsVisible(1);
            $refresh->setName(__('Rafraichir', __FILE__));
        }
        $refresh->setType('action');
        $refresh->setSubType('other');
        $refresh->setEqLogic_id($this->getId());
        $refresh->save();
        
        $stop = $this->getCmd(null, 'stop');
        if (!is_object($stop)) {
            $stop = new JPICmd();
            $stop->setLogicalId('stop');
            $stop->setIsVisible(1);
            $stop->setName(__('Stop', __FILE__));
        }
        $stop->setType('action');
        $stop->setSubType('other');
        $stop->setEqLogic_id($this->getId());
        $stop->save();
        
        $unmute = $this->getCmd(null, 'unmute');
        if (!is_object($unmute)) {
            $unmute = new JPICmd();
            $unmute->setLogicalId('unmute');
            $unmute->setIsVisible(1);
            $unmute->setName(__('Unmute', __FILE__));
        }
        $unmute->setType('action');
        $unmute->setSubType('other');
        $unmute->setEqLogic_id($this->getId());
        $unmute->save();
        
        $version = $this->getCmd(null, 'infoversion');
        if (!is_object($version)) {
            $version = new JPICmd();
        }
        $version->setName(__('Version du moteur', __FILE__));
        $version->setLogicalId('infoversion');
        $version->setEqLogic_id($this->getId());
        $version->setIsVisible(1);
        $version->setType('info');
        $version->setSubType('string');
        $version->setDisplay('generic_type', 'infoversion');
        $version->save();
        
        $voice = $this->getCmd(null, 'voice');
        if (!is_object($voice)) {
            $voice = new JPICmd();
            $voice->setLogicalId('voice');
            $voice->setIsVisible(1);
            $voice->setName(__('Reconnaissance vocale', __FILE__));
        }
        $voice->setType('action');
        $voice->setSubType('other');
        $voice->setEqLogic_id($this->getId());
        $voice->save();
        
        $this->cron30($this->getId());
        
    }
    public function preRemove()
    {
        
    }
    
    public function postRemove()
    {
        
    }
    
    public function toHtml($_version = 'dashboard')
    {
        $replace = $this->preToHtml($_version);
        if (!is_array($replace)) {
            return $replace;
        }
        $version = jeedom::versionAlias($_version);
        foreach ($this->getCmd('info') as $cmd) {
            $replace['#' . $cmd->getLogicalId() . '#']    = $cmd->execCmd();
            $replace['#' . $cmd->getLogicalId() . '_id#'] = $cmd->getId();
            if ($cmd->getIsHistorized() == 1) {
                $replace['#' . $cmd->getLogicalId() . '_history#'] = 'history cursor';
            }
        }
        
        foreach ($this->getCmd('action') as $cmd) {
            $replace['#cmd_' . $cmd->getLogicalId() . '_id#'] = $cmd->getId();
        }
        return $this->postToHtml($_version, template_replace($replace, getTemplate('core', $version, 'JPI', 'JPI')));
    }
    
    
}

class JPICmd extends cmd
{
    
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
            $this->setDisplay('title_placeholder', __('Volume', __FILE__));
        }
        if ($this->getConfiguration('jpiAction') == 'SMS') {
            $this->setDisplay('title_disable', 1);
        }
        if ($this->getConfiguration('jpiAction') == 'USERLOG') {
            $this->setDisplay('title_disable', 1);
        }
    }
    
    public function execute($_options = null)
    {
        $eqLogic = $this->getEqLogic();
        
        
        switch ($this->getLogicalId()) {
            
            case 'mute':
                $request = 'http://' . $eqLogic->getConfiguration('jpiIp') . ':' . $eqLogic->getConfiguration('jpiPort') . '/?action=muteAll';
                $eqLogic->executerequest($request);
                break;
            
            case 'next':
                $request = 'http://' . $eqLogic->getConfiguration('jpiIp') . ':' . $eqLogic->getConfiguration('jpiPort') . '/?action=next';
                $eqLogic->executerequest($request);
                break;
            
            case 'play':
                $request = 'http://' . $eqLogic->getConfiguration('jpiIp') . ':' . $eqLogic->getConfiguration('jpiPort') . '/?action=play';
                $eqLogic->executerequest($request);
                break;
            
            case 'pause':
                $request = 'http://' . $eqLogic->getConfiguration('jpiIp') . ':' . $eqLogic->getConfiguration('jpiPort') . '/?action=pause';
                $eqLogic->executerequest($request);
                break;
            
            case 'preset1':
                $request = 'http://' . $eqLogic->getConfiguration('jpiIp') . ':' . $eqLogic->getConfiguration('jpiPort') . '/?action=play&media=' . $eqLogic->getConfiguration('jpiPreset1');
                $eqLogic->executerequest($request);
                break;
            
            case 'preset2':
                $request = 'http://' . $eqLogic->getConfiguration('jpiIp') . ':' . $eqLogic->getConfiguration('jpiPort') . '/?action=play&media=' . $eqLogic->getConfiguration('jpiPreset2');
                $eqLogic->executerequest($request);
                break;
            
            case 'preset3':
                $request = 'http://' . $eqLogic->getConfiguration('jpiIp') . ':' . $eqLogic->getConfiguration('jpiPort') . '/?action=play&media=' . $eqLogic->getConfiguration('jpiPreset3');
                $eqLogic->executerequest($request);
                break;
            
            case 'preset4':
                $request = 'http://' . $eqLogic->getConfiguration('jpiIp') . ':' . $eqLogic->getConfiguration('jpiPort') . '/?action=play&media=' . $eqLogic->getConfiguration('jpiPreset4');
                $eqLogic->executerequest($request);
                break;
            
            case 'refresh':
                $eqLogic->cron30($eqLogic->getId());
                return true;
                break;
            
            case 'setvolumemedia':
                $vol     = $_options['slider'];
                $request = 'http://' . $eqLogic->getConfiguration('jpiIp') . ':' . $eqLogic->getConfiguration('jpiPort') . '/?action=setVolume&volume=' . $vol . '&stream=media';
                $eqLogic->executerequest($request);
                break;
            
            case 'stop':
                $request = 'http://' . $eqLogic->getConfiguration('jpiIp') . ':' . $eqLogic->getConfiguration('jpiPort') . '/?action=stop';
                $eqLogic->executerequest($request);
                break;
            
            case 'unmute':
                $request = 'http://' . $eqLogic->getConfiguration('jpiIp') . ':' . $eqLogic->getConfiguration('jpiPort') . '/?action=unmuteAll';
                $eqLogic->executerequest($request);
                break;
            
            case 'voice':
                $request = 'http://' . $eqLogic->getConfiguration('jpiIp') . ':' . $eqLogic->getConfiguration('jpiPort') . '/?action=voiceCmd';
                $eqLogic->executerequest($request);;
                break;
        }
        
        
        switch ($this->getConfiguration('jpiAction')) {
            
            
            /* *******************************************APK**************************************************************************************************************************************************** */
            
            case 'CHECK':
                $request = 'http://' . $eqLogic->getConfiguration('jpiIp') . ':' . $eqLogic->getConfiguration('jpiPort') . '/?action=checkMaj';
                $eqLogic->executerequest($request);
                break;
            
            case 'GOTODESIGN':
                $request = 'http://' . $eqLogic->getConfiguration('jpiIp') . ':' . $eqLogic->getConfiguration('jpiPort') . '/?action=goToDesign&id=' . $this->getConfiguration('jpiIddesign');
                $eqLogic->executerequest($request);
                break;
            
            case 'MAJ':
                $request = 'http://' . $eqLogic->getConfiguration('jpiIp') . ':' . $eqLogic->getConfiguration('jpiPort') . '/?action=forceMaj';
                $eqLogic->executerequest($request);
                break;
            
            case 'GOTOHOME':
                $request = 'http://' . $eqLogic->getConfiguration('jpiIp') . ':' . $eqLogic->getConfiguration('jpiPort') . '/?action=goToHome';
                $eqLogic->executerequest($request);
                break;
            
            case 'GOTOURL':
                $request = 'http://' . $eqLogic->getConfiguration('jpiIp') . ':' . $eqLogic->getConfiguration('jpiPort') . '/?action=goToUrl&url=' . $this->getConfiguration('jpiGotourl');
                $eqLogic->executerequest($request);
                break;
            
            case 'GOTOVIEW':
                $request = 'http://' . $eqLogic->getConfiguration('jpiIp') . ':' . $eqLogic->getConfiguration('jpiPort') . '/?action=goToView&id=' . $this->getConfiguration('jpiIdview');
                $eqLogic->executerequest($request);
                break;
            
            case 'SHOWAPP':
                $request = 'http://' . $eqLogic->getConfiguration('jpiIp') . ':' . $eqLogic->getConfiguration('jpiPort') . '/?action=showApp';
                $eqLogic->executerequest($request);
                break;
            
            case 'VRSTATUS':
                $request = 'http://' . $eqLogic->getConfiguration('jpiIp') . ':' . $eqLogic->getConfiguration('jpiPort') . '/?action=VRstatus&status=' . $this->getConfiguration('jpiVrstatus');
                $eqLogic->executerequest($request);
                break;
            
            /* *******************************************CAMERA**************************************************************************************************************************************************** */
            
            case 'ANIMFLASH':
                $request = 'http://' . $eqLogic->getConfiguration('jpiIp') . ':' . $eqLogic->getConfiguration('jpiPort') . '/?action=animFlash';
                $eqLogic->executerequest($request);
                break;
            
            case 'TAKEPICTURE':
                $request = 'http://' . $eqLogic->getConfiguration('jpiIp') . ':' . $eqLogic->getConfiguration('jpiPort') . '/?action=takePicture&camera=' . $this->getConfiguration('jpiPicture') . '&resolution=' . $this->getConfiguration('jpiResolution');
                $eqLogic->executerequest($request);
                break;
            
            /********************************************FONCTIONS**************************************************************************************/
            
            case 'NOTIF':
                $request = 'http://' . $eqLogic->getConfiguration('jpiIp') . ':' . $eqLogic->getConfiguration('jpiPort') . '/?action=notification&header=' . urlencode($_options['title']) . '&message=' . urlencode($_options['message']);
                $eqLogic->executerequest($request);
                break;
            
            case 'SETVAR':
                $request = 'http://' . $eqLogic->getConfiguration('jpiIp') . ':' . $eqLogic->getConfiguration('jpiPort') . '/?action=setVar&name=' . $this->getConfiguration('jpiVarName') . '&value=' . $this->getConfiguration('jpiVarvalue');
                $eqLogic->executerequest($request);
                break;
            
            case 'SCREENON':
                $request = 'http://' . $eqLogic->getConfiguration('jpiIp') . ':' . $eqLogic->getConfiguration('jpiPort') . '/?action=screenOn';
                $eqLogic->executerequest($request);
                break;
            
            case 'SCREENOFF':
                $request = 'http://' . $eqLogic->getConfiguration('jpiIp') . ':' . $eqLogic->getConfiguration('jpiPort') . '/?action=screenOff';
                $eqLogic->executerequest($request);
                break;
            
            case 'TASKER':
                $request = 'http://' . $eqLogic->getConfiguration('jpiIp') . ':' . $eqLogic->getConfiguration('jpiPort') . '/?action=tasker&task=' . $this->getConfiguration('jpiTaskname');
                $eqLogic->executerequest($request);
                break;
            
            case 'TOAST':
                $request = 'http://' . $eqLogic->getConfiguration('jpiIp') . ':' . $eqLogic->getConfiguration('jpiPort') . '/?action=toast&message=' . urlencode($_options['message']);
                $eqLogic->executerequest($request);
                break;
            
            case 'UNSETVAR':
                $request = 'http://' . $eqLogic->getConfiguration('jpiIp') . ':' . $eqLogic->getConfiguration('jpiPort') . '/?action=unsetVar&name=' . $this->getConfiguration('jpiVarName');
                $eqLogic->executerequest($request);
                break;
            
            case 'VIBRATE':
                $request = 'http://' . $eqLogic->getConfiguration('jpiIp') . ':' . $eqLogic->getConfiguration('jpiPort') . '/?action=vibration';
                $eqLogic->executerequest($request);
                break;
            
            /********************************************MEDIA**************************************************************************************/
            
             case 'TTS':          		
                if (isset($_options['title']) && $_options['title'] != '') {
				$request = 'http://' . $eqLogic->getConfiguration('jpiIp') . ':' . $eqLogic->getConfiguration('jpiPort') . '/?action=tts&message=' . urlencode($_options['message']) . '&volume=' . ($_options['title']) . '&voice=' . $this->getConfiguration('jpiVoice');
				$eqLogic->executerequest($request);
                } else {
				$request = 'http://' . $eqLogic->getConfiguration('jpiIp') . ':' . $eqLogic->getConfiguration('jpiPort') . '/?action=tts&message=' . urlencode($_options['message']) . '&voice=' . $this->getConfiguration('jpiVoice');
				$eqLogic->executerequest($request);
                }				
                break;
            
            case 'PLAY':
                $request = 'http://' . $eqLogic->getConfiguration('jpiIp') . ':' . $eqLogic->getConfiguration('jpiPort') . '/?action=play&media=' . $this->getConfiguration('jpiMedia') . '&volume=' . $this->getConfiguration('jpiVolume');
                $eqLogic->executerequest($request);
                break;
            
            case 'VOLUME':
                $request = 'http://' . $eqLogic->getConfiguration('jpiIp') . ':' . $eqLogic->getConfiguration('jpiPort') . '/?action=setVolume&volume=' . $this->getConfiguration('jpiVolume') . '&stream=' . $this->getConfiguration('jpiStream');
                $eqLogic->executerequest($request);
                break;
            
            /* *******************************************MOTEUR**************************************************************************************************************************************************** */
            
            case 'CLEARDATA':
                $request = 'http://' . $eqLogic->getConfiguration('jpiIp') . ':' . $eqLogic->getConfiguration('jpiPort') . '/?action=clearData&data=' . $this->getConfiguration('jpiData');
                $eqLogic->executerequest($request);
                break;
            
            case 'CLEARLOG':
                $request = 'http://' . $eqLogic->getConfiguration('jpiIp') . ':' . $eqLogic->getConfiguration('jpiPort') . '/?action=clearLog&log=' . $this->getConfiguration('jpiLog');
                $eqLogic->executerequest($request);
                break;
            
            case 'RESTART':
                $request = 'http://' . $eqLogic->getConfiguration('jpiIp') . ':' . $eqLogic->getConfiguration('jpiPort') . '/?action=restart';
                $eqLogic->executerequest($request);
                break;
            
            case 'RELOADCONFIG':
                $request = 'http://' . $eqLogic->getConfiguration('jpiIp') . ':' . $eqLogic->getConfiguration('jpiPort') . '/?action=reloadConfig';
                $eqLogic->executerequest($request);
                break;
            
            case 'USERLOG':
                $request = 'http://' . $eqLogic->getConfiguration('jpiIp') . ':' . $eqLogic->getConfiguration('jpiPort') . '/?action=userLog' . '&message=' . urlencode($_options['message']);
                $eqLogic->executerequest($request);
                break;
            
            case 'QUIT':
                $request = 'http://' . $eqLogic->getConfiguration('jpiIp') . ':' . $eqLogic->getConfiguration('jpiPort') . '/?action=quit';
                $eqLogic->executerequest($request);
                break;
            
            /* *******************************************SYSTEME**************************************************************************************************************************************************** */
            
            case 'BRIGHTNESS':
                $request = 'http://' . $eqLogic->getConfiguration('jpiIp') . ':' . $eqLogic->getConfiguration('jpiPort') . '/?action=brightness&level=' . $this->getConfiguration('jpiBrightness');
                $eqLogic->executerequest($request);
                break;
            
            case 'LAUNCHAPP':
                $request = 'http://' . $eqLogic->getConfiguration('jpiIp') . ':' . $eqLogic->getConfiguration('jpiPort') . '/?action=launchApp&packageName=' . $this->getConfiguration('jpiApp');
                $eqLogic->executerequest($request);
                break;
            
            case 'KILLAPP':
                $request = 'http://' . $eqLogic->getConfiguration('jpiIp') . ':' . $eqLogic->getConfiguration('jpiPort') . '/?action=killApp&packageName=' . $this->getConfiguration('jpiApp');
                $eqLogic->executerequest($request);
                break;
            
            case 'REBOOT':
                $request = 'http://' . $eqLogic->getConfiguration('jpiIp') . ':' . $eqLogic->getConfiguration('jpiPort') . '/?action=reboot';
                $eqLogic->executerequest($request);
                break;
            
            case 'RESETWIFI':
                $request = 'http://' . $eqLogic->getConfiguration('jpiIp') . ':' . $eqLogic->getConfiguration('jpiPort') . '/?action=resetWifi';
                $eqLogic->executerequest($request);
                break;
            
            /********************************************TELEPHONIE**************************************************************************************/
            
            case 'CALL':
                $request = 'http://' . $eqLogic->getConfiguration('jpiIp') . ':' . $eqLogic->getConfiguration('jpiPort') . '/?action=makeCall&number=' . $this->getConfiguration('jpiNumsms');
                $eqLogic->executerequest($request);
                break;
            
            case 'RESETSMS':
                $request = 'http://' . $eqLogic->getConfiguration('jpiIp') . ':' . $eqLogic->getConfiguration('jpiPort') . '/?action=resetSmsCounter';
                $eqLogic->executerequest($request);
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
                
                $url      = 'http://' . $eqLogic->getConfiguration('jpiIp') . ':' . $eqLogic->getConfiguration('jpiPort') . '/?action=sendSms&number=' . $this->getConfiguration('jpiNumsms') . '&message=' . urlencode($message);
                $request  = 'http://' . $eqLogic->getConfiguration('jpiIp') . ':' . $eqLogic->getConfiguration('jpiPort') . '/?action=sendSms&number=' . $this->getConfiguration('jpiNumsms') . '&message=' . urlencode($message);
                $response = $eqLogic->executerequest($request);
                
                if (!preg_match("/\bok\b/i", $response)) {
                    $cmd = $eqLogic->getCmd(null, 'infosentsms');
                    $cmd->event("0");
                } else {
                    $cmd = $eqLogic->getCmd(null, 'infosentsms');
                    $cmd->event("1");
                    
                }
                $eqLogic->refreshWidget();
                
                break;
                
                
                
                
                
                
                
        }
        
        
    }
    
    /*     * **********************Getteur Setteur*************************** */
}

?> 
