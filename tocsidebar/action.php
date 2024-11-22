<?php

use dokuwiki\Extension\ActionPlugin;
use dokuwiki\Extension\EventHandler;
use dokuwiki\Extension\Event;

error_log('DEBUG_ACTION: Funktion action wurde aufgerufen.');

/**
 * DokuWiki Plugin tocsidebar (Action Component)
 *
 * @license GPL 2 http://www.gnu.org/licenses/gpl-2.0.html
 * @author Tim Droste <Tim.droste03@gmail.com>
 */
if(!defined('DOKU_INC')) die();

if(!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN',DOKU_INC.'lib/plugins/');
require_once(DOKU_PLUGIN.'action.php');
define ("TOC_URL", DOKU_BASE ."doku.php?id=");
define ("TOCSEL_IMGDIR", DOKU_BASE . 'lib/plugins/tocsidebar/img/');
class action_plugin_tocsidebar extends ActionPlugin
{
    /** @inheritDoc */
    public function register(EventHandler $controller)
    {
        error_log('DEBUG_ACTION: Funktion register wurde aufgerufen.');
        $controller->register_hook('AJAX_CALL_UNKNOWN', 'BEFORE', $this, '_ajax_call');
        $controller->register_hook('DOKUWIKI_STARTED', 'BEFORE', $this, 'handle_started');
    }

    /**
     * Event handler for EXAMPLE_EVENT
     *
     * @see https://www.dokuwiki.org/devel:events:EXAMPLE_EVENT
     * @param Event $event Event object
     * @param mixed $param optional parameter passed when event was registered
     * @return void
     */
    function handle_started(Doku_Event $event, $param)
    {
        error_log('DEBUG_ACTION: Entering function handlestart');
        global $conf;
        if ($this->getConf('notoc')) {
            $conf['tocminheads'] = 0;
        }
    }

    function _ajax_call(Doku_Event $event, $param)
    {

        global $INPUT;
        error_log('DEBUG_ACTION: Entering function ajaxcall');

        if ($event->data == 'tocsidebar') {
            $event->stopPropagation();
            $event->preventDefault();
            $wikifn = rawurldecode($INPUT->str('seltoc_val'));
            error_log($INPUT->str('seltoc_val'));
            $regex = preg_quote(':*');
            if (preg_match('/^(.*?)' . $regex . '\s*$/', $wikifn, $matches)) {
                $wikifn = $matches[1];
                $ns = getNS($wikifn . ':file');
                $pathinf = pathinfo(wikiFN($wikifn . ':file'));
                if ($matches[1]) {
                    $this->up =  $this->get_up_dir($pathinf);  //inserted in get_dir_list()
                }
                $list =  $this->get_dir_list($pathinf['dirname'], $ns);
                echo $list;
                return;
            } else   $file = wikiFN($wikifn);

            $exists =  file_exists($file);
            if ($exists &&  auth_quickaclcheck($wikifn)) {
                setcookie('tocsidebar', $wikifn, 0, DOKU_BASE);
                $this->ul_count =  $this->ul_open = $this->ul_closed = 0;
                $this->get_toc($wikifn);
                if ($this->retv) {
                    echo $this->retv;
                } else {
                    $up =  $this->get_up_dir(pathinfo("$file/file"));
                }
            } else {
                if ($exists && !auth_quickaclcheck($wikifn)) {
                    echo $this->getLang('perm');
                }
            }
        }
    }
    function get_toc($id)
    {
        error_log('DEBUG_ACTION: Entering function get toc');
        $this->retv = "";
        $toc = p_get_metadata($id, 'description tableofcontents');
        if (!$toc) return "";
        $current = 0;
        $start_level = 0;
        $max_depth = 2; // Maximale Überschriftebene, die angezeigt werden soll

        $this->ulcount('open');
        $up = $this->get_up_dir(pathinfo(wikiFN("$id:file")));
        $this->retv .= "<UL class='tocsel_li1'>$up\n";

        foreach ($toc as $head) {
            // Filter nach maximaler Tiefe
            if ($head['level'] > $max_depth) {
                continue;
            }

            $level = $this->format_item($head, $current, $id);
            if ($start_level == 0) $start_level = $level;
        }

        if ($start_level != $level) {
            $this->retv .= "</UL>\n";
            $this->ulcount('closed');
        }
        $this->retv .= "</UL>\n";
        $this->ulcount('closed');

        if ($this->ul_open > $this->ul_closed) {
            $this->retv .= "</UL>\n";
        }
    }


    function format_item($h, &$n, $id)
    {
        error_log('DEBUG_ACTION: Entering function format item');
        if ($n == 0) $n = $h['level'];

        if ($n < $h['level']) {
            $this->ulcount('open');
            $this->retv .= "<UL>\n";
        } else if ($n != $h['level']) {
            $this->retv .= "</UL>\n";
            $this->ulcount('closed');
        }

        $this->retv .=    '<li>' . $this->format_link($h['title'], $h['hid'], $id) . "</li>\n";
        $n = $h['level'];
        return $n;
    }
    function format_link($title, $anchor, $id)
    {
        error_log('DEBUG_ACTION: Entering function format link');

        $link = "<a href ='" . TOC_URL  . $id . '#' . $anchor . "'>" . "$title</a>";
        return $link;
    }

    function ulcount($which)
    {

        if ($which == "open") {
            if ($this->ul_open > 0) $this->retv .= "\n" . '<li class="ihidden">';
            $this->ul_count++;
            $this->ul_open++;
        } else if ($which == "closed") {
            $this->ul_count--;
            if ($this->ul_closed > 0) $this->retv .= "</li>\n";
            $this->ul_closed++;
        }
    }

    private function get_dir_list($dir, $namespace)
    {
        $retdir = "<UL>";
        if (!empty($this->up)) $retdir .= $this->up;
        $retfile = "";
        $dir_ar = array();
        $file_ar = array();

        $dh = opendir($dir);
        if (!$dh) return;
        while (($file = readdir($dh)) !== false) {
            if ($file == '.' || $file == '..') continue;           # cur and upper dir
            if (is_dir("$dir/$file")) {
                $dir_ar[$file] = $this->handle_directory($file, $namespace);
            } else {
                if (!preg_match("/\.txt$/", $file) || preg_match("/^_/", $file)) continue;  //exclude non .txt files and templates
                $file_ar[$file] =  $this->handle_file($file, $namespace);
            }
        }
        closedir($dh);
        ksort($dir_ar);
        ksort($file_ar);
        foreach ($dir_ar as $key => $val) {
            $retdir .= $val;
        }
        foreach ($file_ar as $key => $val) {
            $retfile .= $val;
        }
        $ret = $retdir . $retfile  . "</UL>";
        return $ret;
    }

    private function handle_directory($curdir, $namespace)
    {
        return "<li><span  class='clickerdir  tocselb' onclick=\"tocsel_updatetoc('$namespace:$curdir:*');\">$namespace:$curdir:*</span></li>";
    }

    private function handle_file($file, $namespace)
    {
        $file = preg_replace("/\.txt$/", "", $file);
        return "<li><span  class='clickerfile' onclick=\"tocsel_updatetoc('$namespace:$file');\">$namespace:$file</span></li>";
    }

    private function handle_up($namespace)
    {
        if (empty($namespace))
            $title = 'Root NS';
        else  $title = $namespace;
        $png = '<img title = "' . $title . '"src = "' . TOCSEL_IMGDIR . 'up.png' . '" />';
        return "<li class= 'tocsel_up'><span  class='clicker  tocselb' onclick=\"tocsel_updatetoc('$namespace:*');\">$png</span></li>  ";
    }

    private function get_up_dir($pathinf)
    {
        $up = dirname($pathinf['dirname']);
        $up = preg_replace("#.*?/data/pages#", "", $up);
        $up = str_replace('/', ':',  $up);
        return $this->handle_up($up);   // empty $up = root ns
    }
}
