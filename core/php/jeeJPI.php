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
require_once dirname(__FILE__) . "/../../../../core/php/core.inc.php";

if (isset($argv)) {
    foreach ($argv as $arg) {
        $argList = explode('=', $arg);
        if (isset($argList[0]) && isset($argList[1])) {
            $_REQUEST[$argList[0]] = $argList[1];
        }
    }
}


if (init('reponse') != '') {
    try {
        $type = init('reponse');
        if (!jeedom::apiAccess(init('apikey', init('api')))) {
            throw new Exception(__('Vous n\'etes pas autorisé à effectuer cette action', __FILE__));
        } else {
            $reponse = init('reponse');
            log::add('JPI', 'info', 'Réponse Ask : ' . $reponse);
            
            $eqLogics = eqLogic::byType('jpi');
            foreach ($eqLogics as $eqLogic) {
                foreach ($eqLogic->getCmd() as $cmd) {
                    if ($cmd->getCache('storeVariable', 'none') != 'none') {
                        $dataStore = new dataStore();
                        $dataStore->setType('scenario');
                        $dataStore->setKey($cmd->getCache('storeVariable', 'none'));
                        $dataStore->setValue($reponse);
                        $dataStore->setLink_id(-1);
                        $dataStore->save();
                        $cmd->setCache('storeVariable', 'none');
                        $cmd->save();
                        die();
                    }
                }
                
                
            }
        }
    }
    
    catch (Exception $e) {
        echo $e->getMessage();
        log::add('JPI', 'error', $e->getMessage());
    }
    die();
} 
