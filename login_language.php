<?php

/**
 * Login Language
 *
 * Plugin to let the user select language before login. 
 * 
 * It is also posible to set the language with the url.
 *
 * E.g. https://example.com/?_language=en_GB 
 *
 * Copyright (C) 2014, Kent Varmedal
 *
 * @version 0.3
 * @author Kent Varmedal
 * @url https://github.com/kearva/roundcube-login-language
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see http://www.gnu.org/licenses/.
 */
 
class login_language extends rcube_plugin
{
  public $task = 'login';

  function init()
  {
    $this->add_hook('template_object_loginform', array($this, 'login_form'));
    $this->add_hook('login_after', array($this, 'login_after'));
    $this->add_hook('startup', array($this, 'startup'));
  }
  
  function startup($args)
  {
    if($args['task'] == "login" || $args['task'] == "logout") {
      $lang;
      if(!empty($_GET['_language'])) {
        $lang = $_GET['_language'];
      } elseif(!empty($_POST['_language'])) {
        $lang = $_POST['_language'];
      }
      
      if($lang) {
        $lang = preg_replace('/[^_a-zA-Z]+/','',$lang);
        $rcmail = rcmail::get_instance();
        $rcmail->config->set('language', $lang);
        $rcmail->load_language($lang);
      }
    }
  }
  
  function login_after($args)
  {
    if(!empty($_POST['_language'])) {
      $lang = $_POST['_language'];
      $lang = preg_replace('/[^_a-zA-Z]+/','',$lang);
      $rcmail = rcmail::get_instance();
      $rcmail->config->set('language', $lang);
      $rcmail->load_language($lang);
      $rcmail->user->save_prefs(array("language"=>$lang));
    }
  }
  
  function login_form($p)
  {  
    $rcmail = rcmail::get_instance();
    $langlist = $rcmail->list_languages();
    asort($langlist);

    $out = "";  
    $curlang = isset($_SESSION['language'])?$_SESSION['language']:$rcmail->user->language;
    
    foreach ($langlist as $code => $name) {
      $out .= "langselect.append($('<option />', {value: '$code', text: '$name'" . (($code == $curlang)?", selected: 'selected'":"") . "}));\n";
    }
    
    $label = $rcmail->gettext('language');
    
    $rcmail->output->add_script("
var langtr = $('<tr />');
langtr.append('<td class=\"title\"><label for=\"rcmloginlanguage\">$label</label></td>');
var langselect = $('<select />',{id: \"rcmloginlanguage\", name: \"_language\"});
langtr.append($('<td />',{class: \"input\"}).append(langselect));

$('#rcmloginpwd').parents(\"tbody\").append(langtr);
$out
", 'docready');

    return $p;
  }  
}
