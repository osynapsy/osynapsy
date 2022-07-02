<?php

/*
 * This file is part of the Osynapsy package.
 *
 * (c) Pietro Celeste <p.celeste@osynapsy.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Osynapsy\Console;

use Osynapsy\Console\Terminal;

/**
 * Description of MvcBuilder
 *
 * @author Pietro
 */
class MvcFactory
{
    public $terminal;
    public $question = [
        'Please write the full path of the app to build :'
    ];
    public $answer;

    public function __construct()
    {
        $this->terminal = new Terminal();
    }

    public function getTerminal()
    {
        $this->terminal;
    }

    public function run()
    {
        try {
            $this->printQuestion(0, 'Please write the full path of the app to build :');
            $this->validateAnswer(0);
        } catch (\Exception $e) {
            print $e->getMessage().PHP_EOL;
        }
    }

    private function printQuestion($questionId, $question)
    {
        $answer = $this->terminal->input($question);
        $this->answer[$questionId] = trim($answer);
    }

    private function validateAnswer($key)
    {
        if (!is_dir($this->answer[$key])) {
            $this->raiseException(sprintf("The directory %s is not valid", $this->answer[$key]));
        }
    }

    private function raiseException($message)
    {
        throw new \Exception($message);
    }
}
