<?php

class rds {

    var $servers = array();
    var $timeout = 10;
    var $rdb;
    private $conn;

    function __construct($servers) {
        $this->servers = $servers;
    }

    private function _conn() {
        if (!isset($this->conn)) {
            $this->conn = new Redis();
            $this->conn->connect($this->servers[0], $this->servers[1]);
        }
        return $this->conn;
    }

    private function _exists($k, $func = '') {
        if ($func == -1)
            return true;
        if (!$this->_conn()->exists($k)) {
            if (empty($func) or $this->exists($k . '_null'))
                return false;
            else
                return $this->_calc($k, $func);
        }
        return true;
    }

    private function _calc($k, $func) {
        $out = array();
        if ($this->_conn()->setnx($k . 'b', 1)) {
            $this->_conn()->expire($k . 'b', $this->timeout + 1);
            //echo 'долгая операция выполняется';
            //echo $func;
            eval($func);
            /*
              $fp = fopen('logs/rds_calc.txt','a');
              fwrite($fp,$k.' '.$func."\n");
              fclose($fp);
             */

            $this->sset($k, $out);
            $this->_conn()->del($k . 'b');
            return true;
        } else {
            for ($i = 0; $i < $this->timeout * 2; $i++) {
                //echo 'ждём ';
                usleep(500000);
                if (!$this->_conn()->exists($k . 'b'))
                    return true;
            }
            echo 'function ' . $func . ' is too slow';
            return false;
        }
    }

    public function expire($k, $ex) {
        $this->_conn()->expire($k, $ex);
    }

    public function zset($k, $arr) {
        $this->_conn()->zrem($k);
        if (empty($arr)) {
            $this->_conn()->set($k . '_null', 1);
        } else {
            foreach ($arr as $n => $m) {
                $this->_conn()->zadd($k, $m, $n);
            }
            $this->_conn()->del($k . '_null');
        }
        if ($ex) {
            $this->_conn()->expire($k, $ex);
        }
    }

    public function sset($k, $arr, $ex = 0) {
        $this->_conn()->del($k);
        if (empty($arr)) {
            $this->_conn()->set($k . '_null', 1);
        } else {
            foreach ($arr as $v) {
                $this->_conn()->sadd($k, $v);
            }
            $this->_conn()->del($k . '_null');
        }
        if ($ex) {
            $this->_conn()->expire($k, $ex);
        }
    }

    // добавить в последовательность
    public function sadd($k, $v) {
        return $this->_conn()->sadd($k, $v);
    }

    // добавить в последовательность
    public function ladd($k, $v) {
        return $this->_conn()->lpush($k, $v);
    }

    public function saddn($k, $v) {
        if ($this->_conn()->exists($k)) {
            $this->_conn()->sadd($k, $v);
        } elseif ($this->_conn()->exists($k . '_null')) {
            $this->_conn()->sadd($k, $v);
            $this->_conn()->del($k . '_null');
        }
    }

    public function srem($k, $v, $null_marker = true) {
        if ($this->_conn()->srem($k, $v) and $null_marker and $this->cnt($k) == 0) {
            $this->_conn()->set($k . '_null', 1);
        }
    }

    public function sget($k, $func = '') {
        if (!$this->_exists($k, $func))
            return array();
        return $this->_conn()->smembers($k);
    }

    public function del($k) {
        $this->_conn()->del($k);
        $this->_conn()->del($k . '_null');
    }

    public function in($k, $v, $func = '') {
        if (!$this->_exists($k, $func))
            return false;
        return $this->_conn()->sismember($k, $v) ? 1 : 0;
    }

    public function cnt($k, $func = '') {
        if (!$this->_exists($k, $func))
            return 0;
        return $this->_conn()->scard($k);
    }

    public function rnd($k, $n, $func = '') {
        $cnt = $this->cnt($k, $func);
        if ($cnt <= $n) {
            $out = $this->sget($k, $func);
            shuffle($out);
            return $out;
        } else {
            $out = array();
            while (count($out) < $n) {
                $t = $this->_conn()->srandmember($k);
                if (!in_array($t, $out))
                    $out[] = $t;
            }
            return $out;
        }
    }

    public function dec($k, $v = 1) {
        if ($v == 1)
            return $this->_conn()->decr($k);
        return $this->_conn()->decrby($k, $v);
    }

    public function inc($k, $v = 1) {
        if ($v == 1)
            return $this->_conn()->incr($k);
        return $this->_conn()->incrby($k, $v);
    }

    public function info() {
        return $this->_conn()->info();
    }

    public function delall() {
        $this->_conn()->flushdb();
    }

    public function sinter($k1, $k2, $f1 = '', $f2 = '') {
        if (!$this->_exists($k1, $f1))
            return false;
        if (!$this->_exists($k2, $f2))
            return false;
        return $this->_conn()->sinter($k1, $k2);
    }

    public function sdiff($k1, $k2, $f1 = '', $f2 = '') {
        if (!$this->_exists($k1, $f1))
            return false;
        if (!$this->_exists($k2, $f2))
            return false;
        return $this->_conn()->sdiff($k1, $k2);
    }

    public function sinterstore($k0, $k1, $k2, $f1 = '', $f2 = '') {
        if (!$this->_exists($k1, $f1))
            return false;
        if (!$this->_exists($k2, $f2))
            return false;
        return $this->_conn()->sinterstore($k0, $k1, $k2);
    }

    public function __call($method, $args) {
        //print_r($args);
        $conn = $this->_conn();
        if (method_exists($conn, $method)) {
            return call_user_func_array(array($conn, $method), $args);
        } else
            throw new InvalidArgumentException("Method {$method} doesn't exist");
        /*
          print_r($method);

          if (preg_match('/^(g)([A-Z])(.*)$/', $method, $match)) {
          $property = trim(strtolower($match[2]). $match[3]);
          if(isset(self::$arr[$property])){
          return self::$arr[$property];
          } else
          }
         */
    }

}

?>
