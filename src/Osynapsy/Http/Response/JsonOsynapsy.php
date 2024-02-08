<?php

/*
 * This file is part of the Osynapsy package.
 *
 * (c) Pietro Celeste <p.celeste@osynapsy.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Osynapsy\Http\Response;


/**
 * Description of JsonOsynapsy
 *
 * @author Pietro Celeste <p.celeste@osynapsy.net>
 */
class JsonOsynapsy extends Json
{
    public function writeStream($content, $streamId = 'main')
    {
        if (!array_key_exists($streamId, $this->streams)) {
            $this->streams[$streamId] = [];
        }
        $this->streams[$streamId][] = $content;
    }
}