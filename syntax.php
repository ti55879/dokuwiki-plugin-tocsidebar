<?php

use dokuwiki\Extension\SyntaxPlugin;

/**
 * DokuWiki Plugin tocsidebar (Syntax Component)
 *
 * @license GPL 2 http://www.gnu.org/licenses/gpl-2.0.html
 * @author Tim Droste <Tim.droste03@gmail.com>
 */
if(!defined('DOKU_INC')) die();

if(!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN',DOKU_INC.'lib/plugins/');
define('TOCSEL_DIR', DOKU_BASE . 'lib/plugins/tocsidebar/');
require_once(DOKU_PLUGIN.'syntax.php');

/**
 * All DokuWiki plugins to extend the parser/rendering mechanism
 * need to inherit from this class
 */

class syntax_plugin_tocsidebar extends SyntaxPlugin
{
    function getType(){
error_log('DEBUG_SYNTAX: Entering function getType');
        return 'substition';
    }

    /**
     * Where to sort in?
     */ 
    function getSort(){
error_log('DEBUG_SYNTAX: Entering function getSort');
        return 155;
    }
    function getPType() {
error_log('DEBUG_SYNTAX: Entering function getPType');
        return 'block';
    }

    /** @inheritDoc */
    public function connectTo($mode)
    {
        $this->Lexer->addSpecialPattern('~~PASTETOC>curID~~', $mode, 'plugin_tocsidebar');
    }

//    /** @inheritDoc */
//    public function postConnect()
//    {
//        $this->Lexer->addExitPattern('</FIXME>', 'plugin_tocsidebar');
//    }

    /** @inheritDoc */
    public function handle($match, $state, $pos, Doku_Handler $handler)
    {
        error_log('DEBUG_SYNTAX: Entering function handle');
        $handler->_addCall('notoc',array(),$pos);
        if(preg_match('/curID/', $match)) {
            $match = 'curID';               
        }
        else $match = 'wiki:id';
         
        return array($state,$match);
    }

    /** @inheritDoc */
    public function render($mode, Doku_Renderer $renderer, $data)
    {
        if($mode == 'xhtml'){           
            error_log('DEBUG_SYNTAX: Entering function render');
            global $lang;
            $select = $this->getLang('select');
            $nsroot = $this->getLang('nsroot');
            $rootopen = $this->getLang('rootopen');
            list($state,$wikid) = $data;
            error_log($wikid);
            $renderer->doc .='<div class="tocsel_right">';
            $renderer->doc .=  '<DIV><FORM><input type="button" value="' . $select. '" id="selectoc_btn"  name="selectoc_btn" /> <INPUT  type="text" title="wiki:id" id="selectoc_id" name="selectoc_id" value="'.$wikid .'"></FORM></DIV>';
            $renderer->doc .= '<div id="tocseltoggle"></div ><span class="tocsel_title">Inhaltsverzeichnis</span><div id = "setctoc_out"></div>';
            $renderer->doc .='</div>';
            return true;
           }        
           return false;       
    }
}
