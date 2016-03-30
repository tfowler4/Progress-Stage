<?php

/**
 * news side rankings object
 */
class NewsSideRankings extends DataObject {
    public $points;
    public $progress;
    public $guild;
    public $rank;
    public $image;

    /**
     * constructor
     */
    public function __construct($guildDetails, $points, $image, $rank) {
        $this->points   = $points;
        $this->progress = $guildDetails->_progress;
        $this->guild    = $guildDetails->_nameLink;
        $this->rank     = $rank;
        $this->image    = $image;
    }
}