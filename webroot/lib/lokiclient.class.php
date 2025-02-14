<?php
/**
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see http://www.gnu.org/licenses/gpl-3.0.
 *
 * pre 2023 https://github.com/tuxmainy
 * 2024 https://www.bananas-playground.net/projekt/portagefilelist/
 */


/**
 * Send log messages with optional structured data to Loki using the http api
 * https://grafana.com/docs/loki/latest/reference/loki-http-api/
 */
class Loki {

    /**
     * @var string The host Loki runs on
     */
    private string $_host;

    /**
     * @var int The port number from the Loki installation
     */
    private int $_port;

    /**
     * @var array The stream lables. See Loki http api
     */
    private array $_stream = array();

    /**
     * @var array The log messages as an array to be send with send()
     */
    private array $_values = array();

    /**
     * @var string basic auth for endpoint
     */
    private string $_auth;

    /**
     * @param string $host Loki host
     * @param int $port Loko host port
     * @param array $stream Stream array with labels to send with
     */
    public function __construct(string $host, int $port, array $stream) {
        $this->_stream = $stream;
        $this->_host = $host;
        $this->_port = $port;

        $this->_auth = base64_encode(LOKI_USER.":".LOKI_USER_PW);
    }

    /**
     * Add a log message with optional structured data to the values to be send()
     *
     * @param string $msg The log message
     * @param array $structuredData Additional structured data to be processed from Loki if configured
     * @return void
     */
    public function log(string $msg, array $structuredData=array()): void {
        $_nanosec = strval(shell_exec("date +%s%9N")-1);
        if(!empty($structuredData)) {
            $this->_values[] = array($_nanosec, $msg, $structuredData);
        } else {
            $this->_values[] = array($_nanosec, $msg);
        }
    }

    /**
     * Send the collected messages from log() to the loki installation
     * The messages to be send will be resetted after
     *
     * @return string Non empty on error
     */
    public function send(): string {
        $ret = "";

        if(!LOKI_ENABLE) return $ret;

        $data = array(
            "streams" => array(
                array(
                    "stream" => $this->_stream,
                    "values" => $this->_values
                )
            )
        );

        $data = json_encode($data);
        $out = "POST ".LOKI_PUSH_API." HTTP/1.1\r\n";
        $out .= "Host: $this->_host\r\n";
        $out .= "Authorization: Basic $this->_auth\r\n";
        $out .= "Content-Type: application/json\r\n";
        $out .= "Content-Length: ".strlen($data)."\r\n";
        $out .= "Connection: Close\r\n\r\n";

        $fp = fsockopen($this->_host, $this->_port, $errno, $errstr, 5);
        if($fp) {
            fwrite($fp, $out);
            fwrite($fp, $data."\r\n");
            fclose($fp);
        } else {
            $ret = $errno.' '.$errstr;
        }

        # reset
        $this->_values = array();

        return $ret;
    }
}
