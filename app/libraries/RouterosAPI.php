<?php
/**
 * RouterOS API client class.
 * Standard implementation for PHP.
 */
class RouterosAPI {
    public $debug = false;
    public $connected = false;
    public $port = 8728;
    public $timeout = 10;
    public $attempts = 3;
    public $delay = 2;

    private $socket;
    private $error_no;
    private $error_str;

    public function connect($ip, $login, $password) {
        for ($ATTEMPT = 1; $ATTEMPT <= $this->attempts; $ATTEMPT++) {
            $this->connected = false;
            $this->socket = @fsockopen($ip, (int)$this->port, $this->error_no, $this->error_str, (int)$this->timeout);
            if ($this->socket) {
                socket_set_timeout($this->socket, $this->timeout);
                $this->write('/login', false);
                $this->write('=name=' . $login, false);
                $this->write('=password=' . $password);
                $RESPONSE = $this->read(false);
                
                if (isset($RESPONSE[0]) && $RESPONSE[0] == '!done') {
                    if (isset($RESPONSE[1])) {
                        // For older RouterOS
                        $MATCHES = array();
                        if (preg_match_all('/[^=]+/i', $RESPONSE[1], $MATCHES)) {
                            if ($MATCHES[0][0] == 'ret' && strlen($MATCHES[0][1]) == 32) {
                                $this->write('/login', false);
                                $this->write('=name=' . $login, false);
                                $this->write('=response=00' . md5(chr(0) . $password . pack('H*', $MATCHES[0][1])));
                                $RESPONSE = $this->read(false);
                                if (isset($RESPONSE[0]) && $RESPONSE[0] == '!done') {
                                    $this->connected = true;
                                    break;
                                }
                            }
                        }
                    } else {
                        // RouterOS > 6.43
                        $this->connected = true;
                        break;
                    }
                }
                fclose($this->socket);
            }
            sleep($this->delay);
        }
        return $this->connected;
    }

    public function disconnect() {
        if ($this->socket) {
            fclose($this->socket);
        }
        $this->connected = false;
    }

    private function encodeLength($length) {
        if ($length < 0x80) {
            $length = chr($length);
        } elseif ($length < 0x4000) {
            $length |= 0x8000;
            $length = chr(($length >> 8) & 0xFF) . chr($length & 0xFF);
        } elseif ($length < 0x200000) {
            $length |= 0xC00000;
            $length = chr(($length >> 16) & 0xFF) . chr(($length >> 8) & 0xFF) . chr($length & 0xFF);
        } elseif ($length < 0x10000000) {
            $length |= 0xE0000000;
            $length = chr(($length >> 24) & 0xFF) . chr(($length >> 16) & 0xFF) . chr(($length >> 8) & 0xFF) . chr($length & 0xFF);
        } elseif ($length >= 0x10000000) {
            $length = chr(0xF0) . chr(($length >> 24) & 0xFF) . chr(($length >> 16) & 0xFF) . chr(($length >> 8) & 0xFF) . chr($length & 0xFF);
        }
        return $length;
    }

    public function write($command, $param2 = true) {
        if ($command) {
            $data = explode("\n", $command);
            foreach ($data as $com) {
                $com = trim($com);
                fwrite($this->socket, $this->encodeLength(strlen($com)) . $com);
            }
            if ($this->debug) {
                echo "<pre>WRITE:\n" . print_r($data, true) . "</pre>";
            }
        }
        if ($param2) {
            fwrite($this->socket, chr(0));
        }
    }

    public function read($parse = true) {
        $RESPONSE = array();
        while (true) {
            $BYTE = ord(fread($this->socket, 1));
            $LENGTH = 0;
            if ($BYTE & 0x80) {
                if (($BYTE & 0xC0) == 0x80) {
                    $LENGTH = (($BYTE & 0x3F) << 8) + ord(fread($this->socket, 1));
                } else {
                    if (($BYTE & 0xE0) == 0xC0) {
                        $LENGTH = (($BYTE & 0x1F) << 8) + ord(fread($this->socket, 1));
                        $LENGTH = ($LENGTH << 8) + ord(fread($this->socket, 1));
                    } else {
                        if (($BYTE & 0xF0) == 0xE0) {
                            $LENGTH = (($BYTE & 0x0F) << 8) + ord(fread($this->socket, 1));
                            $LENGTH = ($LENGTH << 8) + ord(fread($this->socket, 1));
                            $LENGTH = ($LENGTH << 8) + ord(fread($this->socket, 1));
                        } else {
                            $LENGTH = ord(fread($this->socket, 1));
                            $LENGTH = ($LENGTH << 8) + ord(fread($this->socket, 1));
                            $LENGTH = ($LENGTH << 8) + ord(fread($this->socket, 1));
                            $LENGTH = ($LENGTH << 8) + ord(fread($this->socket, 1));
                        }
                    }
                }
            } else {
                $LENGTH = $BYTE;
            }

            $_ = "";
            if ($LENGTH > 0) {
                $retlen = 0;
                while ($retlen < $LENGTH) {
                    $toread = $LENGTH - $retlen;
                    $_ .= fread($this->socket, $toread);
                    $retlen = strlen($_);
                }
                $RESPONSE[] = $_;
            }

            if ($_ == "!done" || $_ == "!fatal") {
                break;
            }
        }

        if ($this->debug) {
            echo "<pre>READ:\n" . print_r($RESPONSE, true) . "</pre>";
        }

        if ($parse) {
            return $this->parseResponse($RESPONSE);
        } else {
            return $RESPONSE;
        }
    }

    private function parseResponse($response) {
        $parsed = array();
        $current = null;
        foreach ($response as $line) {
            if ($line === '!re') {
                if ($current !== null) {
                    $parsed[] = $current;
                }
                $current = array();
            } elseif ($line === '!trap') {
                if ($current !== null) {
                    $parsed[] = $current;
                }
                $current = array('!trap' => true);
            } elseif ($line === '!done' || $line === '!fatal') {
                if ($current !== null) {
                    $parsed[] = $current;
                }
                break;
            } elseif (strpos($line, '=') === 0) {
                $parts = explode('=', substr($line, 1), 2);
                if (count($parts) == 2 && $current !== null) {
                    $current[$parts[0]] = $parts[1];
                }
            }
        }
        return $parsed;
    }

    public function comm($com, $arr = array()) {
        $count = count($arr);
        $this->write($com, !$count);
        $i = 0;
        foreach ($arr as $k => $v) {
            $el = (preg_match('/^\?/', $k) || preg_match('/^\-/', $k)) ? "$k=$v" : "=$k=$v";
            $this->write($el, ($i++ == $count - 1));
        }
        return $this->read();
    }
}
