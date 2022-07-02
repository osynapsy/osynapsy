<?php

/*
 * This file is part of the Osynapsy package.
 *
 * (c) Pietro Celeste <p.celeste@osynapsy.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Osynapsy\Db\Sql;

/**
 * Description of Fragment
 *
 * @author Pietro
 */
class Fragment
{
    private $args = [];
    private $debug = false;
    private $deadTrack;
    private $fragments = [];
    private $keyword;
    private $parameters = [];
    private $parent;
    private $stringFragment;
    private $thesaurus = [
        'SELECT' => [
            'word' => 'SELECT',
            'glue' => ',',
            'root' => true,
            'unique' => true
        ],
        'FROM' => [
            'word' => 'FROM',
            'glue' => false,
            'unique' => true,
            'follower' => [
                'JOIN',
                'LEFT JOIN',
                'INNER JOIN'
            ]
        ],
        'JOIN' => [
            'word' => 'JOIN',
            'glue' => false,
            'follower' => [
                'ON'
            ]
        ],
        'LEFTJOIN' => [
            'word' => 'LEFT JOIN',
            'glue' => false,
            'follower' => [
                'ON'
            ]
        ],
        'ON' => [
            'word' => 'ON',
            'unique' => true,
            'glue' => ' AND '
        ],
        'WHERE' => [
            'word' => 'WHERE',
            'unique' => true,
            'glue' => ' AND '
        ],
        'DUMMY' => [
            'unique' => true
        ]
    ];

    public function __construct($keyword, $arguments, $parent = null)
    {
        $this->keyword = $this->validateWord($keyword);
        if (empty($parent) && empty($this->thesaurus[$this->keyword]['root'])) {
            throw new \Exception('Keyword '.$this->keyword.' isn\'t root');
        }
        $this->parent  = $parent;
        $this->addArguments($arguments);
    }

    public function __call($word, $arguments)
    {
        return $this->addFragment($this->validateWord($word), $arguments[0]);
    }

    public function addArguments($arguments)
    {
        $arrayRequired = !empty($this->thesaurus[$this->keyword]['glue']);
        if ($arrayRequired && !is_array($arguments)) {
            throw new \Exception($this->keyword.' require array args ['.print_r($arguments,true).']');
        }
        if (!is_array($arguments)) {
            $arguments = [$arguments];
        }
        $this->args = array_merge($this->args, $arguments);
        return $this;
    }

    private function addFragment($word, $arguments)
    {
        return $this->fragments[] = new Fragment($word, $arguments, $this);
    }

    public function parameters(array $parameters)
    {
        $this->parameters = array_merge($this->parameters, $parameters);
        return $this;
    }

    private function addFragment2($word, $arguments)
    {
        //If parent exist and isn't equal to DUMMY Fragment then add Fragment on parent.
        if (!empty($this->parent) && $this->word() !== 'DUMMY') {
            return $this->parent->addFragment($word, $arguments);
        }
        //If word accept multiple presence in query append to frgament repository
        if (empty($this->thesaurus[$word]['unique'])) {
            $this->debug('new Fragment ('.$word.') su $this '.$this->word());
            return $this->fragments[] = new Fragment($word, $arguments, $this);
        }
        //If fragment to add is equal to current $fragment then append arguments to current fragment.
        if ($this->word() === $word) {
            $this->debug('addArgument ('.$word.') su $this '.$this->word());
            return $this->addArguments($arguments);
        }
        // If fragment to add is present from child fragment append to it.
        foreach($this->fragments as $fragment) {
            if ($fragment->word() === $word) {
                $this->debug('addArgument su '.$fragment->word().' su childFragment '.$word);
                return $fragment->addArguments($arguments);
            }
        }
        //Append to
        $this->debug('new Fragment ('.$word.') su parent '.$this->getParent()->word());
        return $this->fragments[] = new Fragment($word, $arguments, $this->getParent());
    }

    private function build()
    {
        if (!is_null($this->stringFragment)) {
            return $this->stringFragment;
        }
        $rawFragment = [];
        foreach($this->loadFragments() as $fragment) {
            $uniqueFragment = !empty($this->thesaurus[$fragment[0]]['unique']);
            $existsFragment = array_key_exists($fragment[0], $rawFragment);
            if (!$existsFragment) {
                $rawFragment[$fragment[0]] = $uniqueFragment ? [$fragment] : [];
            }
            if (!$uniqueFragment) {
                $rawFragment[$fragment[0]][] = $fragment;
                continue;
            }
            $rawFragment[$fragment[0]][2] = array_merge(
                $rawFragment[$fragment[0]][2],
                $fragment[2]
            );
        }
        return print_r($rawFragment, true);
    }

    public function loadFragments()
    {
        $fragments = [[$this->keyword, $this->getGlue($this->keyword), $this->args]];
        foreach ($this->fragments as $fragment) {
            $fragments = array_merge($fragments, $fragment->loadFragments());
        }
        return $fragments;
    }

    private function debug($message)
    {
        if (empty($this->debug)) {
            return;
        }
        echo $message.'<br>'.PHP_EOL;
    }

    private function getArguments()
    {
        return $this->args;
    }

    public function getDeadTrack()
    {
        if (empty($this->deadTrack)) {
            $this->deadTrack = new Fragment('DUMMY', ['dummy'], $this->getParent());
        }
        return $this->deadTrack;
    }

    private function getGlue($word)
    {
        return $this->thesaurus[$word]['glue'];
    }

    private function getParent()
    {
        return empty($this->parent) || $this->word() === 'DUMMY' ? $this : $this->parent;
    }

    public function getParameters()
    {
        $parameters = $this->parameters;
        foreach($this->fragments as $fragment) {
            $parameters = array_merge($parameters, $fragment->getParameters());
        }
        return $parameters;
    }

    public function get()
    {
        return $this->build();
    }

    public function __if__($condition)
    {
        return $condition ? $this : $this->getParent()->getDeadTrack();
    }

    public function __endif__()
    {
        echo $this->parent->word();
        return $this->parent;
    }

    public function __toString()
    {
        return $this->get();
    }

    private function validateWord($rawword)
    {
        $word = strtoupper($rawword);
        if (!array_key_exists($word, $this->thesaurus)) {
            throw new \Exception('Keyword don\'t recognize');
        }
        return $word;
    }

    public function word()
    {
        return $this->keyword;
    }
}
