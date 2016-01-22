<?php
/**
 * Bootnote Syntax Plugin: display note with different level of importance.
 *
 * @author Algorys
 */

if (!defined('DOKU_INC')) die();

class syntax_plugin_bootnote extends DokuWiki_Syntax_Plugin {

    public function getType() {
        return 'container';
    }

    /**
    * @return string Paragraph type
    **/

    public function getPType() {
        return 'normal';
    }

    // Keep syntax inside plugin
    function getAllowedTypes() {
	return array('container', 'baseonly', 'substition','protected','disabled','formatting','paragraphs');
    }

    public function getSort() {
        return 192;
    }

    function connectTo($mode) {
        if($this->getConf('bootnote.note') == 'note') {
            $this->Lexer->addEntryPattern('<note[^>]*>(?=.*</note>)', $mode,'plugin_bootnote');
        } else {
            $this->Lexer->addEntryPattern('<bootnote[^>]*>(?=.*</bootnote>)', $mode,'plugin_bootnote');
        }
    }
    
    function postConnect() {
        if($this->getConf('bootnote.note') == 'note') {
            $this->Lexer->addExitPattern("</note>", 'plugin_bootnote');
        } else {
            $this->Lexer->addExitPattern("</bootnote>", 'plugin_bootnote');
        }
    }

/**
* Do the regexp
**/
    function handle($match, $state, $pos, Doku_Handler $handler) {
        switch($state){
            case DOKU_LEXER_ENTER :
                $data = array(
                        'state'=>$state,
                        'lvl'=> "",
                    );
                // Looking for id
                if($this->getConf('bootnote.note') == 'note') {
                    $note = 'note';
                } else {
                    $note = 'bootnote';
                }
                preg_match("/$note (\\w*)/", $match, $lvl);
                if( count($lvl) != 0 ) {
                    $data['lvl'] = $lvl[1];
                }
                return $data;           
            case DOKU_LEXER_UNMATCHED :
                return array('state'=>$state, 'text'=>$match);
            default:
                return array('state'=>$state, 'bytepos_end' => $pos + strlen($match));
         }
    }

    function _render_note($renderer, $data, $glyph) {
         $renderer->doc .= '<div>'; // Global
         $renderer->doc .= '<div class="sign-container">';
         $renderer->doc .= '  <span class="sign">';
         $renderer->doc .= '    <span class="glyphicon glyphicon-'.$glyph.' s-'.$glyph.' glyph" aria-hidden="true"></span>';
         $renderer->doc .= '  </span>'; // /.sign
         $renderer->doc .= '</div>'; // /.sign-container
         $renderer->doc .= '<div class="note-container s-'.$glyph.'">';
         $renderer->doc .= '<div class="note">';
    }

    /****
    * MAIN FONCTION
    ****/
    function _define_note($renderer, $data) {
	    if($data['lvl'] == "web") {
            $glyph = "globe";
            $this->_render_note($renderer, $data, $glyph);
	    }elseif($data['lvl'] == "question") {
            $glyph = "question-sign";
            $this->_render_note($renderer, $data, $glyph);
        }elseif($data['lvl'] == "learn" || $data['lvl'] == "tip") {
            $glyph = "education";
            $this->_render_note($renderer, $data, $glyph);
         }elseif($data['lvl'] == "warning") {
            $glyph = "alert";
            $this->_render_note($renderer, $data, $glyph);
        }elseif($data['lvl'] == "critical" || $data['lvl'] == "important") {
            $glyph = "fire";
            $this->_render_note($renderer, $data, $glyph);
	    }else{
            $glyph = "info-sign";
            $this->_render_note($renderer, $data, $glyph);
        }
    }

    // Dokuwiki Renderer
    function render($mode, Doku_Renderer $renderer, $data) {
        if($mode != 'xhtml') return false;

        if($data['error']) {
            $renderer->doc .= $data['text'];
            return true;
        }
        $renderer->info['cache'] = false;
        switch($data['state']) {
            case DOKU_LEXER_ENTER :
                $this->_define_note($renderer, $data);
                break;
            case DOKU_LEXER_EXIT:
                $renderer->doc .= '</div>';// /.note
                $renderer->doc .= '<div class="triangle"></div>';
                $renderer->doc .= '</div>';// /.note-container
                $renderer->doc .= '</div>';// /Global

            case DOKU_LEXER_UNMATCHED :
                $renderer->doc .= $renderer->_xmlEntities($data['text']);
                break;
        }
        return true;
    }
}



