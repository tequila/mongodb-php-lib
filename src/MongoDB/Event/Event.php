<?php

namespace Tequilla\MongoDB\Event;

use Symfony\Component\EventDispatcher\Event as BaseEvent;

/**
 * Decorates @see \Symfony\Component\EventDispatcher\Event class, allows to reuse the same event instance
 * by doing multiple calls to @see \Symfony\Component\EventDispatcher\EventDispatcher::dispatch().
 * If propagation of the event has been stopped during first EventDispatcher::dispatch() call,
 * just call @see refreshPropagation(), and use this event in a new call to dispatch()
 */
abstract class Event extends BaseEvent
{
    /**
     * @var bool
     */
    protected $propagationStopped = false;

    /**
     * @return bool
     */
    public function isPropagationStopped()
    {
        return $this->propagationStopped;
    }

    /**
     * @void
     */
    public function stopPropagation()
    {
        $this->propagationStopped = true;
    }

    /**
     * @void
     */
    public function refreshPropagation()
    {
        $this->propagationStopped = false;
    }
}