<?php
/**
 * Calss to perform simple Text Diff
 * Based on https://github.com/paulgb/simplediff/blob/master/php/simplediff.php
 * 
 */

class SimpleDiff {
    public $raw_old;
    public $raw_new;
    protected $tok_old;
    protected $tok_new;

    public function get_tok_old() {
        if (empty($this->tok_old)) $this->set_tok_old();
        return $this->tok_old;
    }

    public function set_tok_old() {
        $this->tok_old = preg_split("/[\s]+/", $this->raw_old);
    }

    public function get_tok_new() {
        if (empty($this->tok_new)) $this->set_tok_new();
        return $this->tok_new;
    }

    public function set_tok_new() {
        $this->tok_new = preg_split("/[\s]+/", $this->raw_new);
    }


    public function __construct($raw_old, $raw_new)
    {
        $this->raw_old = $raw_old;
        $this->raw_new = $raw_new;
        $this->set_tok_old();
        $this->set_tok_new();
    }


    /*
    Paul's Simple Diff Algorithm v 0.1
    (C) Paul Butler 2007 <http://www.paulbutler.org/>
    May be used and distributed under the zlib/libpng license.
    
    This code is intended for learning purposes; it was written with short
    code taking priority over performance. It could be used in a practical
    application, but there are a few ways it could be optimized.
    
    Given two arrays, the function diff will return an array of the changes.
    I won't describe the format of the array, but it will be obvious
    if you use print_r() on the result of a diff on some test data.
    
    htmlDiff is a wrapper for the diff command, it takes two strings and
    returns the differences in HTML. The tags used are <ins> and <del>,
    which can easily be styled with CSS.  
*/

static function diff($old, $new){
    $matrix = array();
    $maxlen = 0;
    foreach($old as $oindex => $ovalue){
        $nkeys = array_keys($new, $ovalue);
        foreach($nkeys as $nindex){
            $matrix[$oindex][$nindex] = isset($matrix[$oindex - 1][$nindex - 1]) ?
                $matrix[$oindex - 1][$nindex - 1] + 1 : 1;
            if($matrix[$oindex][$nindex] > $maxlen){
                $maxlen = $matrix[$oindex][$nindex];
                $omax = $oindex + 1 - $maxlen;
                $nmax = $nindex + 1 - $maxlen;
            }
        }   
    }
    if($maxlen == 0) return array(array('d'=>$old, 'i'=>$new));
    return array_merge(
        self::diff(array_slice($old, 0, $omax), array_slice($new, 0, $nmax)),
        array_slice($new, $nmax, $maxlen),
        self::diff(array_slice($old, $omax + $maxlen), array_slice($new, $nmax + $maxlen)));
}

public function html_diff(){
    $ret = '';
    $diff = self::diff($this->get_tok_old(), $this->get_tok_new());
    foreach($diff as $k){
        if(is_array($k))
            $ret .= (!empty($k['d'])?"<del>".implode(' ',$k['d'])."</del> ":'').
                (!empty($k['i'])?"<ins>".implode(' ',$k['i'])."</ins> ":'');
        else $ret .= $k . ' ';
    }
    return $ret;
}

public function inserted_only() {
    $ret = '';
    $diff = self::diff($this->get_tok_old(), $this->get_tok_new());
    foreach($diff as $k){
        if(is_array($k))
            $ret .= (!empty($k['i'])?implode(' ',$k['i']):'');
    }
    return $ret;
}


public function deleted_only() {
    $ret = '';
    $diff = self::diff($this->get_tok_old(), $this->get_tok_new());
    foreach($diff as $k){
        if(is_array($k))
            $ret .= (!empty($k['d'])?implode(' ',$k['d']):'');
    }
    return $ret;
}

public function unchanged_only() {
    $ret = '';
    $diff = self::diff($this->get_tok_old(), $this->get_tok_new());
    foreach($diff as $k){
        if(is_array($k) == false) $ret .= $k . ' ';
    }
    return $ret;
}

}

//$d = new SimpleDiff($old, $new);
//var_dump($d->inserted_only());
