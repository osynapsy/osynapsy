<?php
namespace Osynapsy\Helper\Net\Imap;

/**
 * Description of ImapClient
 *
 * @author Pietro
 */
class Client
{
    private $connectionParameters = [];
    private $connection;

    public function __construct($username, $password, $host, $port = 143, $secure = null)
    {
        $this->connectionParameters = [
            'username' => $username,
            'password' => $password,
            'host' => $host,
            'port' => $port,
            'secure' => $secure
        ];
    }

    public function connect($mbox = 'INBOX')
    {
        $connectionString = '{';
        $connectionString .= $this->connectionParameters['host'];
        $connectionString .= ':';
        $connectionString .= $this->connectionParameters['port'];
        $connectionString .= '/imap';
        if ($this->connectionParameters['secure']) {
            $connectionString .= '/'.$this->connectionParameters['secure'];
            $connectionString .= '/novalidate-cert';
        }
        $connectionString .= '}';
        $connectionString .= $mbox;
        //return $connectionString;
        try {
            $this->connection = imap_open(
                $connectionString,
                $this->connectionParameters['username'],
                $this->connectionParameters['password']
            );
            return true;
        } catch(\Exception $e) {
            return $e->getMessage();
        }
    }

    public function searchMessage($command)
    {
        return imap_search($this->connection, $command);
    }

    public function getAllMessage($messageIds)
    {
        if (empty($messageIds)) {
            return [];
        }
        $messages = [];
        foreach($messageIds as $messageId) {
            $messages[$messageId] = $this->messageFactory($messageId);
        }
        return $messages;
    }

    public function getConnection()
    {
        return $this->connection;
    }

    public function getMessage($messageIdx)
    {
        $Message = $this->getMessageRaw($messageIdx);
        return $Message->get();
    }

    public function messageFactory($messageIdx)
    {
        return new Message($this->connection, $messageIdx);
    }

    public function close()
    {
        imap_close($this->connection);
    }
}
