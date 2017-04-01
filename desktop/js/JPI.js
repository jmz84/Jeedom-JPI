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
    if ($(this).value() == 'SMS' || $(this).value() == 'TTS' || $(this).value() == 'TOAST' || $(this).value() == 'NOTIF') {
        tr.find('.cmdAttr[data-l1key=subtype]').value('message');
    } //else{
    //    tr.find('.cmdAttr[data-l1key=subtype]').value('string');
    //  }
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

function printEqLogic(_data) {
    optionCmdForVoices = null;
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


    var tr = '<tr class="cmd" data-cmd_id="' + init(_cmd.id) + '">';
    tr += '<td>';
    tr += '<input class="cmdAttr form-control input-sm" data-l1key="name" placeholder="{{Nom}}">';
    tr += '<input class="cmdAttr" data-l1key="id" style="display:none;" />';
    tr += '<input class="cmdAttr" data-l1key="type" value="action" style="display:none;" />';
    tr += '<input class="cmdAttr" data-l1key="subtype" value="other" style="display:none;" />';
    tr += '</td>';

    tr += '<td>';
    tr += '<select class="cmdAttr form-control input-sm" data-l1key="configuration" data-l2key="jpiAction">';
    tr += '<option value="NOKEY">{{Séléctionner une action...}}</option>';
    tr += '<option value="APKCHECK">{{APK - Check MAJ}}</option>';
    tr += '<option value="APKMAJ">{{APK - Force MAJ}}</option>';
    tr += '<option value="NOKEY">{{----------------------------------------}}</option>';
    tr += '<option value="NOTIF">{{Fonction - Notification}}</option>';
    tr += '<option value="TOAST">{{Fonction - Toast}}</option>';
    tr += '<option value="NOKEY">{{----------------------------------------}}</option>';
    tr += '<option value="VERSION">{{Info - JPI version}}</option>';
    tr += '<option value="NOM">{{Info - Nom}}</option>';
    tr += '<option value="INFOSMS">{{Info - Sms envoyés}}</option>';
    tr += '<option value="WIFI">{{Info - Puissance wifi}}</option>';
    tr += '<option value="BATTERIE>{{Info - Niveau batterie}}</option>';
    tr += '<option value="NOKEY">{{----------------------------------------}}</option>';
    tr += '<option value="FLASH">{{Média - Flash}}</option>';
    tr += '<option value="MUTE">{{Média - Mute}}</option>';
    tr += '<option value="NEXT">{{Média - Next}}</option>';
    tr += '<option value="PAUSE">{{Média - Pause}}</option>';
    tr += '<option value="PLAY">{{Média - Play}}</option>';
    tr += '<option value="PICTURE">{{Média - Photo}}</option>';
    tr += '<option value="STOP">{{Média - Stop}}</option>';
    tr += '<option value="TTS">{{Média - TTS}}</option>';
    tr += '<option value="UNMUTE">{{Média - UnMute}}</option>';
    tr += '<option value="VOLUME">{{Média - Volume}}</option>';
    tr += '<option value="VIBRATE">{{Média - Vibration}}</option>';
    tr += '<option value="NOKEY">{{----------------------------------------}}</option>';
    tr += '<option value="GEARQUIT">{{Moteur - Arrêt}}</option>';
    tr += '<option value="GEARREBOOT">{{Moteur - Reboot}}</option>';
    tr += '<option value="NOKEY">{{----------------------------------------}}</option>';
    tr += '<option value="SYSREBOOT">{{Système - Reboot}}</option>';
    tr += '<option value="SCREENON">{{Système - ScreenOn}}</option>';
    tr += '<option value="SCREENOFF">{{Système - ScreenOff}}</option>';
    tr += '<option value="NOKEY">{{----------------------------------------}}</option>';
    tr += '<option value="CALL">{{Téléphonie - Appel}}</option>';
    tr += '<option value="SMS">{{Téléphonie - SMS}}</option>';
    tr += '</select>';
    tr += '</td>';

    tr += '<td>';
    tr += '<span class="SMS CALL modeOption">';
    tr += '<input class="cmdAttr form-control input-sm" data-l1key="configuration" data-l2key="jpiNumsms" placeholder="{{Numéro téléphone}}" >';
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
    tr += '<span class="PICTURE modeOption">';
    tr += '<select class="cmdAttr form-control input-sm" data-l1key="configuration" data-l2key="jpiPicture"  placeholder="{{Caméra}}" >';
    tr += '<option value="front">{{Avant}}</option>';
    tr += '<option value="rear">{{Arrière}}</option>';
    tr += '</select>';
    tr += '</span>';
    tr += '</td>';

    tr += '<td>';
    tr += '<span class="TTS modeOption" style="display : none;">';
    tr += '<select class="cmdAttr form-control input-sm" data-l1key="configuration" data-l2key="jpiVoice" style="display : inline-block;">';
    tr += optionCmdForVoices;
    tr += '</select>';
    tr += '</span>';
    tr += '<span class="PICTURE modeOption">';
    tr += '<input class="cmdAttr form-control input-sm" data-l1key="configuration" data-l2key="jpiResolution" placeholder="{{Résolution}}" >';
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



    tr += '<td>';
    if (is_numeric(_cmd.id)) {
        tr += '<a class="btn btn-default btn-xs cmdAction expertModeVisible" data-action="configure"><i class="fa fa-cogs"></i></a> ';
        tr += '<a class="btn btn-default btn-xs cmdAction" data-action="test"><i class="fa fa-rss"></i> {{Tester}}</a>';
    }
    tr += '<i class="fa fa-minus-circle pull-right cmdAction cursor" data-action="remove"></i></td>';
    tr += '</tr>';
    $('#table_cmd tbody').append(tr);
    $('#table_cmd tbody tr:last').setValues(_cmd, '.cmdAttr');
}
