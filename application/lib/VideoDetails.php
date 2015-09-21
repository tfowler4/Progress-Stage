<?php

/**
 * video details data object
 */
class VideoDetails extends DataObject {
    protected $_videoId;
    protected $_guildId;
    protected $_encounterId;
    protected $_notes;
    //protected $_channel;
    protected $_url;
    protected $_videoLink;
    protected $_type;

    /**
     * constructor
     */
    public function __construct($params) {
        $this->_videoId     = $params['video_id'];
        $this->_guildId     = $params['guild_id'];
        $this->_encounterId = $params['encounter_id'];
        $this->_notes       = $params['notes'];
        //$this->_channel     = $params['channel'];
        $this->_url         = $params['url'];
        $this->_videoLink   = '<a target="_blank" href="' . $this->_url . '">View</a>';
        $this->_type        = $params['type'];
    }
}