<?php

namespace App\Services;

use Exception;

class RouterOSService
{
    private $socket;
    private $connected = false;

    public function connect(string $host, int $port, string $username, string $password): bool
    {
        try {
            $this->socket = @fsockopen($host, $port, $errno, $errstr, 5);
            
            if (!$this->socket) {
                throw new Exception("Connection failed: $errstr ($errno)");
            }

            stream_set_timeout($this->socket, 5);
            
            // Login process
            $this->write('/login');
            $response = $this->read();
            
            if (isset($response[0]['!done'])) {
                // API login (newer RouterOS)
                $this->write('/login', false, [
                    '=name=' . $username,
                    '=password=' . $password
                ]);
            } else {
                // Legacy login
                $challenge = $response[0]['ret'];
                $md5 = md5(chr(0) . $password . pack('H*', $challenge));
                
                $this->write('/login', false, [
                    '=name=' . $username,
                    '=response=00' . $md5
                ]);
            }
            
            $response = $this->read();
            
            if (isset($response[0]['!trap'])) {
                throw new Exception('Login failed: Invalid credentials');
            }
            
            $this->connected = true;
            return true;
            
        } catch (Exception $e) {
            $this->connected = false;
            return false;
        }
    }

    public function getSystemResource(): ?array
    {
        if (!$this->connected) {
            return null;
        }

        try {
            $this->write('/system/resource/print');
            $response = $this->read();
            
            if (isset($response[0]['!re'])) {
                return $response[0];
            }
            
            return null;
        } catch (Exception $e) {
            return null;
        }
    }

    public function getInterfaces(): array
    {
        if (!$this->connected) {
            return [];
        }

        try {
            $this->write('/interface/print');
            $response = $this->read();
            
            $interfaces = [];
            foreach ($response as $item) {
                if (isset($item['!re'])) {
                    $interfaces[] = $item;
                }
            }
            
            return $interfaces;
        } catch (Exception $e) {
            return [];
        }
    }

    public function disconnect(): void
    {
        if ($this->socket) {
            fclose($this->socket);
            $this->connected = false;
        }
    }

    private function write(string $command, bool $tag = true, array $params = []): void
    {
        $data = [];
        $data[] = strlen($command) . chr(0) . $command;
        
        foreach ($params as $param) {
            $data[] = strlen($param) . chr(0) . $param;
        }
        
        if ($tag) {
            $data[] = chr(0);
        }
        
        foreach ($data as $line) {
            fwrite($this->socket, $line);
        }
    }

    private function read(): array
    {
        $response = [];
        $current = [];
        
        while (true) {
            $line = $this->readLine();
            
            if ($line === false || $line === '') {
                if (!empty($current)) {
                    $response[] = $current;
                }
                break;
            }
            
            if (strpos($line, '!') === 0) {
                if (!empty($current)) {
                    $response[] = $current;
                }
                $current = [$line => true];
            } else {
                $parts = explode('=', $line, 3);
                if (count($parts) >= 3) {
                    $current[$parts[1]] = $parts[2];
                }
            }
        }
        
        return $response;
    }

    private function readLine(): string
    {
        $line = '';
        $length = $this->readLength();
        
        if ($length === false || $length === 0) {
            return '';
        }
        
        while (strlen($line) < $length) {
            $line .= fread($this->socket, $length - strlen($line));
        }
        
        return $line;
    }

    private function readLength()
    {
        $byte = ord(fread($this->socket, 1));
        
        if ($byte === 0) {
            return 0;
        }
        
        if ($byte < 0x80) {
            return $byte;
        }
        
        if ($byte < 0xC0) {
            return (($byte & 0x3F) << 8) + ord(fread($this->socket, 1));
        }
        
        if ($byte < 0xE0) {
            return (($byte & 0x1F) << 16) + (ord(fread($this->socket, 1)) << 8) + ord(fread($this->socket, 1));
        }
        
        if ($byte < 0xF0) {
            return (($byte & 0x0F) << 24) + (ord(fread($this->socket, 1)) << 16) + (ord(fread($this->socket, 1)) << 8) + ord(fread($this->socket, 1));
        }
        
        return ord(fread($this->socket, 1)) << 24 + ord(fread($this->socket, 1)) << 16 + ord(fread($this->socket, 1)) << 8 + ord(fread($this->socket, 1));
    }
}
