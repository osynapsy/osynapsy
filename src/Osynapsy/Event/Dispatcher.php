<?php

/*
 * This file is part of the Osynapsy package.
 *
 * (c) Pietro Celeste <p.celeste@osynapsy.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Osynapsy\Event;

use Osynapsy\Controller\ControllerInterface;

/**
 * Description of Dispatcher
 *
 * @author Peter
 */
class Dispatcher
{
    private static $controller;
    public static $listeners = [];

    public function __construct(ControllerInterface $controller)
    {
        self::$controller = $controller;
    }

    public function dispatch(Event $event)
    {
        $this->loadGlobalListeners($event->getId());
        $this->triggerEvent($event);
    }

    private function triggerEvent(Event $event)
    {
        if (empty(self::$listeners[$event->getId()])) {
            return;
        }
        foreach(self::$listeners[$event->getId()] as $listener) {
            $listener->trigger($event);
        }
    }

    private function loadGlobalListeners($eventId)
    {
        $listeners = $this->getController()->getRequest()->get('listeners');
        if (empty($listeners)) {
            return;
        }
        foreach($listeners as $listener => $listenerEventId) {
            if ($listenerEventId != $eventId) {
                continue;
            }
            if (!array_key_exists($eventId, self::$listeners) ?? []) {
                self::$listeners[$eventId] = [];
            }
            $listenerId = '\\'.trim(str_replace(':','\\',$listener));
            self::$listeners[$eventId][] = new $listenerId($this->getController());
        }
    }

    private function getController()
    {
        return self::$controller;
    }

    public static function addListener(callable $trigger, array $eventIDs)
    {
        $listener = new class(self::$controller) implements ListenerInterface
        {
            private $controller;
            private $trigger;

            public function __construct($controller)
            {
                $this->controller = $controller;
            }

            public function setTrigger(callable $callable)
            {
                $this->trigger = $callable;
            }

            public function trigger(Event $event)
            {
                autowire()->execFunction($this->trigger, [$event]);
            }
        };
        $listener->setTrigger($trigger);
        foreach ($eventIDs as $eventId) {
            if (!array_key_exists($eventId, self::$listeners)) {
                self::$listeners[$eventId] = [];
            }
            self::$listeners[$eventId][] = $listener;
        }
    }
    
    public function __invoke(Event $event)
    {
        $this->dispatch($event);
    }
}
