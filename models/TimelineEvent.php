<?php

/**
 * Created by PhpStorm.
 * User: moridrin
 * Date: 24-11-16
 * Time: 20:02
 */
class TimelineEvent
{
    /**
     * @var WP_Post
     */
    public $post;

    /**
     * @var DateTime
     */
    private $startDate;

    /**
     * @var DateTime
     */
    private $endDate;

    /**
     * @var string
     */
    private $description;

    /**
     * TimelineEvent constructor.
     *
     * @param WP_Post $post
     */
    public function __construct($post)
    {
        $this->post        = $post;
        $this->startDate   = DateTime::createFromFormat(
            'Y-m-d',
            get_post_meta($post->ID, 'start_date', true)
        );
        $this->endDate     = DateTime::createFromFormat(
            'Y-m-d',
            get_post_meta($post->ID, 'end_date', true)
        );
        $this->description = get_post_meta(get_the_ID(), 'description', true);
    }

    /**
     * @param string $tag
     *
     * @return array
     */
    public static function get_all_by_tag($tag)
    {
        $args            = array(
            'posts_per_page' => -1,
            'post_type'      => 'timeline_event',
            'post_status'    => 'publish',
        );
        $timeline_events = get_posts($args);
        $timeline_events = self::order_by_start_date($timeline_events);
        $relevant_events = array();
        foreach ($timeline_events as $event) {
            /** @var TimelineEvent $event */
            if (in_array(strtolower($tag), get_post_meta($event->getPostId(), 'links', true))) {
                $relevant_events[$event->getPostId()] = $event;
            }
        }
        return $relevant_events;
    }

    /**
     * @param WP_Post $post
     * @param array   $events
     *
     * @return array
     */
    public static function get_all_for_post($post, $events = array())
    {
        $events += TimelineEvent::get_all_by_tag($post->post_title);
        $args     = array(
            'post_parent' => $post->ID,
            'post_type'   => 'any',
            'numberposts' => -1,
            'post_status' => 'any',
        );
        $children = get_children($args);
        foreach ($children as $child) {
            $events += TimelineEvent::get_all_for_post($child, $events);
        }
        return self::order_by_start_date($events);
    }

    /**
     * @param $id
     *
     * @return TimelineEvent
     */
    public static function get_by_id($id)
    {
        return new TimelineEvent(get_post($id));
    }

    /**
     * @param array $timeline_events
     *
     * @return array
     */
    public static function order_by_start_date($timeline_events)
    {
        $sortable_array = array();
        foreach ($timeline_events as $event) {
            if (!$event instanceof TimelineEvent) {
                $event = new TimelineEvent($event);
            }
            $sortable_array[date('Ymd', $event->startDate->getTimestamp())] = $event;
        }
        ksort($sortable_array);
        return $sortable_array;
    }

    /**
     * @return int
     */
    public function getPostId()
    {
        return $this->post->ID;
    }

    /**
     * @return DateTime
     */
    public function getStartDate()
    {
        return $this->startDate;
    }

    /**
     * @param bool $newline set false if you don't want to echo <br/> at the end of the line.
     */
    public function echoStartDate($newline = true)
    {
        if (!$this->startDate) {
            return;
        }
        echo $this->startDate->format('Y-m-d');
        if ($newline) {
            echo '<br/>';
        }
    }

    /**
     * @return DateTime
     */
    public function getEndDate()
    {
        return $this->endDate;
    }

    /**
     * @param bool $newline set false if you don't want to echo <br/> at the end of the line.
     */
    public function echoEndDate($newline = true)
    {
        if (!$this->endDate) {
            return;
        }
        echo $this->endDate->format('Y-m-d');
        if ($newline) {
            echo '<br/>';
        }
    }

    /**
     * @return bool true if the TimelineEvent is valid (all mandatory fields are filled).
     */
    public function isValid()
    {
        if ($this->startDate == false) {
            return false;
        }
        return true;
    }

    /**
     * @return bool true if the TimelineEvent is published
     */
    public function isPublished()
    {
        if ($this->post->post_status == 'publish') {
            return true;
        }
        return false;
    }
}