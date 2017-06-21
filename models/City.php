<?php

namespace mp_dd\models;
if (!defined('ABSPATH')) {
    exit;
}
use WP_Post;

class City
{
    #region Variables
    /** @var WP_Post */
    public $post;
    #endregion

    #region Construct
    /**
     * City constructor.
     *
     * @param WP_Post $post
     */
    public function __construct($post)
    {
        $this->post = $post;
    }

    /**
     * @param $id
     *
     * @return City
     */
    public static function getByID($id)
    {
        return new City(get_post($id));
    }
    #endregion

    #region getID()
    /**
     * @return int
     */
    public function getID()
    {
        return $this->post->ID;
    }
    #endregion

    #region getTitle()
    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->post->post_title;
    }
    #endregion

    #region isValid()
    /**
     * @return bool true if the City is valid (all mandatory fields are filled).
     */
    public function isValid()
    {
        return true;
    }
    #endregion

    #region isPublished()
    /**
     * @return bool true if the City is published
     */
    public function isPublished()
    {
        return $this->post->post_status == 'publish';
    }
    #endregion
}
