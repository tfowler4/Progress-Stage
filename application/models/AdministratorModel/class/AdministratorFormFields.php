<?php

/**
 * guild & raid team add/edit form
 */
class AdminGuildFormFields {
    public $guildId;
    public $guildName;
    public $faction;
    public $server;
    public $region;
    public $country;
    public $guildLeader;
    public $website;
    public $facebook;
    public $twitter;
    public $google;
    public $guildLogo;
    public $active;
}

/**
 * kill submission form
 */
class AdminKillSubmissionFormFields {
    public $guildId;
    public $encounter;
    public $dateMonth;
    public $dateDay;
    public $dateYear;
    public $dateHour;
    public $dateMinute;
    public $screenshot;
    public $video;
    public $videoId;
    public $videoTitle;
    public $videoUrl;
    public $videoType;
}

/**
 * tier submission form
 */
class AdminTierFormFields {
    public $tierId;
    public $tierNumber;
    public $altTier;
    public $tierName;
    public $altName;
    public $startDate;
    public $endDate;
}

/**
 * dungeon submission form
 */
class AdminDungeonFormFields {
    public $dungeonId;
    public $dungeon;
    public $abbreviation;
    public $tier;
    public $raidSize;
    public $launchDate;
    public $dungeonType;
    public $euTimeDiff;
}

/**
 * encounter submission form
 */
class AdminEncounterFormFields {
    public $encounterId;
    public $encounter;
    public $dungeon;
    public $encounterName;
    public $encounterShortName;
    public $launchDate;
    public $encounterOrder;
}

/**
 * news article submission form
 */
class AdminArticleFormFields {
    public $articleId;
    public $title;
    public $author;
    public $content;
    public $dateMonth;
    public $dateDay;
    public $dateYear;
    public $dateHour;
    public $dateMinute;
}