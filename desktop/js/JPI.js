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
$('#table_cmd tbody').delegate('.cmdAttr[data-l1key=configuration][data-l2key=jpiAction]', 'change', function() {
    var tr = $(this).closest('tr');
    tr.find('.modeOption').hide();
    tr.find('.modeOption' + '.' + $(this).value()).show();
    if ($(this).value() == 'SMS' || $(this).value() == 'TTS' || $(this).value() == 'TOAST' || $(this).value() == 'NOTIF' || $(this).value() == 'USERLOG') {
        tr.find('.cmdAttr[data-l1key=subtype]').value('message');
    } 
});

$('#bt_lms').on('click', function() {
            $('#md_modal2').dialog({
                title: "Configuration de votre device JPI"
            });
            
            $('#md_modal2').load('index.php?v=d&plugin=JPI&modal=modal.JPI&id=' + $('.eqLogicAttr[data-l1key=id]').value()).dialog('open');
        });

$("#table_cmd").sortable({
    axis: "y",
    cursor: "move",
    items: ".cmd",
    placeholder: "ui-state-highlight",
    tolerance: "intersect",
    forcePlaceholderSize: true
});

function getCmdForVoices() {
    var select = '';
    $.ajax({ // fonction permettant de faire de l'ajax
        type: "POST", // methode de transmission des données au fichier php
        url: "plugins/JPI/core/ajax/JPI.ajax.php", // url du fichier php
        data: {
            action: "getjpiVoice",
            ip: $('.eqLogicAttr[data-l1key=configuration][data-l2key=jpiIp]').value(),
            port: $('.eqLogicAttr[data-l1key=configuration][data-l2key=jpiPort]').value(),
        },
        dataType: 'json',
        error: function(request, status, error) {
            console.log("Erreur lors de la demande");
        },
        error: function(request, status, error) {
            handleAjaxError(request, status, error);
        },
        async: false,
        success: function(data) { // si l'appel a bien fonctionné
            if (data.state != 'ok') {
                $('#div_alert').showAlert({
                    message: data.result,
                    level: 'danger'
                });
                return;
            }

            $.each(data.result, function(val, text) {
                select += '<option value="' + text + '">' + text + '</option>';
            });
            select += '</select>';



        }
    });
    return select;
}

function getCmdForApp() {
    var select = '';
    $.ajax({ // fonction permettant de faire de l'ajax
        type: "POST", // methode de transmission des données au fichier php
        url: "plugins/JPI/core/ajax/JPI.ajax.php", // url du fichier php
        data: {
            action: "getjpiApp",
            ip: $('.eqLogicAttr[data-l1key=configuration][data-l2key=jpiIp]').value(),
            port: $('.eqLogicAttr[data-l1key=configuration][data-l2key=jpiPort]').value(),
        },
        dataType: 'json',
        error: function(request, status, error) {
            console.log("Erreur lors de la demande");
        },
        error: function(request, status, error) {
            handleAjaxError(request, status, error);
        },
        async: false,
        success: function(data) { // si l'appel a bien fonctionné
            if (data.state != 'ok') {
                $('#div_alert').showAlert({
                    message: data.result,
                    level: 'danger'
                });
                return;
            }

            $.each(data.result, function(val, text) {
                select += '<option value="' + text + '">' + text + '</option>';
            });
            select += '</select>';



        }
    });
    return select;
}

function printEqLogic(_data) {
    optionCmdForVoices = null;
    optionCmdForApp = null;
}

function addCmdToTable(_cmd) {

    if (!isset(_cmd)) {
        var _cmd = {
            configuration: {}
        };
    }
    if (optionCmdForVoices == null) {
        optionCmdForVoices = getCmdForVoices();
    }
    if (optionCmdForApp == null) {
        optionCmdForApp = getCmdForApp();
    }

    var tr = '<tr class="cmd" data-cmd_id="' + init(_cmd.id) + '">';
    tr += '<td>';
    if (is_numeric(_cmd.id)) {
        tr += '<a class="btn btn-default btn-xs cmdAction expertModeVisible" data-action="configure"><i class="fa fa-cogs"></i></a> ';
        tr += '<a class="btn btn-default btn-xs cmdAction" data-action="test"><i class="fa fa-rss"></i> {{Tester}}</a>';
    }
    tr += '<i class="fa fa-minus-circle pull-right cmdAction cursor" data-action="remove"></i></td>';
    tr += '<td>';
    tr += '<input class="cmdAttr form-control input-sm" data-l1key="name" placeholder="{{Nom}}">';
    tr += '<input class="cmdAttr" data-l1key="id" style="display:none;" />';
    tr += '<input class="cmdAttr" data-l1key="type" value="action" style="display:none;" />';
    tr += '<input class="cmdAttr" data-l1key="subtype" value="other" style="display:none;" />';
    tr += '</td>';
    tr += '<td>';
    if(_cmd.logicalId != 'play' && _cmd.logicalId != 'next' && _cmd.logicalId != 'mute'&& _cmd.logicalId != 'unmute'&& _cmd.logicalId != 'stop' && _cmd.logicalId != 'refresh' && _cmd.logicalId != 'setvolumemedia' && _cmd.logicalId != 'voice' && _cmd.logicalId != 'pause' && _cmd.logicalId != 'preset1' && _cmd.logicalId != 'preset2' && _cmd.logicalId != 'preset3' && _cmd.logicalId != 'preset4' && _cmd.type != 'info'){
    tr += '<select class="cmdAttr form-control input-sm" data-l1key="configuration" data-l2key="jpiAction">';
    tr += '<option value="NOKEY">{{Séléctionner une action...}}</option>';
    tr += '<option value="CHECK">{{APK - Check MAJ}}</option>';
    tr += '<option value="MAJ">{{APK - Force MAJ}}</option>';
    tr += '<option value="GOTODESIGN">{{APK - Afficher un design}}</option>';    
    tr += '<option value="GOTOHOME">{{APK - Afficher accueil}}</option>';    
    tr += '<option value="GOTOURL">{{APK - Afficher une page web}}</option>';    
    tr += '<option value="GOTOVIEW">{{APK - Afficher une vue}}</option>';
    tr += '<option value="SHOWAPP">{{APK - Application au premier plan}}</option>';    
    tr += '<option value="VRSTATUS">{{APK - Statut reconnaissance vocale}}</option>';    
    tr += '<option value="ANIMFLASH">{{Caméra - Flash}}</option>';    
    tr += '<option value="TAKEPICTURE">{{Caméra - Photo}}</option>';
    tr += '<option value="NOTIF">{{Fonction - Notification}}</option>';
    tr += '<option value="GETVAR">{{Fonction - Lit une variable}}</option>';
    tr += '<option value="SETVAR">{{Fonction - Définit une variable}}</option>';
    tr += '<option value="UNSETVAR">{{Fonction - Efface une variable}}</option>';    
    tr += '<option value="TASKER">{{Fonction - Tâche tasker}}</option>';    
    tr += '<option value="TOAST">{{Fonction - Toast}}</option>';
    tr += '<option value="PLAY">{{Média - Play}}</option>';
    tr += '<option value="TTS">{{Média - TTS}}</option>';
    tr += '<option value="VOLUME">{{Média - Volume}}</option>';
    tr += '<option value="VIBRATE">{{Média - Vibration}}</option>';
    tr += '<option value="CLEARLOG">{{Moteur - Vider un journal}}</option>';    
    tr += '<option value="CLEARDATA">{{Moteur - Nettoyer les données}}</option>';
    tr += '<option value="QUIT">{{Moteur - Arrêt}}</option>';
    tr += '<option value="RESTART">{{Moteur - Restart}}</option>';
    tr += '<option value="RELOADCONFIG">{{Moteur - Recharger la config}}</option>'; 
    tr += '<option value="USERLOG">{{Moteur - Ecrire dans le journal}}</option>';    
    tr += '<option value="BRIGHTNESS">{{Système - Niveau de luminosité}}</option>';
    tr += '<option value="LAUNCHAPP">{{Système - Lancer une application}}</option>';
    tr += '<option value="KILLAPP">{{Système - Fermer une application}}</option>'; 
    tr += '<option value="REBOOT">{{Système - Reboot}}</option>';
    tr += '<option value="RESETWIFI">{{Système - Reset le wifi}}</option>';
    tr += '<option value="SCREENON">{{Système - ScreenOn}}</option>';
    tr += '<option value="SCREENOFF">{{Système - ScreenOff}}</option>';
    tr += '<option value="CALL">{{Téléphonie - Appel}}</option>';
    tr += '<option value="SMS">{{Téléphonie - SMS}}</option>';
    tr += '<option value="RESETSMS">{{Téléphonie - Reset compteur SMS}}</option>';
    tr += '</select>';
    tr += '</td>';

    tr += '<td>';
    tr += '<span class="SMS CALL modeOption">';
    tr += '<input class="cmdAttr form-control input-sm" data-l1key="configuration" data-l2key="jpiNumsms" placeholder="{{Numéro téléphone}}" >';
    tr += '</span>';
    tr += '<span class="BRIGHTNESS modeOption">';
    tr += '<input class="cmdAttr form-control input-sm" data-l1key="configuration" data-l2key="jpiBrightness" placeholder="{{Luminosité}}" >';
    tr += '</span>';
    tr += '<span class="GETVAR SETVAR UNSETVAR modeOption">';
    tr += '<input class="cmdAttr form-control input-sm" data-l1key="configuration" data-l2key="jpiVarName" placeholder="{{Nom de la variable}}" >';
    tr += '</span>';
    tr += '<span class="TASKER modeOption">';
    tr += '<input class="cmdAttr form-control input-sm" data-l1key="configuration" data-l2key="jpiTaskname" placeholder="{{Nom de la tâche tasker}}" >';
    tr += '</span>';    

    tr += '<span class="GOTODESIGN modeOption">';
    tr += '<input class="cmdAttr form-control input-sm" data-l1key="configuration" data-l2key="jpiIddesign" placeholder="{{ID du design}}" >';
    tr += '</span>';    
    tr += '<span class="GOTOVIEW modeOption">';
    tr += '<input class="cmdAttr form-control input-sm" data-l1key="configuration" data-l2key="jpiIdview" placeholder="{{ID de la vue}}" >';
    tr += '</span>';
    tr += '<span class="GOTOURL modeOption">';
    tr += '<input class="cmdAttr form-control input-sm" data-l1key="configuration" data-l2key="jpiGotourl" placeholder="{{Adresse http}}" >';
    tr += '</span>';
    tr += '<span class="TTS VOLUME PLAY modeOption">';
    tr += '<select class="cmdAttr form-control input-sm" data-l1key="configuration" data-l2key="jpiVolume" placeholder="{{Volume}}" >';
    tr += '<option value="10">{{10}}</option>';
    tr += '<option value="20">{{20}}</option>';
    tr += '<option value="30">{{30}}</option>';
    tr += '<option value="40">{{40}}</option>';
    tr += '<option value="50">{{50}}</option>';
    tr += '<option value="60">{{60}}</option>';
    tr += '<option value="70">{{70}}</option>';
    tr += '<option value="80">{{80}}</option>';
    tr += '<option value="90">{{90}}</option>';
    tr += '<option value="100">{{100}}</option>';
    tr += '</select>';
    tr += '</span>';
    tr += '<span class="TAKEPICTURE modeOption">';
    tr += '<select class="cmdAttr form-control input-sm" data-l1key="configuration" data-l2key="jpiPicture"  placeholder="{{Caméra}}" >';
    tr += '<option value="front">{{Avant}}</option>';
    tr += '<option value="rear">{{Arrière}}</option>';
    tr += '</select>';
    tr += '</span>';
    tr += '<span class="CLEARLOG modeOption">';
    tr += '<select class="cmdAttr form-control input-sm" data-l1key="configuration" data-l2key="jpiLog"  placeholder="{{Journal}}" >';
    tr += '<option value="event_log">{{Journal  événements}}</option>';
    tr += '<option value="app_log">{{journal application}}</option>';    
    tr += '<option value="error_log">{{journal erreurs}}</option>';
    tr += '<option value="user_log">{{Journal utilisateur}}</option>';
    tr += '</select>';
    tr += '</span>';    
    tr += '<span class="CLEARDATA modeOption">';
    tr += '<select class="cmdAttr form-control input-sm" data-l1key="configuration" data-l2key="jpiData"  placeholder="{{Données}}" >';
    tr += '<option value="media">{{Fichiers du dossier média}}</option>';
    tr += '<option value="picts">{{Photos prises}}</option>';    
    tr += '<option value="vars">{{Variables}}</option>';
    tr += '<option value="events">{{Données des évenements}}</option>';
    tr += '<option value="log">{{Tout les journaux}}</option>';
    tr += '<option value="tmp">{{Fichiers temporaires}}</option>';
    tr += '<option value="all">{{Tout}}</option>';    
    tr += '</select>';
    tr += '</span>';
    tr += '<span class="VRSTATUS modeOption">';
    tr += '<select class="cmdAttr form-control input-sm" data-l1key="configuration" data-l2key="jpiVrstatus"  placeholder="{{Statut}}" >';
    tr += '<option value="0">{{Désactivé}}</option>';
    tr += '<option value="1">{{Activé}}</option>';
    tr += '</select>';
    tr += '</span>';
    tr += '<span class="LAUNCHAPP KILLAPP modeOption" style="display : none;">';
    tr += '<select class="cmdAttr form-control input-sm" data-l1key="configuration" data-l2key="jpiApp" style="display : inline-block;">';
    tr += optionCmdForApp;
    tr += '</select>';
    tr += '</span>';
    tr += '</td>';
    
    

    tr += '<td>';
    tr += '<span class="TTS modeOption" style="display : none;">';
    tr += '<select class="cmdAttr form-control input-sm" data-l1key="configuration" data-l2key="jpiVoice" style="display : inline-block;">';
    tr += optionCmdForVoices;
    tr += '</select>';
    tr += '</span>';
    tr += '<span class="TAKEPICTURE modeOption">';
    tr += '<input class="cmdAttr form-control input-sm" data-l1key="configuration" data-l2key="jpiResolution" placeholder="{{Résolution}}" >';
    tr += '</span>';
    tr += '<span class="SETVAR modeOption">';
    tr += '<input class="cmdAttr form-control input-sm" data-l1key="configuration" data-l2key="jpiVarvalue" placeholder="{{Valeur de la variable}}" >';
    tr += '</span>';
    tr += '<span class="PLAY modeOption">';
    tr += '<input class="cmdAttr form-control input-sm" data-l1key="configuration" data-l2key="jpiMedia" placeholder="{{Média}}" >';
    tr += '</span>';
    tr += '<span class="VOLUME modeOption">';
    tr += '<select class="cmdAttr form-control input-sm" data-l1key="configuration" data-l2key="jpiStream" placeholder="{{Source}}" >';
    tr += '<option value="alarm">{{Alarme}}</option>';
    tr += '<option value="call">{{Appel}}</option>';
    tr += '<option value="dtmf">{{DTMF}}</option>';
    tr += '<option value="media">{{Médias}}</option>';
    tr += '<option value="notif">{{Notification}}</option>';
    tr += '<option value="ring">{{Sonnerie}}</option>';
    tr += '<option value="system">{{Système}}</option>';
    tr += '</select>';
    tr += '</td>';




    tr += '</tr>';
 }

    $('#table_cmd tbody').append(tr);
    $('#table_cmd tbody tr:last').setValues(_cmd, '.cmdAttr');
  
}  
