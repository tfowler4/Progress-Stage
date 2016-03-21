<?php

/**
 * recent kill object
 */
class RecentKillObject extends DataObject {
    public $name;
    public $guild;
    public $encounter;
    public $time;
    public $server;
    public $link;
    public $screenshot;
    public $video;

    /**
     * constructor
     */
    public function __construct($guildDetails, $encounterId) {
        $encounterDetails   = $guildDetails->_encounterDetails->$encounterId;
        $encounterSpecifics = CommonDataContainer::$encounterArray[$encounterId];
        //$guildDetails->nameLength(20);

        $this->name       = $guildDetails->_name;
        $this->guild      = $guildDetails->_nameLink;
        $this->encounter  = Functions::shortName($encounterSpecifics->_name, 18);
        $this->time       = $encounterDetails->_shorttime;
        $this->server     = $guildDetails->_server;
        $this->link       = Functions::generateInternalHyperLink('guild', $guildDetails->_faction, $guildDetails->_server, $guildDetails->_name, 0, false);
        $this->screenshot = $encounterDetails->_screenshotLink;
        $this->video      = $encounterDetails->_videoLink;
    }
}