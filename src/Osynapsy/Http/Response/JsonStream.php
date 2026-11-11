<?php
namespace Osynapsy\Http\Response;

/**
 * Description of JsonStream
 *
 * @author Pietro Celeste <p.celeste@qanda.cc>
 */
class JsonStream extends Json
{
    public function __construct()
    {        
        header('Content-Type: application/x-ndjson; charset=utf-8');        
        header('Cache-Control: no-cache');
        header('X-Accel-Buffering: no');
                
        // Evita il buffering PHP, se non gestito dal kernel.
        while (ob_get_level() > 0) {
            ob_end_flush();
        }
    }

    public function writeStream($data, $streamId = 'main'): self
    {
        echo json_encode([$streamId => [$data], 'log' => date('Y-m-d H:i:s')], JSON_THROW_ON_ERROR) . "\n";        
                
        flush();
        
        return $this;
    }

    public function __toString(): string
    {
        return '';
    }
}
