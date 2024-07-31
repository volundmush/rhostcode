<?php
class AnsiState {
    public $foreground = 7;
    public $background = 0;
    public $bold = false;
    public $underscore = false;
    public $blink = false;
    public $reverse = false;

    public function isStateReset() {
        return !$this->bold && !$this->underscore && !$this->blink && $this->foreground == 7 && $this->background == 0;
    }

    public function update($command) {
        $changed = false;
        if (substr($command, -1) == "m") {
            $parts = explode(";", substr($command, 0, -1));
            foreach ($parts as $part) {
                $num = (int)$part;

                // Reset
                if ($num == 0) {
                    if(!$this->isStateReset()) {
                        $changed = true;
                        $this->foreground = 7;
                        $this->background = 0;
                        $this->bold = false;
                        $this->underscore = false;
                        $this->blink = false;
                        $this->reverse = false;
                    }
                } elseif ($num == 1) {
                    if(!$this->bold) {
                        $changed = true;
                        $this->bold = true;
                    }
                } elseif ($num == 4) {
                    if(!$this->underscore) {
                        $changed = true;
                        $this->underscore = true;
                    }
                } elseif ($num == 5) {
                    if(!$this->blink) {
                        $changed = true;
                        $this->blink = true;
                    }
                } elseif ($num == 7) {
                    if(!$this->reverse) {
                        $changed = true;
                        $this->reverse = true;
                    }
                // 16 color FG
                } elseif ($num >= 30 && $num <= 37) {
                    if($this->foreground != $num - 30) {
                        $changed = true;
                        $this->foreground = $num - 30;
                    }
                // 16 color BG
                } elseif ($num >= 40 && $num <= 47) {
                    if($this->background != $num - 40) {
                        $changed = true;
                        $this->background = $num - 40;
                    }
                // Extended FG color
                } elseif ($num == 38) {
                    if((int)$parts[1] == 5) {
                        $color = (int)$parts[2];
                        if($this->foreground != $color) {
                            $changed = true;
                            $this->foreground = $color;
                        }
                    }
                // Extended BG color
                } elseif ($num == 48) {
                    // 256 color
                    if((int)$parts[1] == 5) {
                        $color = (int)$parts[2];
                        if($this->background != $color) {
                            $changed = true;
                            $this->background = $color;
                        }
                    }
                }
            }
        }
        return $changed;

    }

    public function renderHtml() {
        $ret = '<span class="';
        $classes = [];

        if ($this->underscore) {
            $classes[] = "underline";
        }
        if ($this->blink) {
            $classes[] = " flash";
        }

        $fg = $this->foreground;
        if($this->bold) {
            $fg += 8;
        }
        $bg = $this->background;

        if ($this->reverse) {
            $classes[] = "fg_" . $bg;
            $classes[] = "bg_" . $fg;
        } else {
            $classes[] = "fg_" . $fg;
            $classes[] = "bg_" . $bg;
        }
        $ret .= implode(" ", $classes);
        $ret .= '">';
        return $ret;
    }
}


function ansiToSpans($text) {
    global $colorTable;

    $len = strlen($text);
    $ret = "";
    $offset = 0;
    $state = new AnsiState();

    $ret .= $state->renderHtml();

    while ($offset < $len) {
        $byte = $text[$offset];

        if ($byte === "\e") {
            if ($offset + 1 < $len && $text[$offset + 1] === "[") {
                $offset += 2;
                $command = "";

                // now we'll find everything up to the 'm' character.
                $terminate = $offset;
                while ($terminate < $len) {
					$char = $text[$terminate];
					if($char == "m") {
						break;
					}
                    $terminate++;
                }
                // We need to yoink everything from $offset to $terminate including the m.
                $command = substr($text, $offset, $terminate - $offset + 1);
                $offset = $terminate + 1;

                if($state->update($command)) {
                    $ret .= "</span>";
                    $ret .= $state->renderHtml();
                }
                continue;
            }
        } elseif ($byte === "&") {
            $ret .= "&amp;";
        } elseif ($byte === "<") {
            $ret .= "&lt;";
        } elseif ($byte === ">") {
            $ret .= "&gt;";
        } elseif ($byte === '"') {
            $ret .= "&quot;";
        } else {
            $ret .= $byte;
        }

        $offset++;
    }

    $ret .= "</span>";

    return $ret;
}

$patterns = [
            '/%cf/' => "\e[5m",    // flash
            '/%ci/' => "\e[7m",    // inverse
            '/%ch/' => "\e[1m",    // hilite (bold)
            '/%cn/' => "\e[0m",    // normal (reset)
            '/%cu/' => "\e[4m",    // underscore

            '/%cx/' => "\e[30m",   // black foreground
            '/%cX/' => "\e[40m",   // black background
            '/%cr/' => "\e[31m",   // red foreground
            '/%cR/' => "\e[41m",   // red background
            '/%cg/' => "\e[32m",   // green foreground
            '/%cG/' => "\e[42m",   // green background
            '/%cy/' => "\e[33m",   // yellow foreground
            '/%cY/' => "\e[43m",   // yellow background
            '/%cb/' => "\e[34m",   // blue foreground
            '/%cB/' => "\e[44m",   // blue background
            '/%cm/' => "\e[35m",   // magenta foreground
            '/%cM/' => "\e[45m",   // magenta background
            '/%cc/' => "\e[36m",   // cyan foreground
            '/%cC/' => "\e[46m",   // cyan background
            '/%cw/' => "\e[37m",   // white foreground
            '/%cW/' => "\e[47m"    // white background
    ];

function markupToANSI($line) {
    global $patterns;
    $line = preg_replace(array_keys($patterns), array_values($patterns), $line);

    // Handle xterm256 colors
    $line = preg_replace_callback('/%c0x([0-9a-f]{2})/', function ($matches) {
        return "\e[38;5;" . hexdec($matches[1]) . "m";
    }, $line);

    $line = preg_replace_callback('/%c0X([0-9a-f]{2})/', function ($matches) {
        return "\e[48;5;" . hexdec($matches[1]) . "m";
    }, $line);

    // Handle unicode
    $line = preg_replace_callback('/%<u([0-9a-f]{4})>/', function ($matches) {
        return mb_convert_encoding('&#x' . $matches[1] . ';', 'UTF-8', 'HTML-ENTITIES');
    }, $line);

    return $line;
}

function convertRhost($line) {
    return ansiToSpans(markupToANSI($line));
}
?>